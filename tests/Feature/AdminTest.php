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
}
