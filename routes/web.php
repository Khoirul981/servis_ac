<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Halaman Beranda Publik (Akan diarahkan ke form pemesanan DKM di Fase 3)
Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

// Autentikasi Pengguna (Admin & Teknisi)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Meja Pengelola Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');
});

// Dashboard Mobile Teknisi Lapangan
Route::middleware(['auth', 'role:technician'])->prefix('teknisi')->name('technician.')->group(function () {
    Route::get('/', function () {
        return view('technician.dashboard');
    })->name('dashboard');
});
