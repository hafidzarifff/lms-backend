<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMahasiswaRequest;
use App\Http\Requests\UpdateMahasiswaRequest;
use App\Models\Pengguna;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\JsonResponse;

class MahasiswaController extends Controller
{
    /**
     * Menyimpan data mahasiswa baru yang diinput oleh Admin.
     * 
     * @param  StoreMahasiswaRequest  $request
     * @return JsonResponse
     */
    public function store(StoreMahasiswaRequest $request): JsonResponse
    {
        // 1. Terima request yang sudah tervalidasi otomatis oleh StoreMahasiswaRequest
        $validatedData = $request->validated();

        // 2. Generate variabel email otomatis dengan format: {nomor_induk}@mhs.kampus.ac.id
        $email = $validatedData['nomor_induk'] . '@mhs.uika.ac.id';

        // 3. Generate variabel password otomatis dengan format: Mhs{nomor_induk}
        $passwordPlain = 'Mhs' . $validatedData['nomor_induk'];

        // 4. Lakukan enkripsi Hash pada password tersebut
        $passwordHashed = Hash::make($passwordPlain);

        // 5. Insert data ke tabel pengguna (id_user UUID digenerate otomatis oleh trait HasUuids di model)
        Pengguna::create([
            'role'               => 'Mahasiswa',
            'nama_lengkap'       => $validatedData['nama_lengkap'],
            'nomor_induk'        => $validatedData['nomor_induk'],
            'email'              => $email,
            'password'           => $passwordHashed,
            'fakultas'           => $validatedData['fakultas'],
            'prodi'              => $validatedData['prodi'],
            'angkatan'           => $validatedData['angkatan'],
            'status_aktif'       => true,
            'status_persetujuan' => 'Disetujui',
        ]);

        // 6. Return response JSON (HTTP 201) berisi pesan sukses
        return response()->json([
            'message' => 'Data mahasiswa berhasil ditambahkan. Email dan sandi default telah dibuat.'
        ], 201);
    }

    /**
     * Mengambil daftar data mahasiswa (Read All).
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Ambil data dengan role 'Mahasiswa', urutkan berdasarkan terbaru, pagination 50 per halaman
        $mahasiswa = Pengguna::where('role', 'Mahasiswa')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($mahasiswa, 200);
    }

    /**
     * Mengambil detail satu data mahasiswa (Read One).
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        // Cari data berdasarkan ID dan pastikan rolenya 'Mahasiswa'
        $mahasiswa = Pengguna::where('id_user', $id)
            ->where('role', 'Mahasiswa')
            ->first();

        // Jika tidak ditemukan, kembalikan response 404
        if (!$mahasiswa) {
            return response()->json([
                'message' => 'Data mahasiswa tidak ditemukan.'
            ], 404);
        }

        // Jika ditemukan, kembalikan detail data mahasiswa
        return response()->json($mahasiswa, 200);
    }

    /**
     * Memperbarui data mahasiswa (Update).
     *
     * @param  UpdateMahasiswaRequest  $request
     * @param  string  $id
     * @return JsonResponse
     */
    public function update(UpdateMahasiswaRequest $request, $id): JsonResponse
    {
        // Cari data mahasiswa berdasarkan ID dan role 'Mahasiswa'
        $mahasiswa = Pengguna::where('id_user', $id)
            ->where('role', 'Mahasiswa')
            ->first();

        if (!$mahasiswa) {
            return response()->json([
                'message' => 'Data mahasiswa tidak ditemukan.'
            ], 404);
        }

        $validatedData = $request->validated();

        // Logika Penting: Jika nomor_induk pada request BERBEDA dengan yang di database, generate ulang email
        // Email mengikuti format yang sudah diatur {nomor_induk_baru}@mhs.uika.ac.id
        if ($mahasiswa->nomor_induk !== $validatedData['nomor_induk']) {
            $mahasiswa->email = $validatedData['nomor_induk'] . '@mhs.uika.ac.id';
        }

        // Update data ke database (JANGAN ubah password)
        $mahasiswa->nama_lengkap = $validatedData['nama_lengkap'];
        $mahasiswa->nomor_induk  = $validatedData['nomor_induk'];
        $mahasiswa->fakultas     = $validatedData['fakultas'];
        $mahasiswa->prodi        = $validatedData['prodi'];
        $mahasiswa->angkatan     = $validatedData['angkatan'];
        $mahasiswa->status_aktif = $validatedData['status_aktif'];

        $mahasiswa->save();

        return response()->json([
            'message' => 'Data mahasiswa berhasil diperbarui.'
        ], 200);
    }

    /**
     * Menghapus data mahasiswa (Delete).
     *
     * @param  string  $id
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        // Cari data mahasiswa berdasarkan ID dan role 'Mahasiswa'
        $mahasiswa = Pengguna::where('id_user', $id)
            ->where('role', 'Mahasiswa')
            ->first();

        if (!$mahasiswa) {
            return response()->json([
                'message' => 'Data mahasiswa tidak ditemukan.'
            ], 404);
        }

        // Lakukan proses delete data dari database
        $mahasiswa->delete();

        return response()->json([
            'message' => 'Data mahasiswa berhasil dihapus.'
        ], 200);
    }
}
