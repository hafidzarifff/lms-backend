<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateKelasRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui data kelas.
     * Kode kelas tetap harus unik, namun mengecualikan record yang sedang di-update.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Nama kelas wajib diisi, bertipe string, maksimal 50 karakter
            'nama_kelas' => ['required', 'string', 'max:50'],

            // Kode kelas wajib diisi, bertipe string, maksimal 10 karakter,
            // unik di tabel master_kelas KECUALI untuk id_kelas yang sedang di-update
            'kode_kelas' => [
                'required',
                'string',
                'max:10',
                Rule::unique('master_kelas', 'kode_kelas')->ignore($this->route('id_kelas'), 'id_kelas'),
            ],

            // Tahun angkatan wajib diisi, bertipe string, minimal 4 karakter
            'tahun_angkatan' => ['required', 'string', 'min:4'],

            // Nama fakultas wajib diisi, bertipe string, maksimal 255 karakter
            'fakultas' => ['required', 'string', 'max:255'],

            // Nama program studi wajib diisi, bertipe string, maksimal 255 karakter
            'prodi' => ['required', 'string', 'max:255'],
        ];
    }
}
