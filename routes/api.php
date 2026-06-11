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

    // Fitur Management Jadwal Perkuliahan (Admin)
    Route::get('/jadwal-perkuliahan', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'index']);
    Route::get('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'show']);
    Route::post('/jadwal-perkuliahan', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'store']);
    Route::put('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'update']);
    Route::delete('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'destroy']);

    // Fitur Peserta Kelas / Enrollment Mahasiswa
    Route::post('/peserta-kelas/enroll', [\App\Http\Controllers\Api\PesertaKelasController::class, 'enroll']);
    Route::get('/jadwal/{id_jadwal}/peserta', [\App\Http\Controllers\Api\PesertaKelasController::class, 'pesertaByJadwal']);

    // Fitur Sesi Pertemuan Kelas
    Route::get('/sesi-pertemuan', [\App\Http\Controllers\SesiPertemuanController::class, 'index']);
    Route::post('/sesi-pertemuan', [\App\Http\Controllers\SesiPertemuanController::class, 'store']);
    Route::get('/sesi-pertemuan/jadwal/{id_jadwal}', [\App\Http\Controllers\SesiPertemuanController::class, 'getByJadwal']);
    Route::get('/sesi-pertemuan/{id_sesi}', [\App\Http\Controllers\SesiPertemuanController::class, 'show']);
    Route::get('/sesi-pertemuan/{id_sesi}/aktif', [\App\Http\Controllers\SesiPertemuanController::class, 'cekSesiAktif']);
    Route::put('/sesi-pertemuan/{id_sesi}', [\App\Http\Controllers\SesiPertemuanController::class, 'update']);
    Route::delete('/sesi-pertemuan/{id_sesi}', [\App\Http\Controllers\SesiPertemuanController::class, 'destroy']);

    // Dashboard Stats (COUNT only — ringan, 1 request)
    Route::get('/dashboard/stats', [\App\Http\Controllers\Api\DashboardController::class, 'stats']);

    // ============================================================
    // Fitur Presensi Mahasiswa
    // ============================================================
    Route::post('/presensi/catat', [\App\Http\Controllers\Api\PresensiController::class, 'catat']);
    Route::put('/presensi/{id}/status', [\App\Http\Controllers\Api\PresensiController::class, 'updateStatus']);
    Route::get('/presensi/sesi/{id_sesi}', [\App\Http\Controllers\Api\PresensiController::class, 'getBySesi']);
    Route::get('/presensi/peserta/{id_peserta}', [\App\Http\Controllers\Api\PresensiController::class, 'getByPeserta']);
    Route::post('/presensi/persentase', [\App\Http\Controllers\Api\PresensiController::class, 'hitungPersentase']);
    Route::post('/presensi/rekap', [\App\Http\Controllers\Api\PresensiController::class, 'rekapKehadiran']);

    // ============================================================
    // Fitur Materi Pembelajaran
    // ============================================================
    Route::post('/materi/upload', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'upload']);
    Route::put('/materi/{id}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'update']);
    Route::delete('/materi/{id}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'hapus']);
    Route::get('/materi/sesi/{id_sesi}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'getBySesi']);
    Route::get('/materi/{id}/download', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'generateLinkDownload']);

    // ============================================================
    // Fitur Tugas
    // ============================================================

    // Dosen: CRUD Tugas di sesi tertentu
    Route::middleware('role:Dosen')->group(function () {
        Route::post('/sesi/{sesi_id}/tugas', [\App\Http\Controllers\Api\TugasController::class, 'store']);
        Route::put('/tugas/{id}', [\App\Http\Controllers\Api\TugasController::class, 'update']);
        Route::delete('/tugas/{id}', [\App\Http\Controllers\Api\TugasController::class, 'destroy']);
    });

    // Dosen & Mahasiswa: List tugas di sesi (GET shared)
    Route::middleware('role:Dosen,Mahasiswa')->group(function () {
        Route::get('/sesi/{sesi_id}/tugas', [\App\Http\Controllers\Api\TugasController::class, 'index']);
        Route::get('/tugas/{id}', [\App\Http\Controllers\Api\TugasController::class, 'show']);
        Route::get('/tugas/{id}/deadline', [\App\Http\Controllers\Api\TugasController::class, 'cekDeadline']);
        Route::get('/tugas/{id}/launch/{id_peserta}', [\App\Http\Controllers\Api\TugasController::class, 'getLaunchUrl']);
    });

    // Admin: List semua tugas
    Route::middleware('role:Admin')->group(function () {
        Route::get('/admin/tugas', [\App\Http\Controllers\Api\AdminTugasController::class, 'index']);
    });
});
