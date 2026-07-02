<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RegisterMahasiswaRequest extends FormRequest
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
     * Aturan validasi untuk registrasi Mahasiswa.
     */
    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'npm'          => 'required|string|unique:' . \App\Models\Pengguna::class . ',nomor_induk',
            'email'        => 'required|email|unique:' . \App\Models\Pengguna::class . ',email',
            'password'     => 'required|string|min:8|confirmed',
            'fakultas'     => 'required|string|max:255',
            'prodi'        => 'required|string|max:255',
            'angkatan'     => 'required|string|min:4|max:4',
        ];
    }

    /**
     * Pesan error kustom.
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'npm.required'          => 'NPM wajib diisi.',
            'npm.unique'            => 'NPM sudah terdaftar.',
            'email.required'        => 'Email wajib diisi.',
            'email.unique'          => 'Email sudah terdaftar.',
            'password.required'     => 'Password wajib diisi.',
            'password.min'          => 'Password minimal 8 karakter.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
            'fakultas.required'     => 'Fakultas wajib diisi.',
            'prodi.required'        => 'Program studi wajib diisi.',
            'angkatan.required'     => 'Tahun angkatan wajib diisi.',
            'angkatan.min'          => 'Tahun angkatan harus 4 digit.',
            'angkatan.max'          => 'Tahun angkatan harus 4 digit.',
        ];
    }
}
