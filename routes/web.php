<?php

use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\Officer\FacilityController as OfficerFacilityController;
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
    // Stubs for missing routes
    Route::get('/stub-reservations-create', fn() => '')->name('reservations.create');
    Route::get('/stub-reservations-index', fn() => '')->name('reservations.index');
    Route::get('/stub-reports-create', fn() => '')->name('reports.create');
    Route::get('/stub-reports-index', fn() => '')->name('reports.index');

    // M3 Reservasi (Dhimas): reservations.*

    // M4 Laporan (Fazl): reports.*
});

/*
| Petugas — auth + role:petugas, prefix /officer, name officer. (dokumen route bagian 10)
*/
Route::middleware(['auth', 'role:petugas'])->prefix('officer')->name('officer.')->group(function () {
    // Stubs for missing routes
    Route::get('/stub-dashboard', fn() => '')->name('dashboard');
    Route::get('/stub-reservations', fn() => '')->name('reservations.index');
    Route::get('/stub-reports', fn() => '')->name('reports.index');

    // M3 Reservasi (Dhimas): officer.dashboard, officer.reservations.*

    // M4 Laporan (Fazl): officer.reports.*

    // M2 Fasilitas (Ferdy): officer.facilities.*
    Route::get('/facilities', [OfficerFacilityController::class, 'index'])->name('facilities.index');
    Route::patch('/facilities/{facility}/status', [OfficerFacilityController::class, 'status'])->name('facilities.status');
});

/*
| Admin — auth + role:admin, prefix /admin, name admin. (dokumen route bagian 11)
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // M2 Fasilitas (Ferdy): admin.facilities.*
    // A1, A2 - Master Fasilitas
    Route::get('/facilities', [AdminFacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [AdminFacilityController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [AdminFacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility}/edit', [AdminFacilityController::class, 'edit'])->name('facilities.edit');
    Route::patch('/facilities/{facility}', [AdminFacilityController::class, 'update'])->name('facilities.update');
    Route::patch('/facilities/{facility}/status', [AdminFacilityController::class, 'status'])->name('facilities.status');

    // Stubs for missing routes
    Route::get('/stub-users', fn() => '')->name('users.index');
    Route::get('/stub-recap', fn() => '')->name('recap.index');

    // M1 Auth & Akun (Elang): admin.users.*

    // M5 Rekap & Export (Fazl): admin.recap.*
});
