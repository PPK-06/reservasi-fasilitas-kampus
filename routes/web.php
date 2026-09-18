<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
| Publik — tanpa prefix (dokumen route bagian 8)
*/
// M1 Auth & Akun (Elang): login, logout, register
Route::get('/login', [AuthController::class, 'create'])->middleware('guest')->name('login');

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

    // M5 Rekap & Export (Fazl): admin.recap.*
});
