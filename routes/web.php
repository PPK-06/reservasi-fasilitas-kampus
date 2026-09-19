<?php

use App\Http\Controllers\FacilityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ── Fasilitas ────────────────────────────────────────────────
Route::prefix('facilities')->name('facilities.')->group(function () {
    Route::get('/', [FacilityController::class, 'index'])->name('index');
});
