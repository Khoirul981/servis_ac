<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MasterMasjidController;
use App\Http\Controllers\MasterUserController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\TechnicianController;
use Illuminate\Support\Facades\Route;

// Halaman Beranda Publik & Form Pemesanan Servis DKM (Tanpa Login)
Route::get('/', [OrderController::class, 'create'])->name('home');
Route::get('/pesan', [OrderController::class, 'create'])->name('order.create');
Route::post('/pesan', [OrderController::class, 'store'])->name('order.store');
Route::get('/pesan/lacak', [OrderController::class, 'track'])->name('order.track');
Route::get('/pesan/status/{order_number}', [OrderController::class, 'status'])->name('order.status');
Route::get('/pesan/kuitansi/{order_number}', [OrderController::class, 'publicReceipt'])->name('order.public_receipt');
Route::get('/pesan/sukses/{order_number}', [OrderController::class, 'success'])->name('order.success');
Route::get('/api/masjid-autocomplete', [OrderController::class, 'searchMasjid'])->name('api.masjid.autocomplete');

// Autentikasi Pengguna (Admin & Teknisi)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Meja Pengelola Admin (Role: admin)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/order/{id}/assign', [AdminController::class, 'assignTechnician'])->name('order.assign');
    Route::post('/order/{id}/pay', [AdminController::class, 'confirmPayment'])->name('order.pay');
    Route::post('/order/{id}/cancel', [AdminController::class, 'cancelOrder'])->name('order.cancel');
    Route::get('/order/{id}/receipt', [AdminController::class, 'receipt'])->name('order.receipt');

    // Master Masjid & Unit AC
    Route::get('/masjids', [MasterMasjidController::class, 'index'])->name('masjids.index');
    Route::post('/masjids', [MasterMasjidController::class, 'store'])->name('masjids.store');
    Route::get('/masjids/{id}', [MasterMasjidController::class, 'show'])->name('masjids.show');
    Route::put('/masjids/{id}', [MasterMasjidController::class, 'update'])->name('masjids.update');
    Route::delete('/masjids/{id}', [MasterMasjidController::class, 'destroy'])->name('masjids.destroy');

    Route::post('/masjids/{id}/ac', [MasterMasjidController::class, 'storeAcUnit'])->name('masjids.ac.store');
    Route::put('/ac/{id}', [MasterMasjidController::class, 'updateAcUnit'])->name('masjids.ac.update');
    Route::delete('/ac/{id}', [MasterMasjidController::class, 'destroyAcUnit'])->name('masjids.ac.destroy');

    // Master Petugas
    Route::get('/users', [MasterUserController::class, 'index'])->name('users.index');
    Route::post('/users', [MasterUserController::class, 'store'])->name('users.store');
    Route::put('/users/{id}', [MasterUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [MasterUserController::class, 'destroy'])->name('users.destroy');

    // Manajemen Foto Bukti Servis oleh Admin
    Route::put('/order/foto/{photo_id}', [AdminController::class, 'updatePhoto'])->name('order.photo.update');
    Route::delete('/order/foto/{photo_id}', [AdminController::class, 'destroyPhoto'])->name('order.photo.destroy');
});

// Dashboard Mobile Teknisi Lapangan (Role: technician)
Route::middleware(['auth', 'role:technician'])->prefix('teknisi')->name('technician.')->group(function () {
    Route::get('/', [TechnicianController::class, 'index'])->name('dashboard');
    Route::get('/order/{id}', [TechnicianController::class, 'show'])->name('order.show');
    Route::post('/order/{id}/mulai', [TechnicianController::class, 'startWork'])->name('order.start');
    Route::post('/order/{id}/batal-mulai', [TechnicianController::class, 'revertWork'])->name('order.revert');
    Route::post('/order/{id}/foto', [TechnicianController::class, 'uploadPhoto'])->name('order.photo');
    Route::put('/order/foto/{photo_id}', [TechnicianController::class, 'updatePhoto'])->name('order.photo.update');
    Route::delete('/order/foto/{photo_id}', [TechnicianController::class, 'destroyPhoto'])->name('order.photo.destroy');
    Route::post('/order/{id}/sparepart', [TechnicianController::class, 'addSparepart'])->name('order.sparepart');
    Route::put('/order/sparepart/{sparepart_id}', [TechnicianController::class, 'updateSparepart'])->name('order.sparepart.update');
    Route::delete('/order/sparepart/{sparepart_id}', [TechnicianController::class, 'destroySparepartItem'])->name('order.sparepart.item.destroy');
    Route::delete('/order/{id}/sparepart', [TechnicianController::class, 'removeSparepart'])->name('order.sparepart.destroy');
    Route::post('/order/{id}/selesai', [TechnicianController::class, 'completeWork'])->name('order.complete');
});
