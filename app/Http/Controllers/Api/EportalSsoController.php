<?php

namespace App\Http\Controllers\Api;

use App\Enums\RolePengguna;
use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class EportalSsoController extends Controller
{
    /**
     * Verifikasi token SSO dari E-Portal (hasil redirect dari dashboard
     * E-Portal) dan tukar dengan token lokal LMS.
     *
     * Dipanggil oleh frontend web setelah menerima redirect dari E-Portal
     * (?token=...&appModule_id=...&role_id=...).
     */
    public function callback(Request $request): JsonResponse
    {
        $token = $request->query('token');
        $roleId = $request->query('role_id');
        $appModuleId = $request->query('appModule_id', config('sso.module_id'));

        if (!$token || !$roleId || !$appModuleId) {
            return response()->json(['status' => 'error', 'message' => 'Parameter tidak lengkap.'], 400);
        }

        return $this->introspectAndRespond($token, $appModuleId);
    }

    /**
     * Login mobile: tukar token hasil login langsung (email+password) ke
     * E-Portal (POST /auth/login, non-scoped) dengan token lokal LMS.
     *
     * Dipanggil oleh app mobile setelah dapat token dari E-Portal.
     */
    public function mobileLogin(Request $request): JsonResponse
    {
        $token = $request->input('token');

        if (!$token) {
            return response()->json(['status' => 'error', 'message' => 'Token E-Portal tidak ditemukan.'], 400);
        }

        return $this->introspectAndRespond($token, config('sso.module_id'));
    }

    /**
     * Verifikasi token ke E-Portal, cari/buat akun lokal, dan terbitkan
     * token Sanctum. Dipakai bersama oleh alur web (scoped) & mobile
     * (non-scoped) — introspect E-Portal tetap valid untuk keduanya.
     */
    private function introspectAndRespond(string $token, string|int|null $appModuleId): JsonResponse
    {
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

        $ssoUser = $response->json('user');
        $email = $ssoUser['email'] ?? null;

        if (!$email) {
            return response()->json(['status' => 'error', 'message' => 'Data pengguna dari E-Portal tidak lengkap.'], 422);
        }

        $user = Pengguna::where('email', $email)->first();

        // E-Portal sudah memvalidasi identitas user — akun LMS dibuat
        // otomatis saat pertama kali login, mengikuti role institusional
        // dari E-Portal.
        if (!$user) {
            $role = match ($ssoUser['institutional_role'] ?? null) {
                'admin' => RolePengguna::Admin,
                'dosen' => RolePengguna::Dosen,
                'mahasiswa' => RolePengguna::Mahasiswa,
                default => null,
            };

            if (!$role) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Role E-Portal Anda belum didukung di sistem LMS. Hubungi Admin.',
                ], 403);
            }

            $user = Pengguna::create([
                'id_user' => Str::uuid()->toString(),
                'nama_lengkap' => $ssoUser['name'] ?? $email,
                'role' => $role,
                'email' => $email,
                'nomor_induk' => $ssoUser['nidn'] ?? $ssoUser['npm'] ?? $ssoUser['nip'] ?? null,
                'nomor_telepon' => $ssoUser['phone'] ?? null,
                'password' => Hash::make(Str::random(40)),
                'status_aktif' => true,
                'status_persetujuan' => 'Disetujui',
            ]);
        } else {
            // Sinkronkan cache identitas dari E-Portal di setiap login, tapi
            // jangan timpa data lokal dengan nilai kosong yang memang tidak
            // dipunyai E-Portal (mis. supaya koreksi manual di profil LMS
            // tidak ketiban null terus-menerus).
            $user->fill(array_filter([
                'nama_lengkap' => $ssoUser['name'] ?? null,
                'nomor_induk' => $ssoUser['nidn'] ?? $ssoUser['npm'] ?? $ssoUser['nip'] ?? null,
                'nomor_telepon' => $ssoUser['phone'] ?? null,
            ]));

            if ($user->isDirty()) {
                $user->save();
            }
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
