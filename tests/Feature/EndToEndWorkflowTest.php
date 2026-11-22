<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EndToEndWorkflowTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Uji simulasi alur kerja utuh (End-to-End) dari pemesanan DKM hingga kuitansi lunas.
     */
    public function test_complete_end_to_end_service_order_lifecycle(): void
    {
        Storage::fake('public');

        // Setup Aktor Petugas
        $admin = User::factory()->create([
            'name' => 'Admin Utama',
            'email' => 'admin@servisac.com',
            'role' => User::ROLE_ADMIN,
            'password' => bcrypt('password123'),
        ]);

        $tech = User::factory()->create([
            'name' => 'Budi Teknisi',
            'email' => 'budi@servisac.com',
            'phone' => '081299991111',
            'role' => User::ROLE_TECHNICIAN,
            'password' => bcrypt('password123'),
        ]);

        // =========================================================================
        // TAHAP 1: Pengurus DKM memesan servis via form web publik (Tanpa Login)
        // =========================================================================
        $orderPayload = [
            'is_new_masjid' => '1',
            'new_masjid_name' => 'Masjid Baiturrahim',
            'address' => 'Jl. Merpati Putih No. 88, Jakarta',
            'pic_name' => 'H. Suryadi',
            'pic_phone' => '081233334444',
            'dkm_complaint' => 'Cuci Rutin',
            'ac_count' => 3,
            'service_date' => now()->addDay()->toDateString(),
            'service_time' => 'Pagi (08:30 - 11:30 WIB)',
            'notes' => 'Tolong bawa tangga untuk mimbar utama',
        ];

        $responsePesan = $this->post('/pesan', $orderPayload);

        $order = Order::where('dkm_complaint', 'Cuci Rutin')->latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals(Order::STATUS_PESANAN_BARU, $order->status);
        $this->assertEquals(225000, $order->base_amount); // 75.000 x 3
        $this->assertNull($order->technician_id);

        $responsePesan->assertRedirect('/pesan/status/' . $order->order_number);

        // =========================================================================
        // TAHAP 2: Admin melihat pesanan baru & menugaskan Teknisi Budi
        // =========================================================================
        $responseAssign = $this->actingAs($admin)->post('/admin/order/' . $order->id . '/assign', [
            'technician_id' => $tech->id,
            'service_date' => now()->toDateString(),
            'service_time' => 'Pagi (08:30 - 11:30 WIB)',
        ]);

        $responseAssign->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_DIJADWALKAN, $order->status);
        $this->assertEquals($tech->id, $order->technician_id);

        // =========================================================================
        // TAHAP 3: Teknisi Budi membuka dashboard HP & melihat tugas
        // =========================================================================
        $responseTechDash = $this->actingAs($tech)->get('/teknisi');
        $responseTechDash->assertStatus(200);
        $responseTechDash->assertSee('Masjid Baiturrahim');

        // =========================================================================
        // TAHAP 4: Teknisi tiba di lokasi dan klik "Mulai Bekerja"
        // =========================================================================
        $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/mulai')->assertRedirect();
        $order->refresh();
        $this->assertEquals(Order::STATUS_DIKERJAKAN, $order->status);

        // =========================================================================
        // TAHAP 5: Teknisi mengunggah foto bukti servis
        // =========================================================================
        $fotoBukti = UploadedFile::fake()->image('hasil_cuci.jpg', 800, 600);
        $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/foto', [
            'photo' => $fotoBukti,
            'photo_type' => 'sesudah',
            'caption' => 'Selesai cuci evaporator dan filter AC',
        ])->assertRedirect();

        $this->assertCount(1, $order->fresh()->photos);

        // =========================================================================
        // TAHAP 6: Teknisi menambahkan catatan penggantian sparepart (tambah freon)
        // =========================================================================
        $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/sparepart', [
            'additional_amount' => 150000,
            'additional_notes' => 'Tambah Freon R32 1.5 Kg',
        ])->assertRedirect();

        $order->refresh();
        $this->assertEquals(150000, $order->additional_amount);
        $this->assertEquals(375000, $order->grandTotal()); // 225.000 + 150.000

        // =========================================================================
        // TAHAP 7: Teknisi menyelesaikan servis di lokasi (Status -> pekerjaan_selesai & redirect ke pembayaran)
        // =========================================================================
        $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/selesai')
            ->assertRedirect('/teknisi/order/' . $order->id . '/pembayaran');

        $order->refresh();
        $this->assertEquals(Order::STATUS_PEKERJAAN_SELESAI, $order->status);
        $this->assertEquals(now()->toDateString(), $order->masjid->fresh()->last_service_date->toDateString());

        // =========================================================================
        // TAHAP 7.5: Teknisi mengunggah bukti transfer dari DKM (Status -> menunggu_verifikasi)
        // =========================================================================
        $proofFile = \Illuminate\Http\UploadedFile::fake()->image('bukti_transfer.jpg');
        $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/lapor-bayar', [
            'payment_method' => 'qris',
            'payment_proof' => $proofFile,
        ])->assertRedirect('/teknisi/order/' . $order->id . '/pembayaran');

        $order->refresh();
        $this->assertEquals(Order::STATUS_MENUNGGU_VERIFIKASI, $order->status);
        $this->assertEquals('qris', $order->payment_method);
        $this->assertNotNull($order->payment_proof);

        // =========================================================================
        // TAHAP 8: Admin memverifikasi pelunasan uang masuk (Metode QRIS)
        // =========================================================================
        $responseBayar = $this->actingAs($admin)->post('/admin/order/' . $order->id . '/pay', [
            'payment_method' => 'qris',
        ]);

        $responseBayar->assertRedirect('/admin/order/' . $order->id . '/receipt');

        $order->refresh();
        $this->assertEquals(Order::STATUS_SELESAI, $order->status);
        $this->assertEquals('qris', $order->payment_method);
        $this->assertNotNull($order->paid_at);

        // =========================================================================
        // TAHAP 9: Kuitansi resmi siap cetak & tombol kirim WA DKM tersedia
        // =========================================================================
        $responseReceipt = $this->actingAs($admin)->get('/admin/order/' . $order->id . '/receipt');
        $responseReceipt->assertStatus(200);
        $responseReceipt->assertSee('KUITANSI SERVIS AC MASJID');
        $responseReceipt->assertSee('LUNAS &amp; BERES', false);
        $responseReceipt->assertSee('Rp 375.000');
        $responseReceipt->assertSee('https://wa.me/6281233334444', false);
    }
}
