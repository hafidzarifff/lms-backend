<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreJadwalRequest;
use App\Http\Requests\UpdateJadwalRequest;
use App\Models\JadwalPerkuliahan;
use App\Models\MasterMataKuliah;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class JadwalPerkuliahanController extends Controller
{
    /**
     * Mengambil daftar seluruh jadwal perkuliahan (Read All).
     * Menggunakan Eager Loading untuk memuat relasi mataKuliah, kelas, dan dosen
     * agar data nama_mk, nama_kelas, dan nama_lengkap dosen ikut terbawa dalam response JSON.
     * Data diurutkan dari yang terbaru dan menggunakan pagination 10 data per halaman.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $request = request();

        $perPage = min((int) $request->query('per_page', 20), 100);

        $jadwal = JadwalPerkuliahan::with([
                'mataKuliah:id_mk,kode_mk,nama_mk,sks',
                'kelas:id_kelas,kode_kelas,nama_kelas',
                'dosen:id_user,nama_lengkap,nomor_induk',
            ])
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('fakultas', 'ilike', "%{$search}%")
                        ->orWhere('prodi', 'ilike', "%{$search}%")
                        ->orWhereHas('mataKuliah', function ($mk) use ($search) {
                            $mk->where('nama_mk', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('kelas', function ($k) use ($search) {
                            $k->where('nama_kelas', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('dosen', function ($d) use ($search) {
                            $d->where('nama_lengkap', 'ilike', "%{$search}%")
                              ->orWhere('nomor_induk', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($request->query('semester'), function ($q, $semester) {
                $q->where('semester', $semester);
            })
            ->when($request->query('tahun'), function ($q, $tahun) {
                $q->where('tahun', $tahun);
            })
            ->when($request->query('hari'), function ($q, $hari) {
                $q->where('hari', $hari);
            })
            ->when($request->query('fakultas'), function ($q, $fakultas) {
                $q->where('fakultas', 'ilike', "%{$fakultas}%");
            })
            ->when($request->query('prodi'), function ($q, $prodi) {
                $q->where('prodi', 'ilike', "%{$prodi}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($jadwal, 200);
    }

    /**
     * Mengambil daftar jadwal yang sudah dikelompokkan per dosen + mata kuliah.
     * Setiap group merepresentasikan satu mata kuliah yang diajarkan oleh satu dosen,
     * lengkap dengan daftar kelas yang tersedia dan jumlah kelasnya.
     *
     * Digunakan di halaman Kelola Materi Perkuliahan & Forum Diskusi.
     *
     * @return JsonResponse
     */
    public function grouped(): JsonResponse
    {
        $request = request();
        $perPage = min((int) $request->query('per_page', 20), 100);

        // Ambil semua jadwal dengan filter yang relevan
        $query = JadwalPerkuliahan::with([
                'mataKuliah:id_mk,kode_mk,nama_mk,sks,deskripsi',
                'kelas:id_kelas,kode_kelas,nama_kelas',
                'dosen:id_user,nama_lengkap,nomor_induk',
            ])
            ->withCount('pesertaKelas')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('fakultas', 'ilike', "%{$search}%")
                        ->orWhere('prodi', 'ilike', "%{$search}%")
                        ->orWhereHas('mataKuliah', function ($mk) use ($search) {
                            $mk->where('nama_mk', 'ilike', "%{$search}%")
                               ->orWhere('kode_mk', 'ilike', "%{$search}%");
                        })
                        ->orWhereHas('dosen', function ($d) use ($search) {
                            $d->where('nama_lengkap', 'ilike', "%{$search}%")
                              ->orWhere('nomor_induk', 'ilike', "%{$search}%");
                        });
                });
            })
            ->when($request->query('semester'), fn($q, $v) => $q->where('semester', $v))
            ->when($request->query('fakultas'), fn($q, $v) => $q->where('fakultas', $v))
            ->when($request->query('prodi'),    fn($q, $v) => $q->where('prodi', $v))
            ->when($request->query('tahun'),    fn($q, $v) => $q->where('tahun', $v))
            ->when($request->query('nidn'), function ($q, $nidn) {
                $q->whereHas('dosen', function ($d) use ($nidn) {
                    $d->where('nomor_induk', $nidn);
                });
            })
            ->orderBy('created_at', 'desc');

        // Ambil semua data, lalu group di PHP layer
        $all = $query->get();

        // Kelompokkan berdasarkan id_dosen + id_mk
        $grouped = $all->groupBy(function ($j) {
            return ($j->id_dosen ?? 'no_dosen') . '_' . ($j->id_mk ?? 'no_mk');
        })->map(function ($items) {
            /** @var JadwalPerkuliahan $first */
            $first = $items->first();

            return [
                // Representasi group — id_jadwal dari jadwal pertama sebagai identifier
                'id_jadwal'       => $first->id_jadwal,
                'id_mk'           => $first->id_mk,
                'id_dosen'        => $first->id_dosen,
                'kode_mk'         => $first->mataKuliah?->kode_mk,
                'nama_mk'         => $first->mataKuliah?->nama_mk,
                'sks'             => $first->mataKuliah?->sks ?? $first->sks,
                'deskripsi'       => $first->mataKuliah?->deskripsi,
                'nama_dosen'      => $first->dosen?->nama_lengkap,
                'nidn'            => $first->dosen?->nomor_induk,
                'fakultas'        => $first->fakultas,
                'prodi'           => $first->prodi,
                'semester'        => $first->semester,
                'tahun'           => $first->tahun,
                // Jumlah kelas unik untuk kombinasi dosen + MK ini
                'jumlah_kelas'    => $items->count(),
                // Detail tiap kelas (untuk ditampilkan saat kartu diklik)
                'kelas_list'      => $items->map(fn($j) => [
                    'id_jadwal'        => $j->id_jadwal,
                    'id_kelas'         => $j->id_kelas,
                    'nama_kelas'       => $j->kelas?->nama_kelas,
                    'kode_kelas'       => $j->kelas?->kode_kelas,
                    'hari'             => $j->hari,
                    'waktu_mulai'      => $j->waktu_mulai,
                    'waktu_berakhir'   => $j->waktu_berakhir,
                    'token_enrollment' => $j->token_enrollment,
                    'akses_bebas'      => $j->akses_bebas,
                    'total_mahasiswa'  => $j->peserta_kelas_count,
                ])->values(),
            ];
        })->values();

        // Manual pagination setelah grouping
        $page     = max(1, (int) $request->query('page', 1));
        $total    = $grouped->count();
        $items    = $grouped->slice(($page - 1) * $perPage, $perPage)->values();
        $lastPage = max(1, (int) ceil($total / $perPage));

        return response()->json([
            'data'         => $items,
            'current_page' => $page,
            'last_page'    => $lastPage,
            'per_page'     => $perPage,
            'total'        => $total,
        ], 200);
    }

    /**
     * Mengambil detail satu jadwal perkuliahan berdasarkan ID (Read One).
     * Sertakan Eager Loading agar data relasi ikut tampil.
     *
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function show(string $id_jadwal): JsonResponse
    {
        // Eager Loading relasi saat mengambil detail satu jadwal
        $jadwal = JadwalPerkuliahan::with(['mataKuliah', 'kelas', 'dosen'])
            ->find($id_jadwal);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        return response()->json($jadwal, 200);
    }

    /**
     * Menyimpan data jadwal perkuliahan baru (Create).
     * - SKS diambil otomatis dari tabel master_mata_kuliah berdasarkan id_mk yang dipilih.
     * - Token enrollment di-generate otomatis sebagai string acak kapital 6 karakter unik.
     *
     * @param StoreJadwalRequest $request
     * @return JsonResponse
     */
    public function store(StoreJadwalRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        // ============================================================
        // Penarikan SKS otomatis dari tabel master_mata_kuliah
        // Admin tidak perlu input SKS manual, cukup pilih mata kuliah
        // ============================================================
        $mataKuliah = MasterMataKuliah::find($validatedData['id_mk']);
        $validatedData['sks'] = $mataKuliah->sks;

        // ============================================================
        // Pembuatan token enrollment unik
        // Token berupa 6 karakter huruf kapital acak (contoh: "ABCXYZ")
        // Loop do-while memastikan token yang dihasilkan belum ada di database
        // ============================================================
        do {
            $token = Str::upper(Str::random(6));
        } while (JadwalPerkuliahan::where('token_enrollment', $token)->exists());

        $validatedData['token_enrollment'] = $token;

        $jadwal = DB::transaction(function () use ($validatedData) {
            // ============================================================
            // Generate sesi pertemuan berdasarkan jumlah_sesi dan tanggal_mulai
            // ============================================================
            $sesiPertemuan = [];
            $validatedData['hari'] = \Carbon\Carbon::parse($validatedData['tanggal_mulai'])->locale('id')->dayName;
            
            // Simpan data baru ke tabel jadwal_perkuliahan
            // (id_jadwal UUID akan di-generate otomatis oleh trait HasUuids di Model)
            $jadwal = JadwalPerkuliahan::create($validatedData);

            $tanggalMulai = \Carbon\Carbon::parse($jadwal->tanggal_mulai);
            $waktuMulai = $jadwal->waktu_mulai;
            $waktuBerakhir = $jadwal->waktu_berakhir;
            $now = now();

            for ($i = 1; $i <= $jadwal->jumlah_sesi; $i++) {
                $sesiPertemuan[] = [
                    'id_sesi'             => (string) Str::uuid(),
                    'id_jadwal'           => $jadwal->id_jadwal,
                    'pertemuan_ke'        => $i,
                    'judul_sesi'          => "Pertemuan ke-{$i}",
                    'tanggal_pelaksanaan' => $tanggalMulai->copy()->addWeeks($i - 1)->format('Y-m-d'),
                    'jam_mulai'           => $waktuMulai,
                    'jam_berakhir'        => $waktuBerakhir,
                    'metode_pertemuan'    => 'asynchronous',
                    'materi'              => '-',
                    'status'              => 'TERJADWAL',
                    'created_at'          => $now,
                    'updated_at'          => $now,
                ];
            }

            \App\Models\SesiPertemuan::insert($sesiPertemuan);

            return $jadwal;
        });

        // Muat relasi agar response lengkap dengan data terkait
        $jadwal->load(['mataKuliah', 'kelas', 'dosen']);

        return response()->json([
            'message' => 'Jadwal perkuliahan berhasil ditambahkan.',
            'data'    => $jadwal,
        ], 201);
    }

    /**
     * Memperbarui data jadwal perkuliahan yang sudah ada (Update).
     * Token enrollment tidak diubah.
     * Jika id_mk berubah, maka SKS akan otomatis menyesuaikan dari mata kuliah baru.
     *
     * @param UpdateJadwalRequest $request
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function update(UpdateJadwalRequest $request, string $id_jadwal): JsonResponse
    {
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        $validatedData = $request->validated();

        // ============================================================
        // Cek apakah id_mk berubah dari data sebelumnya
        // Jika berubah, tarik ulang SKS dari mata kuliah yang baru dipilih
        // Jika tidak berubah, SKS tetap menggunakan nilai lama
        // ============================================================
        if ($validatedData['id_mk'] !== $jadwal->id_mk) {
            $mataKuliah = MasterMataKuliah::find($validatedData['id_mk']);
            $validatedData['sks'] = $mataKuliah->sks;
        }

        if (isset($validatedData['tanggal_mulai'])) {
            $validatedData['hari'] = \Carbon\Carbon::parse($validatedData['tanggal_mulai'])->locale('id')->dayName;
        }

        // Cek apakah waktu berubah
        $waktuMulaiBerubah = isset($validatedData['waktu_mulai']) && $validatedData['waktu_mulai'] !== $jadwal->waktu_mulai;
        $waktuBerakhirBerubah = isset($validatedData['waktu_berakhir']) && $validatedData['waktu_berakhir'] !== $jadwal->waktu_berakhir;

        // Update data jadwal (token_enrollment tidak diubah karena tidak ada di validated data)
        $jadwal->update($validatedData);

        // Jika waktu berubah, update semua sesi pertemuan yang masih TERJADWAL
        if ($waktuMulaiBerubah || $waktuBerakhirBerubah) {
            \App\Models\SesiPertemuan::where('id_jadwal', $id_jadwal)
                ->where('status', 'TERJADWAL')
                ->update([
                    'jam_mulai' => $jadwal->waktu_mulai,
                    'jam_berakhir' => $jadwal->waktu_berakhir,
                ]);
        }

        return response()->json([
            'message' => 'Jadwal perkuliahan berhasil diperbarui.',
        ], 200);
    }

    /**
     * Menghapus data jadwal perkuliahan dari database (Delete).
     *
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function destroy(string $id_jadwal): JsonResponse
    {
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        // Jika data tidak ditemukan, kembalikan response 404
        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        // Lakukan proses delete record dari database
        $jadwal->delete();

        return response()->json([
            'message' => 'Jadwal perkuliahan berhasil dihapus.',
        ], 200);
    }
    /**
     * Mengubah status akses bebas pada jadwal perkuliahan.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $id_jadwal
     * @return JsonResponse
     */
    public function toggleAksesBebas(\Illuminate\Http\Request $request, string $id_jadwal): JsonResponse
    {
        $jadwal = JadwalPerkuliahan::find($id_jadwal);

        if (!$jadwal) {
            return response()->json([
                'message' => 'Data jadwal perkuliahan tidak ditemukan.'
            ], 404);
        }

        $request->validate([
            'akses_bebas' => 'required|boolean'
        ]);

        $jadwal->update([
            'akses_bebas' => $request->akses_bebas
        ]);

        return response()->json([
            'message' => 'Akses bebas berhasil diperbarui.',
            'data' => $jadwal
        ], 200);
    }
}
