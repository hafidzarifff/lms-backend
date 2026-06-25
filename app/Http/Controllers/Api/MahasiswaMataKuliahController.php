<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JadwalPerkuliahan;
use App\Models\PesertaKelas;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MahasiswaMataKuliahController extends Controller
{
    /**
     * Mengambil daftar mata kuliah yang sudah diambil dan yang tersedia untuk mahasiswa.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Ambil id_jadwal yang sudah diikuti oleh mahasiswa
        $enrolledPeserta = PesertaKelas::where('id_mahasiswa', $user->id_user)->get();
        $enrolledJadwalIds = $enrolledPeserta->pluck('id_jadwal')->filter()->toArray();

        // Ambil data jadwal untuk yang "Diambil"
        $jadwalDiambil = [];
        if (!empty($enrolledJadwalIds)) {
            $jadwalDiambil = JadwalPerkuliahan::with(['mataKuliah', 'kelas', 'dosen'])
                ->whereIn('id_jadwal', $enrolledJadwalIds)
                ->get();
        } else {
            $jadwalDiambil = collect();
        }

        // 2. Ambil data jadwal untuk yang "Tersedia"
        $jadwalTersediaQuery = JadwalPerkuliahan::with(['mataKuliah', 'kelas', 'dosen']);

        // Filter jadwal yang BELUM diikuti
        if (!empty($enrolledJadwalIds)) {
            $jadwalTersediaQuery->whereNotIn('id_jadwal', $enrolledJadwalIds);
        }

        // Filter berdasarkan Fakultas dan Program Studi Mahasiswa dengan ILIKE agar case-insensitive di PostgreSQL
        if ($user->fakultas) {
            $fakultasClean = str_ireplace('Fakultas ', '', $user->fakultas);
            $jadwalTersediaQuery->where('fakultas', 'ilike', '%' . $fakultasClean . '%');
        }
        if ($user->prodi) {
            $jadwalTersediaQuery->where('prodi', 'ilike', '%' . $user->prodi . '%');
        }

        $jadwalTersedia = $jadwalTersediaQuery->get();

        // 3. Mapping data sesuai format Frontend
        $formatJadwal = function ($j) {
            return [
                'id' => $j->id_jadwal,
                'title' => $j->mataKuliah ? $j->mataKuliah->nama_mk : 'Tanpa Mata Kuliah',
                'type' => ($j->kelas ? $j->kelas->nama_kelas : 'Kelas') . ' - ' . $j->fakultas,
                'time' => $j->hari . ', ' . ($j->waktu_mulai ? substr($j->waktu_mulai, 0, 5) : '00:00') . ' - ' . ($j->waktu_berakhir ? substr($j->waktu_berakhir, 0, 5) : '00:00'),
                'dosen' => $j->dosen ? $j->dosen->nama_lengkap : 'Tanpa Dosen',
                'role' => 'Dosen pengampu',
                'avatar' => $j->dosen && $j->dosen->foto_profil 
                            ? asset('storage/' . $j->dosen->foto_profil) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($j->dosen ? $j->dosen->nama_lengkap : 'Dosen') . '&background=random',
                'image' => $j->mataKuliah && $j->mataKuliah->banner 
                            ? asset('storage/' . $j->mataKuliah->banner) 
                            : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80',
                'sks' => $j->sks,
                'semester' => $j->semester,
                'tahun' => $j->tahun,
                'deskripsi' => $j->mataKuliah->deskripsi ?? 'Tidak ada deskripsi tersedia.',
            ];
        };

        $diambilFormatted = $jadwalDiambil->map($formatJadwal)->values();
        $tersediaFormatted = $jadwalTersedia->map($formatJadwal)->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'diambil' => $diambilFormatted,
                'tersedia' => $tersediaFormatted,
            ]
        ], 200);
    }
}
