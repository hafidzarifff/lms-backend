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

            // Semester perkuliahan, opsional, harus berupa angka integer antara 1 sampai 14
            'semester' => ['nullable', 'integer', 'min:1', 'max:14'],

            // Nama fakultas penyelenggara, opsional, bertipe string, maksimal 100 karakter
            'fakultas' => ['nullable', 'string', 'max:100'],

            // Nama program studi penyelenggara, opsional, bertipe string, maksimal 100 karakter
            'prodi' => ['nullable', 'string', 'max:100'],
        ];
    }
}
