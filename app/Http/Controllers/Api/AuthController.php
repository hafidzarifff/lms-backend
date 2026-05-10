<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterDosenRequest;
use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Enums\RolePengguna;

class AuthController extends Controller
{
    /**
     * Proses login multi-credential
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'identifier' => 'required|string|max:255',
            'password' => 'required|string|min:8',
        ], [
            'identifier.required' => 'Email, Username, atau Nomor Induk wajib diisi.',
            'identifier.string' => 'Format identifier tidak valid.',
            'identifier.max' => 'Identifier maksimal 255 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.string' => 'Format password tidak valid.',
            'password.min' => 'Password minimal 8 karakter.',
        ]);

        // 2. Pencarian User (Multi-Credential Query Fix)
        $user = Pengguna::where(function ($query) use ($request) {
            $query->where('email', $request->identifier)
                  ->orWhere('username', $request->identifier)
                  ->orWhere('nomor_induk', $request->identifier);
        })->first();

        // 3. Verifikasi Kredensial
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial tidak valid',
            ], 401);
        }

        // Simpan referensi user yang ditemukan ke variabel $pengguna
        $pengguna = $user;

        // 3a. Guard: Cek status persetujuan akun (khusus alur registrasi manual)
        if ($pengguna->status_persetujuan === 'Menunggu') {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda sedang dalam proses verifikasi oleh Admin.',
            ], 403);
        }

        if ($pengguna->status_persetujuan === 'Ditolak') {
            return response()->json([
                'success' => false,
                'message' => 'Pendaftaran akun Anda ditolak. Silakan hubungi Admin.',
            ], 403);
        }

        // 3b. Guard: Cek status aktif akun
        if ($pengguna->status_aktif === false) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda saat ini dinonaktifkan. Silakan hubungi Admin.',
            ], 403);
        }

        // 4. Token Abilities
        $abilities = match ($user->role) {
            RolePengguna::Admin => ['admin:*'],
            RolePengguna::Dosen => ['dosen:*'],
            RolePengguna::Mahasiswa => ['mahasiswa:*'],
            default => ['*'],
        };

        // Generate Token Sanctum
        $token = $user->createToken('auth_token', $abilities)->plainTextToken;

        // 5. Response Berhasil
        return response()->json([
            'status' => 'success',
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
                'user' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role->value,
                ]
            ]
        ], 200);
    }

    /**
     * Proses logout
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Logout berhasil'
        ], 200);
    }

    /**
     * Proses registrasi manual khusus Dosen.
     * Akun yang terdaftar akan menunggu verifikasi Admin sebelum bisa login.
     */
    public function registerDosen(RegisterDosenRequest $request)
    {
        // 1. Ambil data yang sudah tervalidasi oleh RegisterDosenRequest
        $validated = $request->validated();

        // 2. Generate UUID untuk primary key
        $uuid = Str::uuid()->toString();

        // 3. Hash password sebelum disimpan ke database
        $hashedPassword = Hash::make($validated['password']);

        // 4. Simpan data dosen baru ke tabel pengguna
        $pengguna = Pengguna::create([
            'id_user'             => $uuid,
            'nama_lengkap'        => $validated['nama_lengkap'],
            'email'               => $validated['email'],
            'password'            => $hashedPassword,
            'nomor_induk'         => $validated['nidn'],
            'fakultas'            => $validated['fakultas'],
            'prodi'               => $validated['prodi'],
            'role'                => 'Dosen',
            'status_persetujuan'  => 'Menunggu',
            'status_aktif'        => false,
        ]);

        // 5. Return response sukses dengan HTTP 201 (Created)
        return response()->json([
            'success' => true,
            'message' => 'Registrasi berhasil. Akun Anda sedang menunggu verifikasi oleh Admin.',
            'data'    => [
                'nama_lengkap' => $pengguna->nama_lengkap,
                'email'        => $pengguna->email,
            ],
        ], 201);
    }
}
