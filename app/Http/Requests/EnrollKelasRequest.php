<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class EnrollKelasRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     * Otorisasi role dicek di controller, bukan di sini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk proses enrollment mahasiswa.
     * Token enrollment wajib diisi, bertipe string, dan tepat 6 karakter.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token_enrollment' => ['required', 'string', 'size:6'],
            'id_jadwal'        => ['required', 'string', 'exists:jadwal_perkuliahan,id_jadwal'],
        ];
    }

    /**
     * Pesan error kustom dalam Bahasa Indonesia.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token_enrollment.required' => 'Token enrollment wajib diisi.',
            'token_enrollment.string'   => 'Token enrollment harus berupa teks.',
            'token_enrollment.size'     => 'Token enrollment harus tepat 6 karakter.',
            'id_jadwal.required'        => 'ID Jadwal wajib diisi.',
            'id_jadwal.exists'          => 'Jadwal tidak ditemukan.',
        ];
    }

    /**
     * Override response validasi gagal agar mengikuti format JSON Envelope
     * yang konsisten: { success, message, data }.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validasi gagal.',
            'data'    => $validator->errors(),
        ], 422));
    }
}
