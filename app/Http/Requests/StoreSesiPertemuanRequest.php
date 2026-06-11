<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSesiPertemuanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_jadwal' => ['required', 'uuid', 'exists:jadwal_perkuliahan,id_jadwal'],
            'pertemuan_ke' => ['required', 'integer', 'min:1'],
            'judul_sesi' => ['required', 'string', 'max:100'],
            'tanggal_pelaksanaan' => ['required', 'date', 'after_or_equal:today'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_berakhir' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode_pertemuan' => ['required', 'in:synchronous,asynchronous'],
            'link_kelas_daring' => ['required_if:metode_pertemuan,synchronous', 'nullable', 'url'],
        ];
    }
}
