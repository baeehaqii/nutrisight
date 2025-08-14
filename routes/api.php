<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\LoginController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\RegisterController;
use App\Http\Controllers\Api\GetProfileController;
use App\Http\Controllers\Api\ScanProdukController;
use App\Http\Controllers\Api\LupaPasswordController;
use App\Http\Controllers\Api\ResetPasswordController;
use App\Http\Controllers\Api\RiwayatPenyakitController;

// Public routes
Route::post('/register', [RegisterController::class, 'store']);
Route::post('/login', [LoginController::class, 'store']);
Route::post('/password/reset', [ResetPasswordController::class, 'reset']);
Route::get('/riwayat-penyakit', [RiwayatPenyakitController::class, 'index']);
Route::post('/lupa-password', [LupaPasswordController::class, 'resetDirect']);
Route::post('/check-email', [LupaPasswordController::class, 'checkEmail']);

// Protected routes (jika menggunakan Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/historyscan', [ScanProdukController::class, 'index']);
    Route::get('/historyscan/{id}', [ScanProdukController::class, 'show']);
    Route::post('/historyscan', [ScanProdukController::class, 'store']);
    Route::get('/profile', GetProfileController::class);
    Route::post('/profile/update', [ProfileController::class, 'update']);
    Route::get('/ocr-context', [ProfileController::class, 'getOcrContext']);
});
