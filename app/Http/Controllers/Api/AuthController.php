<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
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
                'status' => 'error',
                'message' => 'Kredensial tidak valid',
            ], 401);
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
}
