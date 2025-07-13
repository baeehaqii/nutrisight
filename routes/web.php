<?php

use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\GeminiController;
use App\Http\Controllers\ImageProcessController;

Route::get('/', function () {
    return Inertia::render('welcome');
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        return Inertia::render('dashboard');
    })->name('dashboard');
});

Route::post('/gemini/analyze-product', [GeminiController::class, 'analyzeProduct']);


require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
