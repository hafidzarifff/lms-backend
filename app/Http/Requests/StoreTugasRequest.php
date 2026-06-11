<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul_tugas'     => ['required', 'string', 'max:200'],
            'deskripsi_tugas' => ['nullable', 'string'],
            'batas_waktu'     => ['required', 'date', 'after:now'],
            'link_cbt'        => ['nullable', 'url'],
            'token_cbt'       => ['nullable', 'string', 'max:10'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul_tugas.required'     => 'Judul tugas wajib diisi.',
            'judul_tugas.string'       => 'Judul tugas harus berupa teks.',
            'judul_tugas.max'          => 'Judul tugas maksimal 200 karakter.',

            'deskripsi_tugas.string'   => 'Deskripsi harus berupa teks.',

            'batas_waktu.required'     => 'Batas waktu wajib diisi.',
            'batas_waktu.date'         => 'Format batas waktu tidak valid.',
            'batas_waktu.after'        => 'Batas waktu harus di waktu yang akan datang.',

            'link_cbt.url'             => 'Link CBT harus berupa URL yang valid.',

            'token_cbt.string'         => 'Token CBT harus berupa teks.',
            'token_cbt.max'            => 'Token CBT maksimal 10 karakter.',
        ];
    }
}
