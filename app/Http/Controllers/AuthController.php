<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    /**
     * Menampilkan formulir login.
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check()) {
            /** @var User $user */
            $user = Auth::user();
            return $user->isAdmin()
                ? redirect()->route('admin.dashboard')
                : redirect()->route('technician.dashboard');
        }

        return view('auth.login');
    }

    /**
     * Memproses autentikasi (bisa via Email atau No. HP).
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Email atau Nomor HP wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        $loginInput = trim($credentials['login']);
        $password = $credentials['password'];
        $remember = $request->boolean('remember');

        // Deteksi apakah input berupa alamat email atau nomor telepon
        $field = filter_var($loginInput, FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        if (Auth::attempt([$field => $loginInput, 'password' => $password], $remember)) {
            $request->session()->regenerate();

            /** @var User $user */
            $user = Auth::user();

            if ($user->isAdmin()) {
                return redirect()->intended(route('admin.dashboard'))
                    ->with('success', 'Selamat datang kembali, ' . $user->name . ' (Admin)!');
            }

            return redirect()->intended(route('technician.dashboard'))
                ->with('success', 'Selamat datang, ' . $user->name . ' (Teknisi Lapangan)!');
        }

        return back()->withInput($request->only('login', 'remember'))
            ->withErrors([
                'login' => 'Email/No. HP atau kata sandi tidak cocok. Silakan periksa kembali.',
            ]);
    }

    /**
     * Keluar dari sesi aplikasi.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}
