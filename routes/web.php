<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\Officer\DashboardController;
use App\Http\Controllers\Officer\ReservationController as OfficerReservationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/facilities');

/*
| Publik — tanpa prefix (dokumen route bagian 8)
*/
// M1 Auth & Akun (Elang): login, logout, register
Route::get('/login', [AuthController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware('guest')->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');

// M2 Fasilitas (Ferdy): facilities.index, facilities.show
Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');

/*
| Pengguna — auth + role:pengguna, tanpa prefix (dokumen route bagian 9)
*/
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    // M3 Reservasi (Dhimas): reservations.*
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

    // M4 Laporan (Fazl): reports.*
});

/*
| Petugas — auth + role:petugas, prefix /officer, name officer. (dokumen route bagian 10)
*/
Route::middleware(['auth', 'role:petugas'])->prefix('officer')->name('officer.')->group(function () {
    // M3 Reservasi (Dhimas): officer.dashboard, officer.reservations.*
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/reservations', [OfficerReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [OfficerReservationController::class, 'show'])->name('reservations.show');
    Route::patch('/reservations/{reservation}/approve', [OfficerReservationController::class, 'approve'])->name('reservations.approve');
    Route::patch('/reservations/{reservation}/reject', [OfficerReservationController::class, 'reject'])->name('reservations.reject');
    Route::patch('/reservations/{reservation}/cancel', [OfficerReservationController::class, 'cancel'])->name('reservations.cancel');

    // M4 Laporan (Fazl): officer.reports.*

    // M2 Fasilitas (Ferdy): officer.facilities.*
});

/*
| Admin — auth + role:admin, prefix /admin, name admin. (dokumen route bagian 11)
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // M2 Fasilitas (Ferdy): admin.facilities.*

    // M1 Auth & Akun (Elang): admin.users.*

    // M5 Rekap & Export (Fazl): admin.recap.*
});
