<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMataKuliahRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk menyimpan data mata kuliah baru.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Kode mata kuliah wajib diisi, bertipe string, maksimal 10 karakter, dan harus unik di tabel master_mata_kuliah
            'kode_mk' => ['required', 'string', 'max:10', 'unique:master_mata_kuliah,kode_mk'],

            // Nama mata kuliah wajib diisi, bertipe string, maksimal 100 karakter
            'nama_mk' => ['required', 'string', 'max:100'],

            // SKS wajib diisi, bertipe integer, minimal 1 dan maksimal 8
            'sks' => ['required', 'integer', 'min:1', 'max:8'],

            // Deskripsi bersifat opsional (boleh dikosongkan), bertipe string
            'deskripsi' => ['nullable', 'string'],
        ];
    }
}
