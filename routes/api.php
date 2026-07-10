<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\ForgotPasswordController;
use App\Http\Controllers\Api\GoogleAuthController;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
Route::post('/register/dosen', [AuthController::class, 'registerDosen']);
Route::post('/register/mahasiswa', [AuthController::class, 'registerMahasiswa']);
Route::get('/public/download', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'publicDownload']);
Route::get('/public/mata-kuliah', [\App\Http\Controllers\Api\MahasiswaMataKuliahController::class, 'guestIndex']);
Route::get('/public/sesi-pertemuan/jadwal/{id_jadwal}', [\App\Http\Controllers\SesiPertemuanController::class, 'getByJadwal']);

// Forgot Password (Public)
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendLink']);
Route::post('/reset-password', [ForgotPasswordController::class, 'reset']);

// Google Login (Public)
Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect']);
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback']);

// Public Template Sertifikat Background (CORS enabled for Canvas drawing)
Route::get('/template-sertifikat/{id_template}/download-background', [\App\Http\Controllers\TemplateSertifikatController::class, 'downloadBackground']);

// Public Verifikasi Sertifikat
Route::get('/sertifikat/verify/{nomor_sertifikat}', [\App\Http\Controllers\SertifikatController::class, 'verify'])->where('nomor_sertifikat', '.*');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Notifikasi
    Route::get('/notifikasi', [\App\Http\Controllers\Api\NotifikasiController::class, 'index']);
    Route::put('/notifikasi/{id}/read', [\App\Http\Controllers\Api\NotifikasiController::class, 'markAsRead']);

    // Profile & Password Management
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::post('/profile/foto', [ProfileController::class, 'uploadFoto']);
    Route::put('/profile/password', [ProfileController::class, 'changePassword']);
    
    // Mahasiswa Onboarding
    Route::put('/mahasiswa/onboarding', [ProfileController::class, 'onboarding']);

    // Fitur Verifikasi Dosen (Admin)
    Route::get('/verifikasi-dosen', [\App\Http\Controllers\Api\VerifikasiDosenController::class, 'index']);
    Route::put('/verifikasi-dosen/{id}', [\App\Http\Controllers\Api\VerifikasiDosenController::class, 'updateStatus']);

    // Fitur Management Mahasiswa (Admin)
    Route::post('/mahasiswa', [\App\Http\Controllers\Api\MahasiswaController::class, 'store']);
    Route::get('/mahasiswa', [\App\Http\Controllers\Api\MahasiswaController::class, 'index']);
    
    // Mahasiswa Dashboard & Mata Kuliah (Harus di atas /mahasiswa/{id} agar tidak tertangkap sebagai parameter ID)
    Route::get('/mahasiswa/dashboard', [\App\Http\Controllers\Api\MahasiswaDashboardController::class, 'index']);
    Route::get('/mahasiswa/search', [\App\Http\Controllers\Api\MahasiswaDashboardController::class, 'search']);
    Route::get('/mahasiswa/mata-kuliah', [\App\Http\Controllers\Api\MahasiswaMataKuliahController::class, 'index']);
    Route::get('/mahasiswa/jadwal-kelas', [\App\Http\Controllers\Api\MahasiswaDashboardController::class, 'jadwalKelas']);
    Route::get('/mahasiswa/progress-belajar', [\App\Http\Controllers\Api\MahasiswaDashboardController::class, 'progressBelajar']);
    Route::get('/mahasiswa/nilai', [\App\Http\Controllers\Api\MahasiswaDashboardController::class, 'nilai']);

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
    Route::get('/jadwal-perkuliahan/grouped', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'grouped']);
    Route::get('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'show']);
    Route::post('/jadwal-perkuliahan', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'store']);
    Route::put('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'update']);
    Route::put('/jadwal-perkuliahan/{id_jadwal}/akses-bebas', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'toggleAksesBebas']);
    Route::delete('/jadwal-perkuliahan/{id_jadwal}', [\App\Http\Controllers\Api\JadwalPerkuliahanController::class, 'destroy']);

    // Fitur Peserta Kelas / Enrollment Mahasiswa
    Route::post('/peserta-kelas/enroll', [\App\Http\Controllers\Api\PesertaKelasController::class, 'enroll']);
    Route::get('/jadwal/{id_jadwal}/peserta', [\App\Http\Controllers\Api\PesertaKelasController::class, 'pesertaByJadwal']);
    Route::get('/dosen/monitoring-progres/{id_jadwal}', [\App\Http\Controllers\Api\PesertaKelasController::class, 'monitoringProgres']);
    Route::get('/dosen/verifikasi-sertifikat', [\App\Http\Controllers\Api\PesertaKelasController::class, 'listVerifikasiSertifikat']);
    Route::put('/dosen/verifikasi-sertifikat/{id_peserta}', [\App\Http\Controllers\Api\PesertaKelasController::class, 'updateStatusKelayakan']);

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

    // Fitur Presensi Mahasiswa
    // ============================================================
    Route::post('/presensi/bulk-save', [\App\Http\Controllers\Api\PresensiController::class, 'bulkSave']);
    Route::post('/presensi/catat', [\App\Http\Controllers\Api\PresensiController::class, 'catat']);
    Route::put('/presensi/{id}/status', [\App\Http\Controllers\Api\PresensiController::class, 'updateStatus']);
    Route::get('/presensi/sesi/{id_sesi}', [\App\Http\Controllers\Api\PresensiController::class, 'getBySesi']);
    Route::get('/presensi/peserta/{id_peserta}', [\App\Http\Controllers\Api\PresensiController::class, 'getByPeserta']);
    Route::post('/presensi/persentase', [\App\Http\Controllers\Api\PresensiController::class, 'hitungPersentase']);
    Route::post('/presensi/rekap', [\App\Http\Controllers\Api\PresensiController::class, 'rekapKehadiran']);
    Route::get('/presensi/sesi/{id_sesi}/saya', [\App\Http\Controllers\Api\PresensiController::class, 'getSesiSaya']);
    Route::post('/presensi/hadir-sendiri', [\App\Http\Controllers\Api\PresensiController::class, 'markHadirSendiri']);

    // ============================================================
    // Fitur Materi Pembelajaran
    // ============================================================
    Route::get('/materi/download', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'downloadFile']);
    Route::post('/materi/upload', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'upload']);
    Route::put('/materi/{id}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'update']);
    Route::delete('/materi/{id}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'hapus']);
    Route::get('/materi/sesi/{id_sesi}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'getBySesi']);
    Route::get('/materi/jadwal/{id_jadwal}', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'getByJadwal']);
    Route::get('/materi/{id}/download', [\App\Http\Controllers\Api\MateriPembelajaranController::class, 'generateLinkDownload']);

    // ============================================================
    // Fitur Tugas
    // ============================================================

    // Dosen: CRUD Tugas di sesi tertentu
    Route::middleware('role:Dosen')->group(function () {
        Route::post('/sesi/{sesi_id}/tugas', [\App\Http\Controllers\Api\TugasController::class, 'store']);
        Route::put('/tugas/{id}', [\App\Http\Controllers\Api\TugasController::class, 'update']);
        Route::delete('/tugas/{id}', [\App\Http\Controllers\Api\TugasController::class, 'destroy']);
        
        // Hasil Evaluasi
        Route::get('/dosen/{id_dosen}/hasil-evaluasi', [\App\Http\Controllers\JawabanEvaluasiController::class, 'getHasilByDosen']);
        
        // Dosen Dashboard Stats
        Route::get('/dashboard/dosen/{id_dosen}', [\App\Http\Controllers\Api\DashboardController::class, 'dosenStats']);
    });

    // Dosen & Mahasiswa: List tugas di sesi (GET shared)
    Route::middleware('role:Dosen,Mahasiswa')->group(function () {
        Route::get('/sesi/{sesi_id}/tugas', [\App\Http\Controllers\Api\TugasController::class, 'index']);
        Route::get('/tugas/jadwal/{id_jadwal}', [\App\Http\Controllers\Api\TugasController::class, 'getByJadwal']);
        Route::get('/tugas/{id}', [\App\Http\Controllers\Api\TugasController::class, 'show']);
        Route::get('/tugas/{id}/deadline', [\App\Http\Controllers\Api\TugasController::class, 'cekDeadline']);
        Route::get('/tugas/{id}/launch/{id_peserta}', [\App\Http\Controllers\Api\TugasController::class, 'getLaunchUrl']);
    });

    // Admin: List semua tugas
    Route::middleware('role:Admin')->group(function () {
        Route::get('/admin/tugas', [\App\Http\Controllers\Api\AdminTugasController::class, 'index']);
    });

    // ============================================================
    // Fitur Forum Diskusi
    // ============================================================
    Route::get('/forum/debug', function(\Illuminate\Http\Request $request) {
        // Mock Auth for testing
        $user = \App\Models\Pengguna::where('nama_lengkap', 'Dr. Budi Santoso')->first();
        Auth::login($user);

        $controller = new \App\Http\Controllers\Api\ForumDiskusiController();
        return $controller->getAllForDosen($request);
    });
    Route::get('/forum/dosen/all', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'getAllForDosen']);
    Route::get('/sesi/{idSesi}/forum', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'index']);
    Route::post('/sesi/{idSesi}/forum/read', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'markAsRead']);
    Route::get('/jadwal/{idJadwal}/forum', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'getByJadwal']);
    Route::post('/forum', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'store']);
    Route::get('/forum/{idPesan}', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'show']);
    Route::get('/forum/{idPesan}/replies', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'getReplies']);
    Route::put('/forum/{idPesan}', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'update']);
    Route::delete('/forum/{idPesan}', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'destroy']);
    Route::get('/sesi/{idSesi}/forum/search', [\App\Http\Controllers\Api\ForumDiskusiController::class, 'search']);

    // ============================================================
    // Fitur Nilai CBT
    // ============================================================
    Route::post('/nilai-cbt', [\App\Http\Controllers\NilaiCbtController::class, 'store']);
    Route::get('/nilai-cbt/tugas/{id_tugas}', [\App\Http\Controllers\NilaiCbtController::class, 'getByTugas']);
    Route::get('/nilai-cbt/peserta/{id_peserta}', [\App\Http\Controllers\NilaiCbtController::class, 'getByPeserta']);
    Route::get('/nilai-cbt/{id_tugas}/{id_peserta}', [\App\Http\Controllers\NilaiCbtController::class, 'show']);
    Route::put('/nilai-cbt/{id_nilai}', [\App\Http\Controllers\NilaiCbtController::class, 'update']);
    Route::delete('/nilai-cbt/{id_nilai}', [\App\Http\Controllers\NilaiCbtController::class, 'destroy']);
    Route::get('/nilai-cbt/tugas/{id_tugas}/statistik', [\App\Http\Controllers\NilaiCbtController::class, 'getStatistik']);
    Route::get('/nilai-cbt/tugas/{id_tugas}/ranking/{limit?}', [\App\Http\Controllers\NilaiCbtController::class, 'getRanking']);

    // ============================================================
    // Fitur Pertanyaan Evaluasi
    // ============================================================
    Route::get('/pertanyaan-evaluasi', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'index']);
    Route::get('/pertanyaan-evaluasi/aktif', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'getAktif']);
    Route::get('/pertanyaan-evaluasi/kategori', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'getKategori']);
    Route::get('/pertanyaan-evaluasi/{id_pertanyaan}', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'show']);
    Route::post('/pertanyaan-evaluasi', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'store']);
    Route::put('/pertanyaan-evaluasi/{id_pertanyaan}', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'update']);
    Route::delete('/pertanyaan-evaluasi/{id_pertanyaan}', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'destroy']);
    Route::put('/pertanyaan-evaluasi/{id_pertanyaan}/toggle', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'toggleAktif']);
    Route::post('/pertanyaan-evaluasi/bulk-urutan', [\App\Http\Controllers\PertanyaanEvaluasiController::class, 'bulkUpdateUrutan']);

    // ============================================================
    // Fitur Jawaban Evaluasi
    // ============================================================
    Route::post('/jawaban-evaluasi', [\App\Http\Controllers\JawabanEvaluasiController::class, 'store']);
    Route::get('/jawaban-evaluasi/peserta/{id_peserta}', [\App\Http\Controllers\JawabanEvaluasiController::class, 'getByPeserta']);
    Route::get('/jawaban-evaluasi/pertanyaan/{id_pertanyaan}', [\App\Http\Controllers\JawabanEvaluasiController::class, 'getByPertanyaan']);
    Route::get('/jawaban-evaluasi/{id_pertanyaan}/{id_peserta}', [\App\Http\Controllers\JawabanEvaluasiController::class, 'show']);
    Route::put('/jawaban-evaluasi/{id_evaluasi}', [\App\Http\Controllers\JawabanEvaluasiController::class, 'update']);
    Route::delete('/jawaban-evaluasi/{id_evaluasi}', [\App\Http\Controllers\JawabanEvaluasiController::class, 'destroy']);
    Route::get('/jawaban-evaluasi/pertanyaan/{id_pertanyaan}/statistik', [\App\Http\Controllers\JawabanEvaluasiController::class, 'getStatistikPertanyaan']);
    Route::get('/jawaban-evaluasi/statistik-kategori', [\App\Http\Controllers\JawabanEvaluasiController::class, 'getStatistikKategori']);
    Route::get('/jawaban-evaluasi/peserta/{id_peserta}/status', [\App\Http\Controllers\JawabanEvaluasiController::class, 'checkStatus']);
    Route::get('/jawaban-evaluasi/rekap', [\App\Http\Controllers\JawabanEvaluasiController::class, 'getRekap']);

    // ============================================================
    // Fitur Template Sertifikat
    // ============================================================
    Route::get('/template-sertifikat', [\App\Http\Controllers\TemplateSertifikatController::class, 'index']);
    Route::get('/template-sertifikat/aktif', [\App\Http\Controllers\TemplateSertifikatController::class, 'getAktif']);
    Route::get('/template-sertifikat/{id_template}', [\App\Http\Controllers\TemplateSertifikatController::class, 'show']);
    Route::post('/template-sertifikat', [\App\Http\Controllers\TemplateSertifikatController::class, 'store']);
    Route::put('/template-sertifikat/{id_template}', [\App\Http\Controllers\TemplateSertifikatController::class, 'update']);
    Route::delete('/template-sertifikat/{id_template}', [\App\Http\Controllers\TemplateSertifikatController::class, 'destroy']);
    Route::put('/template-sertifikat/{id_template}/toggle', [\App\Http\Controllers\TemplateSertifikatController::class, 'toggleAktif']);
    Route::post('/template-sertifikat/{id_template}/background', [\App\Http\Controllers\TemplateSertifikatController::class, 'uploadBackground']);

    // ============================================================
    // Fitur Sertifikat
    // ============================================================
    Route::get('/sertifikat', [\App\Http\Controllers\SertifikatController::class, 'index']);
    Route::get('/sertifikat/grouped', [\App\Http\Controllers\SertifikatController::class, 'grouped']);
    Route::get('/sertifikat/peserta/{id_peserta}', [\App\Http\Controllers\SertifikatController::class, 'getByPeserta']);
    Route::get('/sertifikat/{id_sertifikat}', [\App\Http\Controllers\SertifikatController::class, 'show']);
    Route::post('/sertifikat', [\App\Http\Controllers\SertifikatController::class, 'store']);
    Route::post('/sertifikat/bulk', [\App\Http\Controllers\SertifikatController::class, 'storeBulk']);
    Route::put('/sertifikat/{id_sertifikat}', [\App\Http\Controllers\SertifikatController::class, 'update']);
    Route::delete('/sertifikat/{id_sertifikat}', [\App\Http\Controllers\SertifikatController::class, 'destroy']);
    Route::post('/sertifikat/{id_sertifikat}/upload', [\App\Http\Controllers\SertifikatController::class, 'uploadFile']);
    Route::get('/sertifikat/{id_sertifikat}/download', [\App\Http\Controllers\SertifikatController::class, 'download']);
    Route::get('/sertifikat/statistik', [\App\Http\Controllers\SertifikatController::class, 'getStatistik']);
});
