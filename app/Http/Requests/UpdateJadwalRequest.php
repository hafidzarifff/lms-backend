<?php

namespace App\Http\Requests;

use App\Enums\RolePengguna;
use App\Models\Pengguna;
use Illuminate\Foundation\Http\FormRequest;

class UpdateJadwalRequest extends FormRequest
{
    /**
     * Menentukan apakah user memiliki otorisasi untuk request ini.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Aturan validasi untuk memperbarui data jadwal perkuliahan.
     * Rules sama dengan StoreJadwalRequest.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // ID Mata Kuliah wajib diisi, format UUID, dan harus ada di tabel master_mata_kuliah
            'id_mk' => ['required', 'uuid', 'exists:master_mata_kuliah,id_mk'],

            // ID Kelas wajib diisi, format UUID, dan harus ada di tabel master_kelas
            'id_kelas' => ['required', 'uuid', 'exists:master_kelas,id_kelas'],

            // ID Dosen wajib diisi, format UUID, harus ada di tabel pengguna,
            // dan dipastikan role-nya adalah 'Dosen' melalui validasi kustom
            'id_dosen' => [
                'required',
                'uuid',
                'exists:pengguna,id_user',
                function (string $attribute, mixed $value, \Closure $fail) {
                    // Cek apakah pengguna dengan id_user ini memiliki role Dosen
                    $pengguna = Pengguna::find($value);
                    if ($pengguna && $pengguna->role !== RolePengguna::Dosen) {
                        $fail('Pengguna yang dipilih bukan berstatus Dosen.');
                    }
                },
            ],

            // Semester wajib diisi, bertipe integer, nilai antara 1 sampai 14
            'semester' => ['required', 'integer', 'min:1', 'max:14'],

            // Nama fakultas wajib diisi, bertipe string, maksimal 255 karakter
            'fakultas' => ['required', 'string', 'max:255'],

            // Nama program studi wajib diisi, bertipe string, maksimal 255 karakter
            'prodi' => ['required', 'string', 'max:255'],

            // Tahun ajaran wajib diisi, bertipe string, maksimal 9 karakter (contoh: 2025/2026)
            'tahun' => ['required', 'string', 'max:9'],

            // Hari wajib diisi, hanya boleh dari daftar hari yang valid
            'hari' => ['required', 'string', 'in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu'],

            // Waktu mulai wajib diisi, format HH:mm (24 jam)
            'waktu_mulai' => ['required', 'date_format:H:i'],

            // Waktu berakhir wajib diisi, format HH:mm, dan harus setelah waktu_mulai
            'waktu_berakhir' => ['required', 'date_format:H:i', 'after:waktu_mulai'],

            // Tanggal mulai bisa diupdate, bertipe date
            'tanggal_mulai' => ['nullable', 'date'],
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
            'id_mk.required'       => 'Mata kuliah wajib dipilih.',
            'id_mk.uuid'           => 'Format ID mata kuliah tidak valid.',
            'id_mk.exists'         => 'Mata kuliah yang dipilih tidak ditemukan.',

            'id_kelas.required'    => 'Kelas wajib dipilih.',
            'id_kelas.uuid'        => 'Format ID kelas tidak valid.',
            'id_kelas.exists'      => 'Kelas yang dipilih tidak ditemukan.',

            'id_dosen.required'    => 'Dosen wajib dipilih.',
            'id_dosen.uuid'        => 'Format ID dosen tidak valid.',
            'id_dosen.exists'      => 'Dosen yang dipilih tidak ditemukan.',

            'semester.required'    => 'Semester wajib diisi.',
            'semester.integer'     => 'Semester harus berupa angka.',
            'semester.min'         => 'Semester minimal bernilai 1.',
            'semester.max'         => 'Semester maksimal bernilai 14.',

            'fakultas.required'    => 'Fakultas wajib diisi.',
            'prodi.required'       => 'Program studi wajib diisi.',
            'tahun.required'       => 'Tahun ajaran wajib diisi.',
            'tahun.max'            => 'Tahun ajaran maksimal 9 karakter.',

            'hari.required'        => 'Hari wajib dipilih.',
            'hari.in'              => 'Hari yang dipilih tidak valid.',

            'waktu_mulai.required'     => 'Waktu mulai wajib diisi.',
            'waktu_mulai.date_format'  => 'Format waktu mulai tidak valid. Gunakan format HH:mm.',

            'waktu_berakhir.required'    => 'Waktu berakhir wajib diisi.',
            'waktu_berakhir.date_format' => 'Format waktu berakhir tidak valid. Gunakan format HH:mm.',
            'waktu_berakhir.after'       => 'Waktu berakhir harus setelah waktu mulai.',

            'tanggal_mulai.date'         => 'Format tanggal mulai tidak valid.',
        ];
    }
}
