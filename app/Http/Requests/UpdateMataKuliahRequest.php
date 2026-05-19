<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMataKuliahRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui data mata kuliah.
     * Kode mata kuliah tetap harus unik, namun mengecualikan record yang sedang di-update.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Kode mata kuliah wajib diisi, bertipe string, maksimal 10 karakter,
            // unik di tabel master_mata_kuliah KECUALI untuk id_mk yang sedang di-update
            'kode_mk' => [
                'required',
                'string',
                'max:10',
                Rule::unique('master_mata_kuliah', 'kode_mk')->ignore($this->route('id_mk'), 'id_mk'),
            ],

            // Nama mata kuliah wajib diisi, bertipe string, maksimal 100 karakter
            'nama_mk' => ['required', 'string', 'max:100'],

            // SKS wajib diisi, bertipe integer, minimal 1 dan maksimal 8
            'sks' => ['required', 'integer', 'min:1', 'max:8'],

            // Deskripsi bersifat opsional (boleh dikosongkan), bertipe string
            'deskripsi' => ['nullable', 'string'],
        ];
    }
}
