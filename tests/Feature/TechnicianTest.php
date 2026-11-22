<?php

namespace Tests\Feature;

use App\Models\Masjid;
use App\Models\Order;
use App\Models\OrderPhoto;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TechnicianTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test teknisi dapat melihat daftar tugas aktif miliknya di dashboard.
     */
    public function test_technician_can_view_assigned_tasks_on_dashboard(): void
    {
        $tech = User::factory()->create([
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $masjid = Masjid::factory()->create([
            'name' => 'Masjid Nurul Hidayah',
        ]);

        $order = Order::factory()->create([
            'masjid_id' => $masjid->id,
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIJADWALKAN,
        ]);

        $response = $this->actingAs($tech)->get('/teknisi');

        $response->assertStatus(200);
        $response->assertSee('Masjid Nurul Hidayah');
        $response->assertSee($order->order_number);
    }

    /**
     * Test teknisi tidak dapat mengakses lembar kerja order milik teknisi lain.
     */
    public function test_technician_cannot_access_other_technician_orders(): void
    {
        $techA = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);
        $techB = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $techB->id,
            'status' => Order::STATUS_DIJADWALKAN,
        ]);

        $response = $this->actingAs($techA)->get('/teknisi/order/' . $order->id);

        $response->assertStatus(403);
    }

    /**
     * Test tombol 1: teknisi mulai kerja di lokasi (dijadwalkan -> dikerjakan).
     */
    public function test_technician_can_start_work_at_location(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIJADWALKAN,
        ]);

        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/mulai');

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);
    }

    /**
     * Test tombol 2: teknisi dapat mengunggah foto bukti servis.
     */
    public function test_technician_can_upload_service_photo(): void
    {
        Storage::fake('public');

        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        $file = UploadedFile::fake()->image('bukti_sebelum.jpg', 640, 480);

        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/foto', [
            'photo' => $file,
            'photo_type' => 'sebelum',
            'caption' => 'Kondisi evaporator sebelum dicuci',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('order_photos', [
            'order_id' => $order->id,
            'photo_type' => 'sebelum',
            'caption' => 'Kondisi evaporator sebelum dicuci',
        ]);

        $photo = OrderPhoto::first();
        Storage::disk('public')->assertExists($photo->photo_url);
    }

    /**
     * Test tombol 3: teknisi dapat mencatat biaya tambahan sparepart/freon.
     */
    public function test_technician_can_record_additional_sparepart_costs(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
            'base_amount' => 150000,
            'additional_amount' => 0,
        ]);

        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/sparepart', [
            'additional_amount' => 125000,
            'additional_notes' => 'Isi Freon R32 1 Kg',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'additional_amount' => 125000,
            'additional_notes' => 'Isi Freon R32 1 Kg',
        ]);
    }

    /**
     * Test tombol 4: teknisi TIDAK DAPAT menyelesaikan pekerjaan jika belum ada foto bukti.
     */
    public function test_technician_cannot_complete_work_without_photos(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        // Tidak ada foto diunggah
        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/selesai');

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_DIKERJAKAN, // Status tidak berubah
        ]);
    }

    /**
     * Test tombol 4: teknisi berhasil menyelesaikan pekerjaan jika foto bukti sudah ada.
     * Status berubah menjadi pekerjaan_selesai dan dialihkan ke halaman pelaporan pembayaran terpisah.
     */
    public function test_technician_can_complete_work_when_photo_is_present(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);
        $masjid = Masjid::factory()->create();

        $order = Order::factory()->create([
            'masjid_id' => $masjid->id,
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        // Buat 1 foto bukti
        OrderPhoto::create([
            'order_id' => $order->id,
            'photo_url' => 'order_photos/fake.jpg',
            'photo_type' => 'sesudah',
        ]);

        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/selesai');

        $response->assertRedirect('/teknisi/order/' . $order->id . '/pembayaran');
        $response->assertSessionHas('success');

        // Status berubah menjadi pekerjaan_selesai
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_PEKERJAAN_SELESAI,
        ]);

        // Tanggal servis terakhir masjid terupdate
        $this->assertEquals(now()->toDateString(), $masjid->fresh()->last_service_date->toDateString());
    }

    /**
     * Test teknisi dapat mengakses halaman pelaporan pembayaran terpisah dan mengunggah bukti foto.
     */
    public function test_technician_can_access_payment_page_and_upload_payment_proof(): void
    {
        Storage::fake('public');

        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);
        $masjid = Masjid::factory()->create();

        $order = Order::factory()->create([
            'masjid_id' => $masjid->id,
            'technician_id' => $tech->id,
            'status' => Order::STATUS_PEKERJAAN_SELESAI,
        ]);

        // 1. Akses halaman pembayaran
        $responsePage = $this->actingAs($tech)->get('/teknisi/order/' . $order->id . '/pembayaran');
        $responsePage->assertStatus(200);
        $responsePage->assertSee('Pelaporan Bukti Pembayaran');

        // 2. Unggah bukti foto pembayaran
        $proofFile = UploadedFile::fake()->image('bukti_transfer_dkm.jpg', 800, 600);

        $responseUpload = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/lapor-bayar', [
            'payment_method' => 'transfer',
            'payment_proof' => $proofFile,
        ]);

        $responseUpload->assertRedirect('/teknisi/order/' . $order->id . '/pembayaran');
        $responseUpload->assertSessionHas('success');

        $freshOrder = $order->fresh();
        $this->assertEquals(Order::STATUS_MENUNGGU_VERIFIKASI, $freshOrder->status);
        $this->assertEquals('transfer', $freshOrder->payment_method);
        $this->assertNotNull($freshOrder->payment_proof);
        Storage::disk('public')->assertExists($freshOrder->payment_proof);
    }

    /**
     * Test data servis lapangan terkunci setelah pekerjaan dikonfirmasi selesai.
     */
    public function test_technician_cannot_modify_photos_or_spareparts_after_work_is_completed(): void
    {
        Storage::fake('public');

        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_PEKERJAAN_SELESAI,
        ]);

        // 1. Tidak bisa upload foto baru
        $file = UploadedFile::fake()->image('foto_baru.jpg');
        $respPhoto = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/foto', [
            'photo' => $file,
            'photo_type' => 'sesudah',
        ]);
        $respPhoto->assertSessionHas('error');

        // 2. Tidak bisa tambah sparepart
        $respSparepart = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/sparepart', [
            'item_name' => 'Kapasitor',
            'quantity' => 1,
            'price' => 50000,
        ]);
        $respSparepart->assertSessionHas('error');

        // 3. Tidak bisa batalkan mulai kerja
        $respBatal = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/batal-mulai');
        $respBatal->assertSessionHas('error');
    }

    /**
     * Test teknisi dapat mengunggah bukti pembayaran setelah pekerjaan dikonfirmasi selesai.
     */
    public function test_technician_can_upload_payment_proof_after_work_completed(): void
    {
        Storage::fake('public');

        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_PEKERJAAN_SELESAI,
            'payment_proof' => null,
        ]);

        $proofFile = UploadedFile::fake()->image('struk_atm.jpg', 600, 800);

        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/lapor-bayar', [
            'payment_method' => 'transfer',
            'payment_proof' => $proofFile,
        ]);

        $response->assertRedirect('/teknisi/order/' . $order->id . '/pembayaran');
        $response->assertSessionHas('success');

        $freshOrder = $order->fresh();
        $this->assertEquals(Order::STATUS_MENUNGGU_VERIFIKASI, $freshOrder->status);
        $this->assertEquals('transfer', $freshOrder->payment_method);
        $this->assertNotNull($freshOrder->payment_proof);
        Storage::disk('public')->assertExists($freshOrder->payment_proof);
    }

    /**
     * Test teknisi dapat memperbarui dan menghapus foto bukti servis.
     */
    public function test_technician_can_update_and_delete_service_photo(): void
    {
        Storage::fake('public');

        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        $file = UploadedFile::fake()->image('bukti_lama.jpg');
        $path = $file->store('order_photos', 'public');

        $photo = OrderPhoto::create([
            'order_id' => $order->id,
            'photo_url' => $path,
            'photo_type' => 'sebelum',
            'caption' => 'Foto Awal',
        ]);

        // 1. Update Photo
        $responseUpdate = $this->actingAs($tech)->put('/teknisi/order/foto/' . $photo->id, [
            'photo_type' => 'sesudah',
            'caption' => 'Foto Setelah Perbaikan Bersih',
        ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('order_photos', [
            'id' => $photo->id,
            'photo_type' => 'sesudah',
            'caption' => 'Foto Setelah Perbaikan Bersih',
        ]);

        // 2. Delete Photo
        $responseDelete = $this->actingAs($tech)->delete('/teknisi/order/foto/' . $photo->id);
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('order_photos', [
            'id' => $photo->id,
        ]);
        Storage::disk('public')->assertMissing($path);
    }

    /**
     * Test teknisi dapat membatalkan status tiba (kembali ke dijadwalkan).
     */
    public function test_technician_can_revert_start_work(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        $response = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/batal-mulai');

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_DIJADWALKAN,
        ]);
    }

    /**
     * Test teknisi dapat menghapus catatan biaya tambahan sparepart.
     */
    public function test_technician_can_remove_sparepart(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
            'additional_amount' => 150000,
            'additional_notes' => 'Tambah Freon R32',
        ]);

        $response = $this->actingAs($tech)->delete('/teknisi/order/' . $order->id . '/sparepart');

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'additional_amount' => 0,
            'additional_notes' => null,
        ]);
    }

    /**
     * Test teknisi dapat menambah lebih dari 1 suku cadang, mengubah, dan menghapus salah satunya.
     */
    public function test_technician_can_manage_multiple_spareparts(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $order = Order::factory()->create([
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
            'additional_amount' => 0,
        ]);

        // 1. Tambah item pertama: Kapasitor (2 unit x 75.000 = 150.000)
        $responseItem1 = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/sparepart', [
            'item_name' => 'Kapasitor 35uF',
            'quantity' => 2,
            'price' => 75000,
        ]);
        $responseItem1->assertRedirect();
        $this->assertDatabaseHas('order_spareparts', [
            'order_id' => $order->id,
            'item_name' => 'Kapasitor 35uF',
            'quantity' => 2,
            'subtotal' => 150000,
        ]);
        $this->assertEquals(150000, $order->fresh()->additional_amount);

        // 2. Tambah item kedua: Freon R32 (1 unit x 150.000 = 150.000)
        $responseItem2 = $this->actingAs($tech)->post('/teknisi/order/' . $order->id . '/sparepart', [
            'item_name' => 'Isi Freon R32',
            'quantity' => 1,
            'price' => 150000,
        ]);
        $responseItem2->assertRedirect();
        $this->assertDatabaseHas('order_spareparts', [
            'order_id' => $order->id,
            'item_name' => 'Isi Freon R32',
            'quantity' => 1,
            'subtotal' => 150000,
        ]);
        // Total akumulasi otomatis 150.000 + 150.000 = 300.000
        $this->assertEquals(300000, $order->fresh()->additional_amount);

        // 3. Edit item kedua menjadi 200.000
        $freon = \App\Models\OrderSparepart::where('order_id', $order->id)->where('item_name', 'Isi Freon R32')->first();
        $responseUpdate = $this->actingAs($tech)->put('/teknisi/order/sparepart/' . $freon->id, [
            'item_name' => 'Isi Freon R32 (Tabung Penuh)',
            'quantity' => 1,
            'price' => 200000,
        ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('order_spareparts', [
            'id' => $freon->id,
            'item_name' => 'Isi Freon R32 (Tabung Penuh)',
            'subtotal' => 200000,
        ]);
        // Total baru: 150.000 + 200.000 = 350.000
        $this->assertEquals(350000, $order->fresh()->additional_amount);

        // 4. Hapus item pertama (Kapasitor)
        $kapasitor = \App\Models\OrderSparepart::where('order_id', $order->id)->where('item_name', 'Kapasitor 35uF')->first();
        $responseDeleteItem = $this->actingAs($tech)->delete('/teknisi/order/sparepart/' . $kapasitor->id);
        $responseDeleteItem->assertRedirect();
        $this->assertDatabaseMissing('order_spareparts', ['id' => $kapasitor->id]);
        // Sisa total: 200.000
        $this->assertEquals(200000, $order->fresh()->additional_amount);
    }

    /**
     * Test teknisi dapat mengelola unit AC (tambah, edit, hapus) HANYA untuk masjid yang ditugaskan kepadanya.
     */
    public function test_technician_can_manage_ac_units_for_assigned_masjid_only(): void
    {
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);
        $otherTech = User::factory()->create(['role' => User::ROLE_TECHNICIAN]);

        $masjid = Masjid::factory()->create(['name' => 'Masjid Al-Ikhlas']);

        $assignedOrder = Order::factory()->create([
            'masjid_id' => $masjid->id,
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        $unassignedOrder = Order::factory()->create([
            'technician_id' => $otherTech->id,
            'status' => Order::STATUS_DIKERJAKAN,
        ]);

        // 1. Teknisi berhasil menambah unit AC pada masjid yang ditugaskan kepadanya
        $responseAdd = $this->actingAs($tech)->post('/teknisi/order/' . $assignedOrder->id . '/ac', [
            'room_name' => 'Shaf Utama Depan',
            'ac_type' => '2 PK',
            'brand' => 'Daikin',
            'quantity' => 4,
        ]);
        $responseAdd->assertRedirect();
        $this->assertDatabaseHas('ac_units', [
            'masjid_id' => $masjid->id,
            'room_name' => 'Shaf Utama Depan',
            'ac_type' => '2 PK',
            'quantity' => 4,
        ]);

        $acUnit = \App\Models\AcUnit::where('masjid_id', $masjid->id)->first();

        // 2. Teknisi berhasil mengedit unit AC masjid tersebut
        $responseEdit = $this->actingAs($tech)->put('/teknisi/order/' . $assignedOrder->id . '/ac/' . $acUnit->id, [
            'room_name' => 'Shaf Utama Depan Kanan',
            'ac_type' => '2 PK',
            'brand' => 'Daikin Inverter',
            'quantity' => 5,
        ]);
        $responseEdit->assertRedirect();
        $this->assertDatabaseHas('ac_units', [
            'id' => $acUnit->id,
            'room_name' => 'Shaf Utama Depan Kanan',
            'quantity' => 5,
        ]);

        // 3. Teknisi TIDAK BISA mengelola AC di order masjid yang BUKAN ditugaskan kepadanya (403 Forbidden)
        $responseForbidden = $this->actingAs($tech)->post('/teknisi/order/' . $unassignedOrder->id . '/ac', [
            'room_name' => 'Ruang DKM',
            'ac_type' => '1 PK',
            'quantity' => 1,
        ]);
        $responseForbidden->assertStatus(403);

        // 4. Teknisi berhasil menghapus unit AC pada masjid yang ditugaskan
        $responseDelete = $this->actingAs($tech)->delete('/teknisi/order/' . $assignedOrder->id . '/ac/' . $acUnit->id);
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('ac_units', [
            'id' => $acUnit->id,
        ]);
    }
}
