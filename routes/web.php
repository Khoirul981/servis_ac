<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

// Halaman Beranda Publik & Form Pemesanan Servis DKM (Tanpa Login)
Route::get('/', [OrderController::class, 'create'])->name('home');
Route::get('/pesan', [OrderController::class, 'create'])->name('order.create');
Route::post('/pesan', [OrderController::class, 'store'])->name('order.store');
Route::get('/pesan/sukses/{order_number}', [OrderController::class, 'success'])->name('order.success');
Route::get('/api/masjid-autocomplete', [OrderController::class, 'searchMasjid'])->name('api.masjid.autocomplete');

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
