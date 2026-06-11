<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class KumpulTugasRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file' => ['required', 'file', 'max:10240'], // max 10MB
        ];
    }

    public function messages(): array
    {
        return [
            'file.required' => 'File tugas wajib diunggah.',
            'file.file'     => 'Yang diunggah harus berupa file.',
            'file.max'      => 'Ukuran file maksimal 10 MB.',
        ];
    }
}
