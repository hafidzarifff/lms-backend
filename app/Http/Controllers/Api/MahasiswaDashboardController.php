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
                        ->where('id_peserta', $user->id_user)
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
        $todayStr = now()->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d');
        
        $sesiHariIni = \App\Models\SesiPertemuan::whereIn('id_jadwal', $idJadwals)
            ->whereDate('tanggal_pelaksanaan', $todayStr)
            ->with(['jadwalPerkuliahan.mataKuliah', 'jadwalPerkuliahan.dosen'])
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $jadwalHariIni = $sesiHariIni->map(function ($sesi) {
            $jadwal = $sesi->jadwalPerkuliahan;
            
            $jamMulai = $sesi->jam_mulai ? \Carbon\Carbon::parse($sesi->jam_mulai)->format('H:i') : '';
            $jamBerakhir = $sesi->jam_berakhir ? \Carbon\Carbon::parse($sesi->jam_berakhir)->format('H:i') : '';
            $timeString = $jamMulai && $jamBerakhir ? "{$jamMulai} - {$jamBerakhir}" : '-';

            $avatar = $jadwal?->dosen && $jadwal->dosen->foto_profil 
                ? request()->getSchemeAndHttpHost() . '/storage/' . $jadwal->dosen->foto_profil
                : 'https://ui-avatars.com/api/?name=' . urlencode($jadwal?->dosen ? $jadwal->dosen->nama_lengkap : 'Dosen') . '&background=random';
            
            $image = $jadwal?->mataKuliah && $jadwal->mataKuliah->banner 
                ? request()->getSchemeAndHttpHost() . '/storage/' . $jadwal->mataKuliah->banner 
                : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80';

            return [
                'id' => $sesi->id_sesi,
                'id_jadwal' => $sesi->id_jadwal,
                'title' => $jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah',
                'pertemuan' => $sesi->judul_sesi ?? ('Pertemuan ' . $sesi->pertemuan_ke),
                'date' => \Carbon\Carbon::parse($sesi->tanggal_pelaksanaan)->locale('id')->translatedFormat('d F Y'),
                'time' => $timeString,
                'lecturer' => $jadwal?->dosen?->nama_lengkap ?? 'Tanpa Dosen',
                'role' => 'Dosen Utama',
                'avatar' => $avatar,
                'image' => $image,
                'course' => $jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah',
                'method' => $sesi->metode_pertemuan,
                'topic' => $sesi->materi ?? '-',
                'link_kelas_daring' => $sesi->link_kelas_daring,
                'rawMateri' => $sesi->materi,
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

        $unreadCount = \App\Models\Notifikasi::where('id_user', $user->id_user)
            ->where('is_read', false)
            ->count();

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
                'unread_notif' => $unreadCount,
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
                $q->where('nama_mk', 'ilike', '%' . $query . '%');
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
                    ->where('judul_materi', 'ilike', '%' . $query . '%')
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
    /**
     * Mengambil jadwal kelas dinamis untuk mahasiswa.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function jadwalKelas(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== \App\Enums\RolePengguna::Mahasiswa) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $peserta = \App\Models\PesertaKelas::where('id_mahasiswa', $user->id_user)->get();
        $idJadwals = $peserta->pluck('id_jadwal')->filter()->toArray();

        if (empty($idJadwals)) {
            return response()->json(['status' => 'success', 'data' => []], 200);
        }

        $sesiPertemuan = \App\Models\SesiPertemuan::whereIn('id_jadwal', $idJadwals)
            ->with(['jadwalPerkuliahan.mataKuliah', 'jadwalPerkuliahan.dosen'])
            ->orderBy('tanggal_pelaksanaan', 'asc')
            ->orderBy('jam_mulai', 'asc')
            ->get();

        $jadwalData = $sesiPertemuan->map(function ($sesi) {
            $jadwal = $sesi->jadwalPerkuliahan;
            
            $jamMulai = $sesi->jam_mulai ? \Carbon\Carbon::parse($sesi->jam_mulai)->format('H:i') : '';
            $jamBerakhir = $sesi->jam_berakhir ? \Carbon\Carbon::parse($sesi->jam_berakhir)->format('H:i') : '';
            $timeString = $jamMulai && $jamBerakhir ? "{$jamMulai} - {$jamBerakhir}" : '-';

            $avatar = $jadwal?->dosen && $jadwal->dosen->foto_profil 
                ? request()->getSchemeAndHttpHost() . '/storage/' . $jadwal->dosen->foto_profil
                : 'https://api.dicebear.com/7.x/initials/png?seed=' . urlencode($jadwal?->dosen ? $jadwal->dosen->nama_lengkap : 'Dosen') . '&backgroundColor=116E63&textColor=ffffff';

            return [
                'id' => $sesi->id_sesi,
                'id_jadwal' => $sesi->id_jadwal,
                'date' => $sesi->tanggal_pelaksanaan ? \Carbon\Carbon::parse($sesi->tanggal_pelaksanaan)->setTimezone(config('app.timezone', 'Asia/Jakarta'))->format('Y-m-d') : null,
                'title' => $jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah',
                'pertemuan' => $sesi->judul_sesi ?? ('Pertemuan ' . $sesi->pertemuan_ke),
                'time' => $timeString,
                'dosen' => $jadwal?->dosen?->nama_lengkap ?? 'Dosen',
                'role' => 'Dosen Utama',
                'avatar' => $avatar,
                'course' => $jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah',
                'method' => $sesi->metode_pertemuan,
                'topic' => $sesi->materi ?? '-',
                'link_kelas_daring' => $sesi->link_kelas_daring,
                'rawMateri' => $sesi->materi,
            ];
        })->filter(function($item) {
            return !is_null($item['date']);
        })->values();

        return response()->json([
            'status' => 'success',
            'data' => $jadwalData
        ]);
    }

    /**
     * Mengambil data progress belajar per mata kuliah untuk mahasiswa.
     */
    public function progressBelajar(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== \App\Enums\RolePengguna::Mahasiswa) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Ambil semua jadwal yang diikuti
        $peserta = \App\Models\PesertaKelas::where('id_mahasiswa', $user->id_user)
            ->with(['jadwal.mataKuliah', 'jadwal.kelas', 'jadwal.dosen'])
            ->get();

        $progressData = $peserta->map(function ($p) use ($user) {
            $jadwal = $p->jadwal;
            if (!$jadwal) return null;

            // Hitung sesi
            $sesiIds = \App\Models\SesiPertemuan::where('id_jadwal', $jadwal->id_jadwal)->pluck('id_sesi')->toArray();
            $totalSesi = count($sesiIds);
            
            $hadirCount = 0;
            if ($totalSesi > 0) {
                $hadirCount = \App\Models\Presensi::whereIn('id_sesi', $sesiIds)
                    ->where('id_peserta', $p->id_peserta)
                    ->where('status_kehadiran', 'hadir')
                    ->count();
            }

            // Hitung tugas
            $tugasList = \App\Models\Tugas::whereIn('id_sesi', $sesiIds)->pluck('id_tugas')->toArray();
            $totalTugas = count($tugasList);
            
            $tugasDikerjakan = 0;
            if ($totalTugas > 0) {
                $tugasDikerjakan = \App\Models\NilaiCbt::whereIn('id_tugas', $tugasList)
                    ->where('id_peserta', $user->id_user)
                    ->count();
            }

            $periode = (string) $jadwal->tahun;

            $image = $jadwal->mataKuliah && $jadwal->mataKuliah->banner 
                ? request()->getSchemeAndHttpHost() . '/storage/' . $jadwal->mataKuliah->banner 
                : 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&q=80';

            return [
                'id' => $jadwal->id_jadwal,
                'id_peserta' => $p->id_peserta,
                'title' => $jadwal->mataKuliah ? $jadwal->mataKuliah->nama_mk : 'Mata Kuliah',
                'classInfo' => $jadwal->kelas ? $jadwal->kelas->nama_kelas : 'Kelas',
                'major' => $jadwal->prodi ?? 'Program Studi',
                'image' => $image,
                'dosen' => $jadwal->dosen ? $jadwal->dosen->nama_lengkap : 'Tanpa Dosen',
                'periode' => $periode,
                'absensi_current' => $hadirCount,
                'absensi_total' => $totalSesi,
                'tugas_current' => $tugasDikerjakan,
                'tugas_total' => $totalTugas,
            ];
        })->filter()->values();

        return response()->json([
            'status' => 'success',
            'data' => $progressData
        ]);
    }
    /**
     * Mengambil data nilai per mata kuliah untuk mahasiswa.
     */
    public function nilai(Request $request): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== \App\Enums\RolePengguna::Mahasiswa) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        // Ambil semua jadwal yang diikuti
        $peserta = \App\Models\PesertaKelas::where('id_mahasiswa', $user->id_user)
            ->with(['jadwal.mataKuliah', 'jadwal.dosen'])
            ->get();

        $totalPertanyaan = \App\Models\PertanyaanEvaluasi::aktif()->count();

        $nilaiData = $peserta->map(function ($p) use ($user, $totalPertanyaan) {
            $jadwal = $p->jadwal;
            if (!$jadwal) return null;

            // Hitung sesi
            $sesiIds = \App\Models\SesiPertemuan::where('id_jadwal', $jadwal->id_jadwal)->pluck('id_sesi')->toArray();
            
            // Hitung tugas
            $tugasList = \App\Models\Tugas::whereIn('id_sesi', $sesiIds)->pluck('id_tugas')->toArray();
            
            $rataRata = 0;
            if (count($tugasList) > 0) {
                $nilaiCbt = \App\Models\NilaiCbt::whereIn('id_tugas', $tugasList)
                    ->where('id_peserta', $user->id_user)
                    ->get();
                $totalTugasDinilai = $nilaiCbt->count();
                if ($totalTugasDinilai > 0) {
                    $rataRata = $nilaiCbt->sum('nilai') / $totalTugasDinilai;
                }
            }
            
            // Convert to 4.0 scale and letter grade
            $huruf = 'E';
            $nilai4 = '0.00';
            
            if ($rataRata >= 85) { $huruf = 'A'; $nilai4 = '4.00'; }
            elseif ($rataRata >= 80) { $huruf = 'A-'; $nilai4 = '3.75'; }
            elseif ($rataRata >= 75) { $huruf = 'B+'; $nilai4 = '3.33'; }
            elseif ($rataRata >= 70) { $huruf = 'B'; $nilai4 = '3.00'; }
            elseif ($rataRata >= 65) { $huruf = 'B-'; $nilai4 = '2.75'; }
            elseif ($rataRata >= 60) { $huruf = 'C+'; $nilai4 = '2.33'; }
            elseif ($rataRata >= 55) { $huruf = 'C'; $nilai4 = '2.00'; }
            elseif ($rataRata >= 40) { $huruf = 'D'; $nilai4 = '1.00'; }

            $periode = (string) $jadwal->tahun;

            $totalDijawab = 0;
            if ($totalPertanyaan > 0) {
                $totalDijawab = \App\Models\JawabanEvaluasi::where('id_peserta', $user->id_user)
                    ->where('id_jadwal', $jadwal->id_jadwal)
                    ->join('pertanyaan_evaluasi', 'jawaban_evaluasi.id_pertanyaan', '=', 'pertanyaan_evaluasi.id_pertanyaan')
                    ->where('pertanyaan_evaluasi.is_aktif', true)
                    ->whereNull('jawaban_evaluasi.deleted_at')
                    ->count('jawaban_evaluasi.id_evaluasi');
            }
            $needsEval = ($totalPertanyaan > 0 && $totalDijawab < $totalPertanyaan);

            return [
                'id' => $jadwal->id_jadwal,
                'course' => $jadwal->mataKuliah ? $jadwal->mataKuliah->nama_mk : 'Mata Kuliah',
                'lecturer' => $jadwal->dosen ? $jadwal->dosen->nama_lengkap : 'Tanpa Dosen',
                'sks' => (string) ($jadwal->sks ?? 0),
                'nilai' => $nilai4,
                'huruf' => $huruf,
                'periode' => $periode,
                'rataRata' => number_format((float)$rataRata, 2, '.', ''),
                'needsEval' => $needsEval
            ];
        })->filter()->values();

        return response()->json([
            'status' => 'success',
            'data' => $nilaiData
        ]);
    }
}
