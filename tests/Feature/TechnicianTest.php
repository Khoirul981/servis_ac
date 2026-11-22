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

        $response->assertRedirect('/teknisi');
        $response->assertSessionHas('success');

        // Status berubah menjadi menunggu_bayar
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_MENUNGGU_BAYAR,
        ]);

        // Tanggal servis terakhir masjid terupdate
        $this->assertEquals(now()->toDateString(), $masjid->fresh()->last_service_date->toDateString());
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
}
