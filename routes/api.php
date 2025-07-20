<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\GetProfileController;
use App\Http\Controllers\Api\RiwayatPenyakitController;
use App\Http\Controllers\Api\ProfileController; // Tambahkan ini

// Public routes
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);
Route::get('/riwayat-penyakit', [RiwayatPenyakitController::class, 'index']);

// Protected routes (jika menggunakan Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', GetProfileController::class); 
    Route::post('/profile/update', [ProfileController::class, 'update']); // Endpoint untuk update
});
