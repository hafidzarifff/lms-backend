<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BeriNilaiRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nilai'          => ['required', 'integer', 'min:0', 'max:100'],
            'catatan_dosen'  => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'nilai.required'    => 'Nilai wajib diisi.',
            'nilai.integer'     => 'Nilai harus berupa angka.',
            'nilai.min'         => 'Nilai minimal 0.',
            'nilai.max'         => 'Nilai maksimal 100.',

            'catatan_dosen.string' => 'Catatan harus berupa teks.',
        ];
    }
}
