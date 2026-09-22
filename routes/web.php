<?php

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\RegisterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

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

/*
| Pengguna — auth + role:pengguna, tanpa prefix (dokumen route bagian 9)
*/
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    // M3 Reservasi (Dhimas): reservations.*

    // M4 Laporan (Fazl): reports.*
});

/*
| Petugas — auth + role:petugas, prefix /officer, name officer. (dokumen route bagian 10)
*/
Route::middleware(['auth', 'role:petugas'])->prefix('officer')->name('officer.')->group(function () {
    // M3 Reservasi (Dhimas): officer.dashboard, officer.reservations.*

    // M4 Laporan (Fazl): officer.reports.*

    // M2 Fasilitas (Ferdy): officer.facilities.*
});

/*
| Admin — auth + role:admin, prefix /admin, name admin. (dokumen route bagian 11)
*/
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // M2 Fasilitas (Ferdy): admin.facilities.*

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
});
