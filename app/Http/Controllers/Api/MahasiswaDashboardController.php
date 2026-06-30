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
        $idJadwals = $peserta->pluck('id_jadwal')->filter()->toArray();
        $totalSesi = 0;
        $totalTugas = 0;
        $jumlahHadir = 0;
        $jumlahTugasDikerjakan = 0;

        if (!empty($idJadwals)) {
            $sesiPertemuan = \App\Models\SesiPertemuan::whereIn('id_jadwal', $idJadwals)->get();
            $sesiIds = $sesiPertemuan->pluck('id_sesi')->toArray();
            $totalSesi = count($sesiIds);
            
            if ($totalSesi > 0) {
                $pesertaIds = $peserta->pluck('id_peserta')->toArray();
                
                $jumlahHadir = \App\Models\Presensi::whereIn('id_sesi', $sesiIds)
                    ->whereIn('id_peserta', $pesertaIds)
                    ->where('status_kehadiran', 'hadir')
                    ->count();
                
                $tugasList = \App\Models\Tugas::whereIn('id_sesi', $sesiIds)->get();
                $totalTugas = $tugasList->count();
                $tugasIds = $tugasList->pluck('id_tugas')->toArray();
                
                if ($totalTugas > 0) {
                    $jumlahTugasDikerjakan = \App\Models\NilaiCbt::whereIn('id_tugas', $tugasIds)
                        ->whereIn('id_peserta', $pesertaIds)
                        ->count();
                }
            }
        }

        $totalItems = $totalTugas + $totalSesi;
        $progress = 0;
        if ($totalItems > 0) {
            $progress = round((($jumlahTugasDikerjakan + $jumlahHadir) / $totalItems) * 100);
        }
        
        $completedModules = $jumlahTugasDikerjakan + $jumlahHadir; 

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
                    ->with(['sesiPertemuan.jadwalPerkuliahan.mataKuliah', 'sesiPertemuan.jadwalPerkuliahan.dosen', 'sesiPertemuan.jadwalPerkuliahan.kelas'])
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get();
                    
                $materiTerbaru = $materi->map(function ($m) {
                    $images = [
                        'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&q=80',
                        'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=600&q=80',
                        'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80'
                    ];
                    $jadwal = $m->sesiPertemuan?->jadwalPerkuliahan;
                    $sesi = $m->sesiPertemuan;
                    
                    $timeString = '-';
                    if ($sesi && $sesi->tanggal_pelaksanaan) {
                        \Carbon\Carbon::setLocale('id');
                        $dateObj = \Carbon\Carbon::parse($sesi->tanggal_pelaksanaan);
                        $dayName = $dateObj->translatedFormat('l');
                        $dateNum = $dateObj->format('d');
                        $monthName = $dateObj->translatedFormat('F');
                        $year = $dateObj->format('Y');
                        
                        $jamMulai = $sesi->jam_mulai ? substr($sesi->jam_mulai, 0, 5) : '';
                        $jamBerakhir = $sesi->jam_berakhir ? substr($sesi->jam_berakhir, 0, 5) : '';
                        $timeString = "{$dayName}, {$dateNum} {$monthName} {$year}, {$jamMulai} - {$jamBerakhir}";
                    }

                    return [
                        'id' => $m->id_materi,
                        'id_sesi' => $m->id_sesi,
                        'image' => $images[array_rand($images)],
                        'title' => $m->judul_materi,
                        'course' => $jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah',
                        'fakultas' => $jadwal?->prodi ?? 'Program Studi',
                        'kelas' => $jadwal?->kelas?->nama_kelas ?? 'Kelas',
                        'session_title' => $sesi->judul_sesi ?? ('Pertemuan ' . $sesi->pertemuan_ke),
                        'topic' => $sesi->materi ?? '-',
                        'method' => $sesi->metode_pertemuan,
                        'link_kelas_daring' => $sesi->link_kelas_daring,
                        'time' => $timeString,
                        'lecturer' => $jadwal?->dosen?->nama_lengkap ?? 'Dosen',
                        'avatar' => $jadwal?->dosen && $jadwal->dosen->foto_profil 
                            ? request()->getSchemeAndHttpHost() . '/storage/' . $jadwal->dosen->foto_profil
                            : 'https://api.dicebear.com/7.x/initials/png?seed=' . urlencode($jadwal?->dosen ? $jadwal->dosen->nama_lengkap : 'Dosen') . '&backgroundColor=116E63&textColor=ffffff',
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

    /**
     * Pencarian global untuk mahasiswa (Mata Kuliah dan Materi).
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', '');
        $user = $request->user();

        if (empty(trim($query))) {
            return response()->json([
                'status' => 'success',
                'data' => [
                    'mata_kuliah' => [],
                    'materi' => []
                ]
            ]);
        }

        // Cari Jadwal Perkuliahan (Mata Kuliah yang tersedia dan yang diambil)
        $peserta = \App\Models\PesertaKelas::where('id_mahasiswa', $user->id_user)->get();
        $idJadwals = $peserta->pluck('id_jadwal')->filter()->toArray();

        $jadwalPerkuliahan = \App\Models\JadwalPerkuliahan::with(['mataKuliah', 'dosen', 'kelas'])
            ->whereHas('mataKuliah', function($q) use ($query) {
                $q->where('nama_mk', 'like', '%' . $query . '%');
            })
            ->limit(10)
            ->get();

        $mataKuliahData = $jadwalPerkuliahan->map(function($j) use ($idJadwals) {
            return [
                'id' => $j->id_jadwal,
                'title' => $j->mataKuliah ? $j->mataKuliah->nama_mk : 'Tanpa Mata Kuliah',
                'lecturer' => $j->dosen ? $j->dosen->nama_lengkap : 'Tanpa Dosen',
                'fakultas' => $j->fakultas ?? '-',
                'prodi' => $j->prodi ?? '-',
                'kelas' => $j->kelas ? $j->kelas->nama_kelas : '-',
                'type' => 'Mata Kuliah',
                'image' => $j->mataKuliah && $j->mataKuliah->banner ? asset('storage/' . $j->mataKuliah->banner) : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80',
                'isDiambil' => in_array($j->id_jadwal, $idJadwals)
            ];
        });

        $materiData = collect();
        if (!empty($idJadwals)) {
            $idSesis = \App\Models\SesiPertemuan::whereIn('id_jadwal', $idJadwals)->pluck('id_sesi')->toArray();
            
            if (!empty($idSesis)) {
                $materi = \App\Models\MateriPembelajaran::whereIn('id_sesi', $idSesis)
                    ->where('judul_materi', 'like', '%' . $query . '%')
                    ->with(['sesiPertemuan.jadwalPerkuliahan.mataKuliah', 'sesiPertemuan.jadwalPerkuliahan.dosen'])
                    ->limit(10)
                    ->get();
                    
                $images = [
                    'https://images.unsplash.com/photo-1517694712202-14dd9538aa97?w=600&q=80',
                    'https://images.unsplash.com/photo-1516321497487-e288fb19713f?w=600&q=80',
                    'https://images.unsplash.com/photo-1498050108023-c5249f4df085?w=600&q=80'
                ];
                
                $materiData = $materi->map(function ($m) use ($images) {
                    $jadwal = $m->sesiPertemuan?->jadwalPerkuliahan;
                    return [
                        'id' => $m->id_materi,
                        'id_sesi' => $m->id_sesi,
                        'title' => $m->judul_materi,
                        'course' => $jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah',
                        'lecturer' => $jadwal?->dosen?->nama_lengkap ?? 'Dosen',
                        'type' => 'Materi',
                        'image' => $images[array_rand($images)],
                    ];
                });
            }
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'mata_kuliah' => $mataKuliahData,
                'materi' => $materiData
            ]
        ]);
    }
}
