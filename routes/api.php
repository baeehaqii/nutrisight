<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\ProfileController; // Tambahkan ini
use App\Http\Controllers\Api\RiwayatPenyakitController;

// Public routes
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);
Route::get('/riwayat-penyakit', [RiwayatPenyakitController::class, 'index']);

// Protected routes (jika menggunakan Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::post('/profile/update', [ProfileController::class, 'update']); // Endpoint untuk update
});
