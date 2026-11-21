<?php

namespace Tests\Feature;

use App\Models\AcUnit;
use App\Models\Masjid;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test admin dapat mengakses seluruh tab di papan operasional.
     */
    public function test_admin_can_view_operations_board_tabs(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get('/admin?tab=pesanan_baru')->assertStatus(200)->assertSee('Pesanan Baru');
        $this->actingAs($admin)->get('/admin?tab=berjalan')->assertStatus(200)->assertSee('Sedang Berjalan');
        $this->actingAs($admin)->get('/admin?tab=menunggu_bayar')->assertStatus(200)->assertSee('Menunggu Pembayaran');
        $this->actingAs($admin)->get('/admin?tab=selesai')->assertStatus(200)->assertSee('Selesai');
    }

    /**
     * Test admin dapat menugaskan teknisi dan menjadwalkan pesanan.
     */
    public function test_admin_can_assign_technician_and_schedule_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $tech = User::factory()->create(['role' => User::ROLE_TECHNICIAN, 'name' => 'Budi Teknisi']);

        $order = Order::factory()->create([
            'status' => Order::STATUS_PESANAN_BARU,
            'technician_id' => null,
        ]);

        $response = $this->actingAs($admin)->post('/admin/order/' . $order->id . '/assign', [
            'technician_id' => $tech->id,
            'service_date' => now()->addDays(2)->toDateString(),
            'service_time' => 'Pagi (08:30 - 11:30 WIB)',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'technician_id' => $tech->id,
            'status' => Order::STATUS_DIJADWALKAN,
            'service_time' => 'Pagi (08:30 - 11:30 WIB)',
        ]);
    }

    /**
     * Test admin dapat memverifikasi pembayaran dan menandai pesanan selesai.
     */
    public function test_admin_can_verify_payment_and_complete_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $order = Order::factory()->create([
            'status' => Order::STATUS_MENUNGGU_BAYAR,
            'base_amount' => 150000,
            'additional_amount' => 50000,
        ]);

        $response = $this->actingAs($admin)->post('/admin/order/' . $order->id . '/pay', [
            'payment_method' => 'transfer',
        ]);

        $response->assertRedirect('/admin/order/' . $order->id . '/receipt');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_SELESAI,
            'payment_method' => 'transfer',
        ]);

        $this->assertNotNull($order->fresh()->paid_at);
    }

    /**
     * Test admin dapat melihat lembar kuitansi resmi lunas.
     */
    public function test_admin_can_view_official_receipt(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $order = Order::factory()->create([
            'status' => Order::STATUS_SELESAI,
            'payment_method' => 'qris',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/order/' . $order->id . '/receipt');

        $response->assertStatus(200);
        $response->assertSee('KUITANSI SERVIS AC MASJID');
        $response->assertSee($order->order_number);
        $response->assertSee('LUNAS &amp; BERES', false);
    }

    /**
     * Test admin dapat membatalkan pesanan.
     */
    public function test_admin_can_cancel_order(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $order = Order::factory()->create([
            'status' => Order::STATUS_PESANAN_BARU,
        ]);

        $response = $this->actingAs($admin)->post('/admin/order/' . $order->id . '/cancel', [
            'reason' => 'Masjid sedang renovasi atap',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => Order::STATUS_BATAL,
        ]);
    }

    /**
     * Test admin dapat mengelola data masjid dan menambah unit AC.
     */
    public function test_admin_can_manage_masjid_and_add_ac_unit(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $masjid = Masjid::factory()->create(['name' => 'Masjid Raya Al-Falah']);

        // Buka daftar masjid
        $this->actingAs($admin)->get('/admin/masjids')->assertStatus(200)->assertSee('Masjid Raya Al-Falah');

        // Buka detail masjid
        $this->actingAs($admin)->get('/admin/masjids/' . $masjid->id)->assertStatus(200);

        // Tambah unit AC
        $response = $this->actingAs($admin)->post('/admin/masjids/' . $masjid->id . '/ac', [
            'room_name' => 'Shaf Utama Depan',
            'ac_type' => '2 PK',
            'brand' => 'Panasonic',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('ac_units', [
            'masjid_id' => $masjid->id,
            'room_name' => 'Shaf Utama Depan',
            'ac_type' => '2 PK',
            'brand' => 'Panasonic',
        ]);
    }

    /**
     * Test admin dapat mendaftarkan akun teknisi baru.
     */
    public function test_admin_can_register_new_technician(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Rahmat Hidayat',
            'email' => 'rahmat@servisac.com',
            'phone' => '081255556666',
            'role' => 'technician',
            'password' => 'password123',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'email' => 'rahmat@servisac.com',
            'role' => 'technician',
            'phone' => '081255556666',
        ]);
    }

    /**
     * Test admin dapat membuat, memperbarui, dan menghapus data masjid.
     */
    public function test_admin_can_create_update_and_delete_masjid(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // 1. Create Masjid
        $responseCreate = $this->actingAs($admin)->post('/admin/masjids', [
            'name' => 'Masjid Nurul Huda',
            'address' => 'Jl. Kebon Jeruk No. 12',
            'pic_name' => 'Pak Haji Syahril',
            'pic_phone' => '081233445566',
        ]);
        $responseCreate->assertRedirect();
        $this->assertDatabaseHas('masjids', [
            'name' => 'Masjid Nurul Huda',
            'pic_phone' => '081233445566',
        ]);

        $masjid = Masjid::where('name', 'Masjid Nurul Huda')->first();

        // 2. Update Masjid
        $responseUpdate = $this->actingAs($admin)->put('/admin/masjids/' . $masjid->id, [
            'name' => 'Masjid Nurul Huda Updated',
            'address' => 'Jl. Kebon Jeruk No. 12 Baru',
            'pic_name' => 'Pak Haji Syahril',
            'pic_phone' => '081233445566',
        ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('masjids', [
            'id' => $masjid->id,
            'name' => 'Masjid Nurul Huda Updated',
        ]);

        // 3. Delete Masjid
        $responseDelete = $this->actingAs($admin)->delete('/admin/masjids/' . $masjid->id);
        $responseDelete->assertRedirect('/admin/masjids');
        $this->assertDatabaseMissing('masjids', [
            'id' => $masjid->id,
        ]);
    }

    /**
     * Test admin dapat memperbarui dan menghapus unit AC.
     */
    public function test_admin_can_update_and_delete_ac_unit(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $masjid = Masjid::factory()->create();
        $unit = \App\Models\AcUnit::create([
            'masjid_id' => $masjid->id,
            'room_name' => 'Ruang Imam',
            'ac_type' => '1 PK',
            'brand' => 'Daikin',
        ]);

        // Update AC Unit
        $responseUpdate = $this->actingAs($admin)->put('/admin/ac/' . $unit->id, [
            'room_name' => 'Ruang Imam Baru',
            'ac_type' => '2 PK Inverter',
            'brand' => 'Panasonic',
        ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('ac_units', [
            'id' => $unit->id,
            'room_name' => 'Ruang Imam Baru',
            'ac_type' => '2 PK Inverter',
            'brand' => 'Panasonic',
        ]);

        // Delete AC Unit
        $responseDelete = $this->actingAs($admin)->delete('/admin/ac/' . $unit->id);
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('ac_units', [
            'id' => $unit->id,
        ]);
    }

    /**
     * Test admin dapat memperbarui dan menghapus akun pengguna serta dicegah menghapus diri sendiri.
     */
    public function test_admin_can_update_and_delete_user_and_cannot_delete_self(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $tech = User::factory()->create([
            'name' => 'Bambang',
            'email' => 'bambang@servisac.com',
            'phone' => '081299887766',
            'role' => User::ROLE_TECHNICIAN,
        ]);

        // Update User
        $responseUpdate = $this->actingAs($admin)->put('/admin/users/' . $tech->id, [
            'name' => 'Bambang Pamungkas',
            'email' => 'bambang.baru@servisac.com',
            'phone' => '081299887766',
            'role' => User::ROLE_TECHNICIAN,
        ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $tech->id,
            'name' => 'Bambang Pamungkas',
            'email' => 'bambang.baru@servisac.com',
        ]);

        // Admin cannot delete self
        $responseSelfDelete = $this->actingAs($admin)->delete('/admin/users/' . $admin->id);
        $responseSelfDelete->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Admin can delete other user
        $responseDelete = $this->actingAs($admin)->delete('/admin/users/' . $tech->id);
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('users', ['id' => $tech->id]);
    }

    /**
     * Test admin dapat memperbarui dan menghapus foto bukti servis.
     */
    public function test_admin_can_update_and_delete_service_photo(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $order = Order::factory()->create();

        $file = \Illuminate\Http\UploadedFile::fake()->image('bukti_admin.jpg');
        $path = $file->store('order_photos', 'public');

        $photo = \App\Models\OrderPhoto::create([
            'order_id' => $order->id,
            'photo_url' => $path,
            'photo_type' => 'sebelum',
            'caption' => 'Foto Dari Lapangan',
        ]);

        // Admin Update Photo
        $responseUpdate = $this->actingAs($admin)->put('/admin/order/foto/' . $photo->id, [
            'photo_type' => 'sesudah',
            'caption' => 'Keterangan diperbarui oleh Admin',
        ]);
        $responseUpdate->assertRedirect();
        $this->assertDatabaseHas('order_photos', [
            'id' => $photo->id,
            'photo_type' => 'sesudah',
            'caption' => 'Keterangan diperbarui oleh Admin',
        ]);

        // Admin Delete Photo
        $responseDelete = $this->actingAs($admin)->delete('/admin/order/foto/' . $photo->id);
        $responseDelete->assertRedirect();
        $this->assertDatabaseMissing('order_photos', [
            'id' => $photo->id,
        ]);
        \Illuminate\Support\Facades\Storage::disk('public')->assertMissing($path);
    }
}
