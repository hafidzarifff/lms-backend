<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSesiPertemuanRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui data sesi pertemuan.
     * Perbedaan dengan StoreSesiPertemuanRequest:
     * - tanggal_pelaksanaan boleh tanggal lampau (tidak ada after_or_equal:today)
     * - id_jadwal tidak boleh diubah
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'id_jadwal' => ['prohibited'],
            'pertemuan_ke' => ['required', 'integer', 'min:1'],
            'judul_sesi' => ['required', 'string', 'max:100'],
            'tanggal_pelaksanaan' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_berakhir' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'metode_pertemuan' => ['required', 'in:synchronous,asynchronous'],
            'status' => ['required', 'in:TERJADWAL,BERJALAN,SELESAI'],
            'materi' => ['nullable', 'string'],
            'url_cbt' => ['nullable', 'url'],
            'link_kelas_daring' => ['required_if:metode_pertemuan,synchronous', 'nullable', 'url'],
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
            'id_jadwal.prohibited' => 'ID Jadwal tidak boleh diubah.',

            'pertemuan_ke.required' => 'Pertemuan ke- wajib diisi.',
            'pertemuan_ke.integer'  => 'Pertemuan ke- harus berupa angka.',
            'pertemuan_ke.min'      => 'Pertemuan ke- minimal bernilai 1.',

            'judul_sesi.required' => 'Judul sesi wajib diisi.',
            'judul_sesi.string'   => 'Judul sesi harus berupa teks.',
            'judul_sesi.max'      => 'Judul sesi maksimal 100 karakter.',

            'tanggal_pelaksanaan.required' => 'Tanggal pelaksanaan wajib diisi.',
            'tanggal_pelaksanaan.date'     => 'Format tanggal pelaksanaan tidak valid.',

            'jam_mulai.required'     => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format'  => 'Format jam mulai tidak valid. Gunakan format HH:mm.',

            'jam_berakhir.required'    => 'Jam berakhir wajib diisi.',
            'jam_berakhir.date_format' => 'Format jam berakhir tidak valid. Gunakan format HH:mm.',
            'jam_berakhir.after'       => 'Jam berakhir harus setelah jam mulai.',

            'metode_pertemuan.required' => 'Metode pertemuan wajib dipilih.',
            'metode_pertemuan.in'       => 'Metode pertemuan harus synchronous atau asynchronous.',

            'link_kelas_daring.required_if' => 'Link kelas daring wajib diisi untuk metode synchronous.',
            'link_kelas_daring.url'         => 'Format link kelas daring tidak valid.',
        ];
    }
}
