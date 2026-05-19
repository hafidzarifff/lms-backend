<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreKelasRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk menyimpan data kelas baru.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Nama kelas wajib diisi, bertipe string, maksimal 50 karakter
            'nama_kelas' => ['required', 'string', 'max:50'],

            // Kode kelas wajib diisi, bertipe string, maksimal 10 karakter, dan harus unik di tabel master_kelas
            'kode_kelas' => ['required', 'string', 'max:10', 'unique:master_kelas,kode_kelas'],

            // Tahun angkatan wajib diisi, bertipe string, minimal 4 karakter (contoh: 2024)
            'tahun_angkatan' => ['required', 'string', 'min:4'],
        ];
    }
}
