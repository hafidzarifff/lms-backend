<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterDosenRequest extends FormRequest
{
    /**
     * Menentukan apakah pengguna diizinkan untuk membuat request ini.
     */
    public function authorize(): bool
    {
        // Registrasi terbuka untuk publik, tidak perlu autentikasi
        return true;
    }

    /**
     * Aturan validasi untuk registrasi Dosen.
     */
    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'nidn'         => 'required|string|unique:' . \App\Models\Pengguna::class . ',nomor_induk',
            'email'        => 'required|email|unique:' . \App\Models\Pengguna::class . ',email',
            'password'     => 'required|string|min:8|confirmed',
            'fakultas'     => 'required|string|max:255',
            'prodi'        => 'required|string|max:255',
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     */
    public function messages(): array
    {
        return [
            // Nama Lengkap
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.string'   => 'Nama lengkap harus berupa teks.',
            'nama_lengkap.max'      => 'Nama lengkap maksimal 255 karakter.',

            // NIDN
            'nidn.required' => 'NIDN wajib diisi.',
            'nidn.string'   => 'NIDN harus berupa teks.',
            'nidn.unique'   => 'NIDN sudah terdaftar dalam sistem.',

            // Email
            'email.required' => 'Email wajib diisi.',
            'email.email'    => 'Format email tidak valid.',
            'email.unique'   => 'Email sudah terdaftar dalam sistem.',

            // Password
            'password.required'  => 'Password wajib diisi.',
            'password.string'    => 'Password harus berupa teks.',
            'password.min'       => 'Password minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.',

            // Fakultas
            'fakultas.required' => 'Fakultas wajib diisi.',
            'fakultas.string'   => 'Fakultas harus berupa teks.',
            'fakultas.max'      => 'Fakultas maksimal 255 karakter.',

            // Prodi
            'prodi.required' => 'Program studi wajib diisi.',
            'prodi.string'   => 'Program studi harus berupa teks.',
            'prodi.max'      => 'Program studi maksimal 255 karakter.',
        ];
    }
}
