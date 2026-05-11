<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreMahasiswaRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Mengizinkan request ini dijalankan (biasanya diset true untuk API jika auth dihandle oleh middleware)
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nama_lengkap' => ['required', 'string', 'max:300'],
            'nomor_induk'  => ['required', 'string', 'unique:pengguna,nomor_induk'],
            'fakultas'     => ['required', 'string', 'max:100'],
            'prodi'        => ['required', 'string', 'max:100'],
            'angkatan'     => ['required', 'string', 'min:4', 'max:4'],
        ];
    }

    /**
     * Menyesuaikan pesan error agar lebih informatif (opsional tapi disarankan)
     */
    public function messages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi.',
            'nama_lengkap.max'      => 'Nama lengkap maksimal 300 karakter.',
            'nomor_induk.required'  => 'NPM / Nomor Induk wajib diisi.',
            'nomor_induk.unique'    => 'NPM / Nomor Induk sudah terdaftar di sistem.',
            'fakultas.required'     => 'Fakultas wajib diisi.',
            'prodi.required'        => 'Prodi wajib diisi.',
            'angkatan.required'     => 'Angkatan wajib diisi.',
            'angkatan.min'          => 'Angkatan minimal 4 karakter.',
            'angkatan.max'          => 'Angkatan maksimal 4 karakter.',
        ];
    }
}
