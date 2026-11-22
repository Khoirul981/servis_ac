<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test formulir pemesanan dapat dibuka publik tanpa login.
     */
    public function test_public_user_can_view_order_form(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Pemesanan Servis AC Masjid');
        $response->assertSee('Cuci Rutin');
    }

    /**
     * Test pemesanan servis untuk masjid yang sudah terdaftar.
     */
    public function test_public_user_can_submit_order_for_existing_masjid(): void
    {
        $masjid = Masjid::factory()->create([
            'name' => 'Masjid Al-Barokah',
            'address' => 'Jl. Mawar No. 10',
            'pic_name' => 'Pak Haji Agus',
            'pic_phone' => '081234567890',
        ]);

        $response = $this->post('/pesan', [
            'is_new_masjid' => '0',
            'masjid_id' => $masjid->id,
            'address' => 'Jl. Mawar No. 10 (Samping Kantor Pos)',
            'pic_name' => 'Pak Haji Agus',
            'pic_phone' => '081234567890',
            'dkm_complaint' => 'Cuci Rutin',
            'ac_count' => 2,
            'service_date' => now()->addDay()->toDateString(),
            'service_time' => 'Pagi (08:30 - 11:30 WIB)',
            'notes' => 'Tolong bawa tangga lipat',
        ]);

        $this->assertDatabaseHas('orders', [
            'masjid_id' => $masjid->id,
            'status' => Order::STATUS_PESANAN_BARU,
            'ac_count' => 2,
            'base_amount' => 150000, // 75.000 x 2
        ]);

        $order = Order::latest()->first();
        $response->assertRedirect('/pesan/status/' . $order->order_number);
    }

    /**
     * Test pemesanan servis dengan mendaftarkan masjid baru.
     */
    public function test_public_user_can_submit_order_with_new_masjid(): void
    {
        $response = $this->post('/pesan', [
            'is_new_masjid' => '1',
            'new_masjid_name' => 'Musholla At-Taqwa Baru',
            'address' => 'Jl. Dahlia Indah No. 45',
            'pic_name' => 'Ust. Fauzi',
            'pic_phone' => '085711223344',
            'dkm_complaint' => 'Tidak Dingin / Bocor Air',
            'ac_count' => 1,
        ]);

        $this->assertDatabaseHas('masjids', [
            'name' => 'Musholla At-Taqwa Baru',
            'address' => 'Jl. Dahlia Indah No. 45',
        ]);

        $this->assertDatabaseHas('orders', [
            'dkm_complaint' => 'Tidak Dingin / Bocor Air',
            'base_amount' => 85000,
        ]);

        $order = Order::latest()->first();
        $response->assertRedirect('/pesan/status/' . $order->order_number);
    }

    /**
     * Test honeypot menolak bot spam secara senyap.
     */
    public function test_honeypot_silently_rejects_spam_bots(): void
    {
        $response = $this->post('/pesan', [
            'website' => 'https://spam-bot.site', // Jebakan terisi
            'is_new_masjid' => '1',
            'new_masjid_name' => 'Spam Masjid',
            'address' => 'Spam Address',
            'pic_name' => 'Bot',
            'pic_phone' => '081234567890',
            'dkm_complaint' => 'Cuci Rutin',
            'ac_count' => 1,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseMissing('orders', [
            'dkm_complaint' => 'Cuci Rutin',
        ]);
    }

    /**
     * Test rate limiter membatasi pengiriman berlebihan (maksimal 3 order per nomor per jam).
     */
    public function test_order_rate_limiter_blocks_excessive_submissions(): void
    {
        RateLimiter::clear('order-submission:089999999999');

        $payload = [
            'is_new_masjid' => '1',
            'new_masjid_name' => 'Musholla Rate Limit',
            'address' => 'Jl. Melati',
            'pic_name' => 'Pak Test',
            'pic_phone' => '089999999999',
            'dkm_complaint' => 'Cuci Rutin',
            'ac_count' => 1,
        ];

        // 3 submit pertama berhasil
        $this->post('/pesan', $payload)->assertRedirect();
        $this->post('/pesan', $payload)->assertRedirect();
        $this->post('/pesan', $payload)->assertRedirect();

        // Submit ke-4 diblokir
        $response = $this->post('/pesan', $payload);
        $response->assertSessionHasErrors('pic_phone');
    }

    /**
     * Test halaman sukses menampilkan rincian dan link WhatsApp.
     */
    public function test_order_success_page_displays_order_details_and_whatsapp_button(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'phone' => '081234567890',
        ]);

        $masjid = Masjid::factory()->create([
            'name' => 'Masjid Raya Darussalam',
        ]);

        $order = Order::create([
            'order_number' => 'SO-20261121-777',
            'masjid_id' => $masjid->id,
            'status' => Order::STATUS_PESANAN_BARU,
            'ac_count' => 3,
            'base_amount' => 225000,
            'dkm_complaint' => 'Cuci Rutin',
        ]);

        $response = $this->get('/pesan/sukses/' . $order->order_number);

        $response->assertStatus(200);
        $response->assertSee('SO-20261121-777');
        $response->assertSee('Masjid Raya Darussalam');
        $response->assertSee('https://wa.me/6281234567890', false);
        $response->assertSee('BELUM DIBAYAR');
    }

    /**
     * Test pengguna dapat melacak pesanan dengan memasukkan nomor registrasi.
     */
    public function test_user_can_track_order_by_registration_number(): void
    {
        $masjid = Masjid::factory()->create(['name' => 'Masjid Jamie Al-Kautsar']);
        $order = Order::create([
            'order_number' => 'SO-20261121-003',
            'masjid_id' => $masjid->id,
            'status' => Order::STATUS_PESANAN_BARU,
            'ac_count' => 2,
            'base_amount' => 150000,
            'dkm_complaint' => 'Cuci Rutin',
        ]);

        // 1. Submit pencarian nomor registrasi
        $response = $this->get('/pesan/lacak?order_number=SO-20261121-003');
        $response->assertRedirect('/pesan/status/SO-20261121-003');

        // 2. Akses halaman status pesanan (sebelum dibayar)
        $statusResponse = $this->get('/pesan/status/SO-20261121-003');
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('SO-20261121-003');
        $statusResponse->assertSee('Masjid Jamie Al-Kautsar');
        $statusResponse->assertSee('BELUM DIBAYAR');
        $statusResponse->assertSee('Menunggu Verifikasi Admin');
    }

    /**
     * Test status pesanan berubah menjadi SUDAH DIBAYAR ketika diverifikasi oleh admin.
     */
    public function test_order_status_displays_sudah_dibayar_when_verified_by_admin(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $masjid = Masjid::factory()->create(['name' => 'Masjid Agung Al-Ikhlas']);
        $order = Order::create([
            'order_number' => 'SO-20261121-005',
            'masjid_id' => $masjid->id,
            'status' => Order::STATUS_MENUNGGU_BAYAR,
            'ac_count' => 2,
            'base_amount' => 150000,
            'dkm_complaint' => 'Cuci Rutin',
        ]);

        // Admin verifikasi pembayaran
        $responsePay = $this->actingAs($admin)->post('/admin/order/' . $order->id . '/pay', [
            'payment_method' => 'transfer',
        ]);
        $responsePay->assertRedirect('/admin/order/' . $order->id . '/receipt');

        // Refresh model dan cek status
        $order->refresh();
        $this->assertEquals(Order::STATUS_SELESAI, $order->status);
        $this->assertNotNull($order->paid_at);
        $this->assertTrue($order->isPaid());

        // Buka halaman status pesanan publik
        $statusResponse = $this->get('/pesan/status/SO-20261121-005');
        $statusResponse->assertStatus(200);
        $statusResponse->assertSee('SUDAH DIBAYAR');
        $statusResponse->assertSee('Terverifikasi Admin');
        $statusResponse->assertSee('Lihat Kuitansi Resmi');
    }

    /**
     * Test kuitansi memiliki link menuju halaman status pesanan dan pesan WA memuat link pesanan.
     */
    public function test_receipt_and_whatsapp_contain_order_status_link(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $masjid = Masjid::factory()->create([
            'name' => 'Musholla An-Nur',
            'pic_name' => 'Pak Ahmad',
            'pic_phone' => '081233445566',
        ]);
        $order = Order::create([
            'order_number' => 'SO-20261121-009',
            'masjid_id' => $masjid->id,
            'status' => Order::STATUS_SELESAI,
            'payment_method' => 'qris',
            'paid_at' => now(),
            'ac_count' => 1,
            'base_amount' => 75000,
            'dkm_complaint' => 'Cuci Rutin',
        ]);

        // Cek halaman kuitansi admin (ada tombol Cetak dan tombol Kirim ke WhatsApp DKM)
        $receiptResponse = $this->actingAs($admin)->get('/admin/order/' . $order->id . '/receipt');
        $receiptResponse->assertStatus(200);
        $receiptResponse->assertSee('/pesan/status/SO-20261121-009');
        $receiptResponse->assertSee('Cetak Kuitansi');
        $receiptResponse->assertSee('Kirim ke WhatsApp DKM');

        // Cek halaman kuitansi publik pemesan (hanya ada tombol Cetak Kuitansi, TIDAK ada tombol Kirim ke WhatsApp DKM)
        auth()->logout();
        $publicReceiptResponse = $this->get('/pesan/kuitansi/SO-20261121-009');
        $publicReceiptResponse->assertStatus(200);
        $publicReceiptResponse->assertSee('/pesan/status/SO-20261121-009');
        $publicReceiptResponse->assertSee('Cetak Kuitansi');
        $publicReceiptResponse->assertDontSee('Kirim ke WhatsApp DKM');

        // Cek link pesanan di pesan WhatsApp kuitansi
        $receiptResponse->assertSee(route('order.status', ['order_number' => 'SO-20261121-009']), false);
    }

    /**
     * Test pelacakan nomor registrasi yang salah atau tidak ada mengembalikan pesan error validasi.
     */
    public function test_tracking_invalid_order_number_returns_validation_error(): void
    {
        $response = $this->get('/pesan/lacak?order_number=SO-99999999-999');
        $response->assertRedirect('/pesan');
        $response->assertSessionHasErrors('track_order_number');
    }
}
