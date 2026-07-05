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
                ->withExists(['sesiPertemuan as has_tugas' => function ($query) {
                    $query->whereHas('tugas');
                }])
                ->withExists(['sesiPertemuan as has_materi' => function ($query) {
                    $query->whereHas('materiPembelajaran');
                }])
                ->whereIn('id_jadwal', $enrolledJadwalIds)
                ->get();
        } else {
            $jadwalDiambil = collect();
        }

        // 2. Ambil data jadwal untuk yang "Tersedia"
        $jadwalTersediaQuery = JadwalPerkuliahan::with(['mataKuliah', 'kelas', 'dosen'])
            ->withExists(['sesiPertemuan as has_tugas' => function ($query) {
                $query->whereHas('tugas');
            }])
            ->withExists(['sesiPertemuan as has_materi' => function ($query) {
                $query->whereHas('materiPembelajaran');
            }]);

        // Filter jadwal yang BELUM diikuti
        if (!empty($enrolledJadwalIds)) {
            $jadwalTersediaQuery->whereNotIn('id_jadwal', $enrolledJadwalIds);
        }

        // Removed automatic filter for Fakultas and Program Studi to allow all courses to show

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
                            ? request()->getSchemeAndHttpHost() . '/storage/' . $j->dosen->foto_profil
                            : 'https://api.dicebear.com/7.x/initials/png?seed=' . urlencode($j->dosen ? $j->dosen->nama_lengkap : 'Dosen') . '&backgroundColor=116E63&textColor=ffffff',
                'image' => $j->mataKuliah && $j->mataKuliah->banner 
                            ? request()->getSchemeAndHttpHost() . '/storage/' . $j->mataKuliah->banner
                            : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80',
                'sks' => $j->sks,
                'semester' => $j->semester,
                'tahun' => $j->tahun,
                'hari' => $j->hari,
                'kelas' => $j->kelas ? $j->kelas->nama_kelas : 'Unknown',
                'fakultas' => $j->fakultas,
                'prodi' => $j->prodi,
                'deskripsi' => $j->mataKuliah->deskripsi ?? 'Tidak ada deskripsi tersedia.',
                'has_tugas' => $j->has_tugas ?? false,
                'has_materi' => $j->has_materi ?? false,
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

    /**
     * Mengambil daftar mata kuliah yang tersedia untuk guest.
     */
    public function guestIndex(): JsonResponse
    {
        // Ambil data jadwal untuk yang "Tersedia"
        $jadwalTersedia = JadwalPerkuliahan::with(['mataKuliah', 'kelas', 'dosen'])
            ->withExists(['sesiPertemuan as has_tugas' => function ($query) {
                $query->whereHas('tugas');
            }])
            ->withExists(['sesiPertemuan as has_materi' => function ($query) {
                $query->whereHas('materiPembelajaran');
            }])->get();

        // Mapping data sesuai format Frontend
        $formatJadwal = function ($j) {
            return [
                'id' => $j->id_jadwal,
                'title' => $j->mataKuliah ? $j->mataKuliah->nama_mk : 'Tanpa Mata Kuliah',
                'type' => ($j->kelas ? $j->kelas->nama_kelas : 'Kelas') . ' - ' . $j->fakultas,
                'time' => $j->hari . ', ' . ($j->waktu_mulai ? substr($j->waktu_mulai, 0, 5) : '00:00') . ' - ' . ($j->waktu_berakhir ? substr($j->waktu_berakhir, 0, 5) : '00:00'),
                'dosen' => $j->dosen ? $j->dosen->nama_lengkap : 'Tanpa Dosen',
                'role' => 'Dosen pengampu',
                'avatar' => $j->dosen && $j->dosen->foto_profil 
                            ? request()->getSchemeAndHttpHost() . '/storage/' . $j->dosen->foto_profil
                            : 'https://api.dicebear.com/7.x/initials/png?seed=' . urlencode($j->dosen ? $j->dosen->nama_lengkap : 'Dosen') . '&backgroundColor=116E63&textColor=ffffff',
                'image' => $j->mataKuliah && $j->mataKuliah->banner 
                            ? request()->getSchemeAndHttpHost() . '/storage/' . $j->mataKuliah->banner
                            : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80',
                'sks' => $j->sks,
                'semester' => $j->semester,
                'tahun' => $j->tahun,
                'hari' => $j->hari,
                'kelas' => $j->kelas ? $j->kelas->nama_kelas : 'Unknown',
                'fakultas' => $j->fakultas,
                'prodi' => $j->prodi,
                'deskripsi' => $j->mataKuliah->deskripsi ?? 'Tidak ada deskripsi tersedia.',
                'has_tugas' => $j->has_tugas ?? false,
                'has_materi' => $j->has_materi ?? false,
            ];
        };

        $tersediaFormatted = $jadwalTersedia->map($formatJadwal)->values();

        return response()->json([
            'status' => 'success',
            'data' => [
                'diambil' => [],
                'tersedia' => $tersediaFormatted,
            ]
        ], 200);
    }
}
