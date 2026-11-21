<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test login screen can be rendered.
     */
    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Sistem');
    }

    /**
     * Test admin can login and is redirected to admin dashboard.
     */
    public function test_admin_can_login_and_redirect_to_admin_dashboard(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin_test@servisac.com',
            'role' => User::ROLE_ADMIN,
            'password' => bcrypt('password123'),
        ]);

        $response = $this->post('/login', [
            'login' => 'admin_test@servisac.com',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($admin);
        $response->assertRedirect('/admin');
    }

    /**
     * Test technician can login and is redirected to technician dashboard.
     */
    public function test_technician_can_login_and_redirect_to_technician_dashboard(): void
    {
        $tech = User::factory()->create([
            'email' => 'tech_test@servisac.com',
            'phone' => '081299998888',
            'role' => User::ROLE_TECHNICIAN,
            'password' => bcrypt('password123'),
        ]);

        // Login via nomor telepon
        $response = $this->post('/login', [
            'login' => '081299998888',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($tech);
        $response->assertRedirect('/teknisi');
    }

    /**
     * Test user cannot login with incorrect password.
     */
    public function test_user_cannot_login_with_incorrect_password(): void
    {
        User::factory()->create([
            'email' => 'wrong_pass@servisac.com',
            'password' => bcrypt('correct_password'),
        ]);

        $response = $this->post('/login', [
            'login' => 'wrong_pass@servisac.com',
            'password' => 'invalid_password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    /**
     * Test unauthenticated user is redirected to login.
     */
    public function test_unauthenticated_user_cannot_access_protected_routes(): void
    {
        $responseAdmin = $this->get('/admin');
        $responseAdmin->assertRedirect('/login');

        $responseTech = $this->get('/teknisi');
        $responseTech->assertRedirect('/login');
    }

    /**
     * Test technician cannot access admin dashboard.
     */
    public function test_technician_cannot_access_admin_dashboard(): void
    {
        $tech = User::factory()->create([
            'role' => User::ROLE_TECHNICIAN,
        ]);

        $response = $this->actingAs($tech)->get('/admin');

        $response->assertRedirect('/teknisi');
    }

    /**
     * Test authenticated user can logout.
     */
    public function test_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}
