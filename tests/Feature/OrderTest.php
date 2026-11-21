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
        $response->assertRedirect('/pesan/sukses/' . $order->order_number);
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
        $response->assertRedirect('/pesan/sukses/' . $order->order_number);
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
    }
}
