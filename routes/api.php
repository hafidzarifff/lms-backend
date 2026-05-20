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

    // Fitur Management Dosen (Admin)
    Route::put('/dosen/{id}', [\App\Http\Controllers\Api\DosenController::class, 'update']);
    Route::delete('/dosen/{id}', [\App\Http\Controllers\Api\DosenController::class, 'destroy']);

    // Fitur Management Master Kelas (Admin)
    Route::get('/kelas', [\App\Http\Controllers\Api\KelasController::class, 'index']);
    Route::get('/kelas/{id_kelas}', [\App\Http\Controllers\Api\KelasController::class, 'show']);
    Route::post('/kelas', [\App\Http\Controllers\Api\KelasController::class, 'store']);
    Route::put('/kelas/{id_kelas}', [\App\Http\Controllers\Api\KelasController::class, 'update']);
    Route::delete('/kelas/{id_kelas}', [\App\Http\Controllers\Api\KelasController::class, 'destroy']);

    // Fitur Management Master Mata Kuliah (Admin)
    Route::get('/mata-kuliah', [\App\Http\Controllers\Api\MataKuliahController::class, 'index']);
    Route::get('/mata-kuliah/{id_mk}', [\App\Http\Controllers\Api\MataKuliahController::class, 'show']);
    Route::post('/mata-kuliah', [\App\Http\Controllers\Api\MataKuliahController::class, 'store']);
    Route::put('/mata-kuliah/{id_mk}', [\App\Http\Controllers\Api\MataKuliahController::class, 'update']);
    Route::delete('/mata-kuliah/{id_mk}', [\App\Http\Controllers\Api\MataKuliahController::class, 'destroy']);

    // Fitur CRUD Jadwal Perkuliahan (Admin)
    Route::get('/jadwal-perkuliahan', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'index']);
    Route::get('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'show']);
    Route::post('/jadwal-perkuliahan', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'store']);
    Route::put('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'update']);
    Route::delete('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'destroy']);
});
