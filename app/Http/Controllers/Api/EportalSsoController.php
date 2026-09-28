<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class EportalSsoController extends Controller
{
    /**
     * Verifikasi token SSO dari E-Portal dan tukar dengan token lokal LMS.
     *
     * Dipanggil oleh frontend setelah menerima redirect dari E-Portal
     * (?token=...&appModule_id=...&role_id=...).
     */
    public function callback(Request $request)
    {
        $token = $request->query('token');
        $roleId = $request->query('role_id');
        $appModuleId = $request->query('appModule_id', config('sso.module_id'));

        if (!$token || !$roleId || !$appModuleId) {
            return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap.'], 400);
        }

        $response = Http::withHeaders([
            'X-SSO-Client-ID' => config('sso.client_id'),
            'X-SSO-Client-Secret' => config('sso.client_secret'),
            'Authorization' => 'Bearer ' . $token,
        ])->post(config('sso.base_url') . '/api/sso/introspect?' . http_build_query([
            'appModule_id' => $appModuleId,
        ]));

        if (!$response->successful() || $response->json('status') !== 200) {
            return response()->json([
                'status' => 'error',
                'message' => $response->json('message', 'Token SSO tidak valid.'),
            ], 401);
        }

        $email = $response->json('user.email');

        if (!$email) {
            return response()->json(['status' => 'error', 'message' => 'Data pengguna dari E-Portal tidak lengkap.'], 422);
        }

        $user = Pengguna::where('email', $email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Akun belum terdaftar di sistem LMS. Hubungi Admin.'], 404);
        }

        if (!$user->status_aktif || $user->status_persetujuan !== 'Disetujui') {
            return response()->json(['status' => 'error', 'message' => 'Akun Anda tidak aktif atau sedang menunggu verifikasi.'], 403);
        }

        $localToken = $user->createToken('eportal-sso-token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Login SSO berhasil.',
            'data' => [
                'token' => $localToken,
                'token_type' => 'Bearer',
                'user' => [
                    'id_user' => $user->id_user,
                    'email' => $user->email,
                    'nama_lengkap' => $user->nama_lengkap,
                    'role' => $user->role->value,
                    'is_first_login' => $user->is_first_login,
                ],
            ],
        ]);
    }
}
