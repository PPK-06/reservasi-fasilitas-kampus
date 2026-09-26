<?php

use App\Http\Controllers\Admin\FacilityController as AdminFacilityController;
use App\Http\Controllers\Admin\RecapController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\Officer\DashboardController;
use App\Http\Controllers\Officer\FacilityController as OfficerFacilityController;
use App\Http\Controllers\Officer\ReportController as OfficerReportController;
use App\Http\Controllers\Officer\ReservationController as OfficerReservationController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/facilities');

/*
| Publik — tanpa prefix (dokumen route bagian 8)
*/
// M1 Auth & Akun (Elang): login, logout, register
Route::get('/login', [AuthController::class, 'create'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'store'])->middleware('guest')->name('login.store');
Route::post('/logout', [AuthController::class, 'destroy'])->middleware('auth')->name('logout');
Route::get('/register', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('/register', [RegisterController::class, 'store'])->middleware('guest')->name('register.store');

// M2 Fasilitas (Ferdy): facilities.index, facilities.show
Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
Route::get('/facilities/{facility}', [FacilityController::class, 'show'])->name('facilities.show');

/*
| Pengguna — auth + role:pengguna, tanpa prefix (dokumen route bagian 9)
*/
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    // M3 Reservasi (Dhimas): reservations.*
    Route::get('/reservations/create', [ReservationController::class, 'create'])->name('reservations.create');
    Route::get('/reservations/availability', [ReservationController::class, 'availability'])->name('reservations.availability');
    Route::post('/reservations', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::get('/reservations/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
    Route::patch('/reservations/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('reservations.cancel');

    // M4 Laporan (Fazl): reports.*
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
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
    Route::get('/reports', [OfficerReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [OfficerReportController::class, 'show'])->name('reports.show');
    Route::patch('/reports/{report}', [OfficerReportController::class, 'update'])->name('reports.update');

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

    // M1 Auth & Akun (Elang): admin.users.*
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}', [UserController::class, 'show'])->name('users.show');
    Route::patch('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::patch('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');
    Route::patch('/users/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
    Route::patch('/users/{user}/activate', [UserController::class, 'activate'])->name('users.activate');
    Route::patch('/users/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');

    // M5 Rekap & Export (Fazl): admin.recap.*
    Route::get('/recap', [RecapController::class, 'index'])->name('recap.index');
    Route::get('/recap/export', [RecapController::class, 'export'])->name('recap.export');
});
