<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PesertaKelas;
use App\Models\SesiPertemuan;
use App\Models\MateriPembelajaran;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MahasiswaDashboardController extends Controller
{
    /**
     * Mengambil data dashboard untuk mahasiswa yang login.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        // 1. Ambil data peserta kelas mahasiswa ini (jadwal yang diikuti)
        $peserta = PesertaKelas::with(['jadwal.mataKuliah', 'jadwal.dosen'])
            ->where('id_mahasiswa', $user->id_user)
            ->get();

        // 2. Kalkulasi Progress Pembelajaran
        // Menggunakan rata-rata kehadiran sebagai progress statis untuk contoh.
        // Jika belum ada data, kita asumsikan 0.
        $progressAverage = $peserta->avg('kehadiran') ?? 0;
        $progress = intval($progressAverage);
        
        // Asumsi hitung modul selesai: jumlah sesi yang sudah lewat atau sekedar dummy text sementara
        $completedModules = $peserta->where('kehadiran', '>', 0)->count(); 

        // 3. Jadwal Hari Ini
        $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $todayIndo = $days[now()->dayOfWeek];

        $jadwalHariIni = $peserta->map(function ($p) {
            return $p->jadwal;
        })->filter(function ($j) use ($todayIndo) {
            return $j && $j->hari === $todayIndo;
        })->map(function ($j) {
            return [
                'id' => $j->id_jadwal,
                'title' => $j->mataKuliah ? $j->mataKuliah->nama_mk : 'Tanpa Mata Kuliah',
                'date' => now()->translatedFormat('d F Y'), // e.g. 21 Januari 2026
                'time' => substr($j->waktu_mulai, 0, 5) . ' - ' . substr($j->waktu_berakhir, 0, 5),
                'lecturer' => $j->dosen ? $j->dosen->nama_lengkap : 'Tanpa Dosen',
                'role' => 'Dosen utama',
                'avatar' => $j->dosen && $j->dosen->foto_profil 
                            ? asset('storage/' . $j->dosen->foto_profil) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($j->dosen ? $j->dosen->nama_lengkap : 'Dosen') . '&background=random',
                'image' => $j->mataKuliah && $j->mataKuliah->banner 
                            ? asset('storage/' . $j->mataKuliah->banner) 
                            : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80',
            ];
        })->values();

        // 4. Materi Terbaru
        $idJadwals = $peserta->pluck('id_jadwal')->filter()->toArray();
        
        $materiTerbaru = collect();
        if (!empty($idJadwals)) {
            $idSesis = SesiPertemuan::whereIn('id_jadwal', $idJadwals)->pluck('id_sesi')->toArray();
            
            if (!empty($idSesis)) {
                $materi = MateriPembelajaran::whereIn('id_sesi', $idSesis)
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                    
                $materiTerbaru = $materi->map(function ($m) {
                    // Beri gambar default atau random dari unsplash sebagai thumbnail (karena DB materi tidak punya cover)
                    $images = [
                        'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&q=80',
                        'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=600&q=80',
                        'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80'
                    ];
                    return [
                        'id' => $m->id_materi,
                        'image' => $images[array_rand($images)],
                        'title' => $m->judul_materi,
                    ];
                });
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'user' => [
                    'nama_lengkap' => $user->nama_lengkap,
                    'foto_profil' => $user->foto_profil,
                ],
                'progress' => [
                    'percentage' => $progress,
                    'completed_modules' => $completedModules,
                ],
                'jadwal_hari_ini' => $jadwalHariIni,
                'materi_terbaru' => $materiTerbaru,
            ]
        ], 200);
    }
}
