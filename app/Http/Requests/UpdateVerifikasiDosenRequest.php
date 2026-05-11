<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateVerifikasiDosenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Mengizinkan request ini dijalankan (hak akses akan dicek di middleware API)
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
            'status_persetujuan' => ['required', 'string', 'in:Disetujui,Ditolak'],
        ];
    }

    /**
     * Menyesuaikan pesan error agar lebih informatif.
     */
    public function messages(): array
    {
        return [
            'status_persetujuan.required' => 'Status persetujuan wajib diisi.',
            'status_persetujuan.in'       => 'Status persetujuan hanya boleh berisi Disetujui atau Ditolak.',
        ];
    }
}
