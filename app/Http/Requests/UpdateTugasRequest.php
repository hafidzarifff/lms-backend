<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'judul'     => ['sometimes', 'required', 'string', 'max:200'],
            'deskripsi' => ['nullable', 'string'],
            'deadline'  => ['sometimes', 'required', 'date', 'after:now'],
        ];
    }

    public function messages(): array
    {
        return [
            'judul.required'    => 'Judul tugas wajib diisi.',
            'judul.string'      => 'Judul tugas harus berupa teks.',
            'judul.max'         => 'Judul tugas maksimal 200 karakter.',

            'deskripsi.string'  => 'Deskripsi harus berupa teks.',

            'deadline.required' => 'Deadline wajib diisi.',
            'deadline.date'     => 'Format deadline tidak valid.',
            'deadline.after'    => 'Deadline harus di waktu yang akan datang.',
        ];
    }
}
