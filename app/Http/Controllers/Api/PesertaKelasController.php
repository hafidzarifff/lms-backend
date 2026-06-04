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
}
