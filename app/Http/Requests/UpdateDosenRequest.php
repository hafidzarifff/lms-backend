<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDosenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
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
            'nomor_induk' => [
                'required', 
                'string', 
                Rule::unique('pengguna', 'nomor_induk')->ignore($this->route('id'), 'id_user')
            ],
            'email' => [
                'required', 
                'email', 
                Rule::unique('pengguna', 'email')->ignore($this->route('id'), 'id_user')
            ],
            'fakultas' => ['required', 'string', 'max:100'],
            'prodi' => ['required', 'string', 'max:100'],
            'status_aktif' => ['required', 'boolean'],
            'status_persetujuan' => [
                'required', 
                'string', 
                Rule::in(['Menunggu', 'Disetujui', 'Ditolak'])
            ],
        ];
    }
}
