<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TechnicianController;
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
    Route::get('/', [TechnicianController::class, 'index'])->name('dashboard');
    Route::get('/order/{id}', [TechnicianController::class, 'show'])->name('order.show');
    Route::post('/order/{id}/mulai', [TechnicianController::class, 'startWork'])->name('order.start');
    Route::post('/order/{id}/foto', [TechnicianController::class, 'uploadPhoto'])->name('order.photo');
    Route::post('/order/{id}/sparepart', [TechnicianController::class, 'addSparepart'])->name('order.sparepart');
    Route::post('/order/{id}/selesai', [TechnicianController::class, 'completeWork'])->name('order.complete');
});
