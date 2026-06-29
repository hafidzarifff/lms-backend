<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MasterKelas;
use App\Models\MasterMataKuliah;
use App\Models\Pengguna;
use App\Models\Sertifikat;
use App\Models\ForumDiskusi;
use App\Models\SesiPertemuan;
use App\Models\JadwalPerkuliahan;
use App\Models\PesertaKelas;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Mengambil data statistik dashboard dalam satu request.
     * Menggunakan COUNT query (bukan load semua data) agar ringan dan cepat.
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        $stats = [
            'mahasiswa'   => Pengguna::where('role', 'Mahasiswa')->count(),
            'dosen'       => Pengguna::where('role', 'Dosen')
                                     ->where('status_persetujuan', 'Disetujui')
                                     ->count(),
            'kelas'       => MasterKelas::count(),
            'mata_kuliah' => MasterMataKuliah::count(),
            'sertifikat'  => Sertifikat::count(),
        ];

        // 10 pengajuan dosen terbaru untuk widget verifikasi
        $dosenTerbaru = Pengguna::where('role', 'Dosen')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // 5 forum terbaru
        $forumTerbaru = ForumDiskusi::with([
            'pengirim:id_user,nama_lengkap,nomor_induk,role',
            'sesi.jadwalPerkuliahan.mataKuliah:id_mk,nama_mk',
            'sesi.jadwalPerkuliahan.kelas:id_kelas,nama_kelas'
        ])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        return response()->json([
            'stats'         => $stats,
            'dosen_terbaru' => $dosenTerbaru,
            'forum_terbaru' => $forumTerbaru,
        ], 200);
    }

    /**
     * Mengambil data statistik dashboard spesifik untuk seorang Dosen.
     *
     * @param string $id_dosen
     * @return JsonResponse
     */
    public function dosenStats($id_dosen): JsonResponse
    {
        // Total mahasiswa (unik) yang mendaftar di kelas-kelas yang diajar Dosen ini
        $totalMahasiswa = PesertaKelas::join('jadwal_perkuliahan', 'peserta_kelas.id_jadwal', '=', 'jadwal_perkuliahan.id_jadwal')
            ->where('jadwal_perkuliahan.id_dosen', $id_dosen)
            ->distinct('peserta_kelas.id_peserta')
            ->count('peserta_kelas.id_peserta');

        // Jumlah Mata Kuliah & Kelas Aktif
        $jadwalAktif = JadwalPerkuliahan::where('id_dosen', $id_dosen)->get();
        $mataKuliahAktif = $jadwalAktif->pluck('id_mk')->unique()->count();
        $kelasAktif = $jadwalAktif->pluck('id_kelas')->unique()->count();

        // Sertifikat Perlu Verifikasi
        $sertifikatPerluVerifikasi = PesertaKelas::join('jadwal_perkuliahan', 'peserta_kelas.id_jadwal', '=', 'jadwal_perkuliahan.id_jadwal')
            ->where('jadwal_perkuliahan.id_dosen', $id_dosen)
            ->where('peserta_kelas.status_kelayakan', 'Belum Ditentukan')
            ->count();

        // Jadwal Sesi Pertemuan Hari Ini
        $today = now()->toDateString();
        $sesiHariIni = SesiPertemuan::with(['jadwalPerkuliahan.mataKuliah', 'jadwalPerkuliahan.kelas'])
            ->whereHas('jadwalPerkuliahan', function ($q) use ($id_dosen) {
                $q->where('id_dosen', $id_dosen);
            })
            ->whereDate('tanggal_pelaksanaan', $today)
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $sesiHariIni->each(function ($sesi) {
            $sesi->is_aktif = $sesi->cekSesiAktif();
        });

        // Interaksi Forum Diskusi Mahasiswa Terbaru (5 terakhir dari kelas dosen ini)
        $forumTerbaru = ForumDiskusi::with([
            'pengirim:id_user,nama_lengkap,nomor_induk,role',
            'sesi.jadwalPerkuliahan.mataKuliah:id_mk,nama_mk',
            'sesi.jadwalPerkuliahan.kelas:id_kelas,nama_kelas'
        ])
        ->whereHas('sesi.jadwalPerkuliahan', function ($q) use ($id_dosen) {
            $q->where('id_dosen', $id_dosen);
        })
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => [
                    'total_mahasiswa' => $totalMahasiswa,
                    'mata_kuliah_aktif' => $mataKuliahAktif,
                    'kelas_aktif' => $kelasAktif,
                    'sertifikat_perlu_verifikasi' => $sertifikatPerluVerifikasi,
                ],
                'sesi_hari_ini' => $sesiHariIni,
                'forum_terbaru' => $forumTerbaru,
            ]
        ], 200);
    }
}
