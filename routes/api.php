<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register/dosen', [AuthController::class, 'registerDosen']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Fitur Verifikasi Dosen (Admin)
    Route::get('/verifikasi-dosen', [\App\Http\Controllers\Api\VerifikasiDosenController::class, 'index']);
    Route::put('/verifikasi-dosen/{id}', [\App\Http\Controllers\Api\VerifikasiDosenController::class, 'updateStatus']);

    // Fitur Management Mahasiswa (Admin)
    Route::post('/mahasiswa', [\App\Http\Controllers\Api\MahasiswaController::class, 'store']);
    Route::get('/mahasiswa', [\App\Http\Controllers\Api\MahasiswaController::class, 'index']);
    Route::get('/mahasiswa/{id}', [\App\Http\Controllers\Api\MahasiswaController::class, 'show']);
    Route::put('/mahasiswa/{id}', [\App\Http\Controllers\Api\MahasiswaController::class, 'update']);
    Route::delete('/mahasiswa/{id}', [\App\Http\Controllers\Api\MahasiswaController::class, 'destroy']);
});
