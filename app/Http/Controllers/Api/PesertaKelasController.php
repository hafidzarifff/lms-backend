<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\EnrollKelasRequest;
use App\Enums\RolePengguna;
use App\Models\JadwalPerkuliahan;
use App\Models\PesertaKelas;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PesertaKelasController extends Controller
{
    /**
     * Enrollment mahasiswa ke jadwal perkuliahan menggunakan token.
     *
     * Alur:
     * 1. Validasi input token_enrollment (via EnrollKelasRequest).
     * 2. Cek apakah user yang login memiliki role 'Mahasiswa'.
     * 3. Cari jadwal berdasarkan token yang di-uppercase-kan.
     * 4. Cek apakah mahasiswa sudah terdaftar di jadwal tersebut (duplikat).
     * 5. Insert data peserta_kelas dengan nilai default.
     *
     * @param  EnrollKelasRequest  $request
     * @return JsonResponse
     */
    public function enroll(EnrollKelasRequest $request): JsonResponse
    {
        $user = $request->user();

        // ============================================================
        // Pengecekan Role: Hanya mahasiswa yang boleh melakukan enrollment
        // ============================================================
        if ($user->role !== RolePengguna::Mahasiswa) {
            return response()->json([
                'success' => false,
                'message' => 'Hanya pengguna dengan role Mahasiswa yang dapat melakukan enrollment.',
                'data'    => null,
            ], 403);
        }

        // ============================================================
        // Pencarian jadwal berdasarkan token enrollment
        // Token di-uppercase-kan untuk memastikan konsistensi pencarian
        // ============================================================
        $tokenInput = strtoupper($request->validated()['token_enrollment']);

        $jadwal = JadwalPerkuliahan::where('token_enrollment', $tokenInput)->first();

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Token enrollment tidak valid atau jadwal tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        // ============================================================
        // Pengecekan duplikat: Mahasiswa tidak boleh enroll dua kali
        // di jadwal yang sama
        // ============================================================
        $sudahTerdaftar = PesertaKelas::where('id_jadwal', $jadwal->id_jadwal)
            ->where('id_mahasiswa', $user->id_user)
            ->exists();

        if ($sudahTerdaftar) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah terdaftar di kelas ini.',
                'data'    => null,
            ], 409);
        }

        // ============================================================
        // Insert data peserta kelas baru dengan nilai default
        // UUID di-generate otomatis oleh trait HasUuids pada model
        // ============================================================
        $peserta = PesertaKelas::create([
            'id_jadwal'         => $jadwal->id_jadwal,
            'id_mahasiswa'      => $user->id_user,
            'tanggal_daftar'    => now(),
            'evaluasi_selesai'  => false,
            'kehadiran'         => '0/0',
            'nilai_akhir'       => 0.00,
            'status_kelayakan'  => 'Belum Ditentukan',
        ]);

        // Muat relasi agar response lengkap dengan data jadwal & mahasiswa
        $peserta->load(['jadwal', 'mahasiswa']);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil mendaftar ke kelas.',
            'data'    => $peserta,
        ], 201);
    }

    /**
     * Mengambil daftar seluruh peserta yang terdaftar di jadwal tertentu.
     *
     * Menggunakan Eager Loading untuk memuat relasi mahasiswa
     * dan hanya mengambil kolom yang diperlukan dari tabel pengguna
     * agar query efisien dan mencegah masalah N+1.
     *
     * @param  string  $id_jadwal
     * @return JsonResponse
     */
    public function pesertaByJadwal(string $id_jadwal): JsonResponse
    {
        // ============================================================
        // Validasi: Pastikan jadwal yang diminta benar-benar ada
        // ============================================================
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal perkuliahan tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        // ============================================================
        // Eager Loading relasi mahasiswa dengan select kolom spesifik
        // Hanya mengambil id_user, nama_lengkap, nomor_induk, email
        // untuk efisiensi query dan keamanan data
        // ============================================================
        $peserta = PesertaKelas::where('id_jadwal', $id_jadwal)
            ->with(['mahasiswa:id_user,nama_lengkap,nomor_induk,email'])
            ->orderBy('tanggal_daftar', 'asc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar peserta kelas berhasil diambil.',
            'data'    => $peserta,
        ], 200);
    }

    /**
     * Mengambil data progres peserta untuk halaman Monitoring Progres Dosen.
     * Mengembalikan data lengkap dengan kalkulasi persentase dan log aktivitas.
     *
     * @param  string  $id_jadwal
     * @return JsonResponse
     */
    public function monitoringProgres(string $id_jadwal): JsonResponse
    {
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        if (!$jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Data jadwal perkuliahan tidak ditemukan.',
                'data'    => null,
            ], 404);
        }

        $peserta = PesertaKelas::where('id_jadwal', $id_jadwal)
            ->with(['mahasiswa:id_user,nama_lengkap,nomor_induk'])
            ->orderBy('tanggal_daftar', 'asc')
            ->get();
            
        $sesiIds = \App\Models\SesiPertemuan::where('id_jadwal', $id_jadwal)->pluck('id_sesi');
        
        $presensiTerakhir = \App\Models\Presensi::whereIn('id_sesi', $sesiIds)
            ->orderBy('created_at', 'desc')
            ->get()
            ->groupBy('id_peserta');

        $result = $peserta->map(function ($p) use ($presensiTerakhir) {
            $progresPercent = 0;
            if ($p->kehadiran) {
                $parts = explode('/', $p->kehadiran);
                if (count($parts) === 2 && (int)$parts[1] > 0) {
                    $progresPercent = round(((int)$parts[0] / (int)$parts[1]) * 100);
                }
            }

            $log = "Belum ada log";
            $latestPresensi = $presensiTerakhir->get($p->id_peserta)?->first();
            
            if ($latestPresensi) {
                if ($latestPresensi->status_kehadiran === 'Hadir') {
                    $log = "Hadir Sesi Terakhir";
                } elseif ($latestPresensi->status_kehadiran === 'Tidak Hadir' || $latestPresensi->status_kehadiran === 'Alpa') {
                    $log = "Bolos Sesi Terakhir";
                } else {
                    $log = "Sesi Terakhir: " . $latestPresensi->status_kehadiran;
                }
            }

            return [
                'id' => $p->id_peserta,
                'nim' => $p->mahasiswa?->nomor_induk ?? '-',
                'nama' => $p->mahasiswa?->nama_lengkap ?? 'Tanpa Nama',
                'log' => $log,
                'rataRata' => number_format((float)$p->nilai_akhir, 2, '.', ''),
                'progres' => $progresPercent,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Data monitoring progres berhasil diambil.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Mengambil daftar seluruh peserta untuk verifikasi sertifikat dosen.
     * Hanya mengambil kelas yang diampu oleh dosen yang login.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function listVerifikasiSertifikat(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cari jadwal yang diajar oleh dosen ini
        $jadwalIds = \App\Models\JadwalPerkuliahan::where('id_dosen', $user->id_user)
            ->pluck('id_jadwal');

        $peserta = PesertaKelas::whereIn('id_jadwal', $jadwalIds)
            ->with([
                'mahasiswa:id_user,nama_lengkap,nomor_induk,fakultas,prodi',
                'jadwal:id_jadwal,id_mk,id_kelas',
                'jadwal.mataKuliah:id_mk,nama_mk',
                'jadwal.kelas:id_kelas,nama_kelas'
            ])
            ->orderBy('tanggal_daftar', 'asc')
            ->get();

        $result = $peserta->map(function ($p) {
            return [
                'id' => $p->id_peserta,
                'nim' => $p->mahasiswa?->nomor_induk ?? '-',
                'nama' => $p->mahasiswa?->nama_lengkap ?? 'Tanpa Nama',
                'fakultas' => $p->mahasiswa?->fakultas ?? '-',
                'prodi' => $p->mahasiswa?->prodi ?? '-',
                'mataKuliah' => ($p->jadwal?->mataKuliah?->nama_mk ?? 'Mata Kuliah') . ' - ' . ($p->jadwal?->kelas?->nama_kelas ?? 'Kelas'),
                'kehadiran' => $p->kehadiran ?? '0/0',
                'nilaiAkhir' => number_format((float)$p->nilai_akhir, 2, '.', ''),
                'status' => strtoupper($p->status_kelayakan ?? 'Belum Ditentukan'),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Daftar verifikasi sertifikat berhasil diambil.',
            'data'    => $result,
        ], 200);
    }

    /**
     * Memperbarui status kelayakan sertifikat.
     *
     * @param Request $request
     * @param string $id_peserta
     * @return JsonResponse
     */
    public function updateStatusKelayakan(Request $request, string $id_peserta): JsonResponse
    {
        $validated = $request->validate([
            'status_kelayakan' => 'required|string|in:Disetujui,Ditolak,Belum Ditentukan'
        ]);

        $peserta = PesertaKelas::find($id_peserta);
        if (!$peserta) {
            return response()->json([
                'success' => false,
                'message' => 'Data peserta kelas tidak ditemukan.'
            ], 404);
        }

        $peserta->status_kelayakan = $validated['status_kelayakan'];
        $peserta->save();

        // Jika disetujui, generate sertifikat jika belum ada
        if ($peserta->status_kelayakan === 'Disetujui') {
            $sertifikatAda = \App\Models\Sertifikat::where('id_peserta', $peserta->id_peserta)->exists();
            if (!$sertifikatAda) {
                // Ambil template aktif
                $templateAktif = \App\Models\TemplateSertifikat::where('is_aktif', true)->first();
                if ($templateAktif) {
                    \App\Models\Sertifikat::create([
                        'id_peserta' => $peserta->id_peserta,
                        'id_template' => $templateAktif->id_template,
                        'nomor_sertifikat' => \App\Models\Sertifikat::generateNomorSertifikat(),
                        'tanggal_terbit' => now(),
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Status kelayakan berhasil diperbarui.',
            'data'    => [
                'id_peserta' => $peserta->id_peserta,
                'status_kelayakan' => strtoupper($peserta->status_kelayakan)
            ]
        ], 200);
    }
}
