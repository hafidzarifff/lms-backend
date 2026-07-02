<?php

namespace App\Http\Controllers;

use App\Models\Sertifikat;
use App\Models\TemplateSertifikat;
use App\Models\Pengguna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class SertifikatController extends Controller
{
    /**
     * Get semua sertifikat dengan filter
     */
    public function index(Request $request)
    {
        $query = Sertifikat::with([
            'peserta.mahasiswa:id_user,nama_lengkap,nomor_induk,email',
            'peserta.jadwal:id_jadwal,id_mk,id_kelas,id_dosen',
            'peserta.jadwal.mataKuliah:id_mk,nama_mk,semester',
            'peserta.jadwal.kelas:id_kelas,nama_kelas',
            'peserta.jadwal.dosen:id_user,nama_lengkap,nomor_induk',
            'template:id_template,nama_template'
        ]);

        // Filter by peserta
        if ($request->has('id_peserta')) {
            $query->where('id_peserta', $request->id_peserta);
        }

        // Filter by template
        if ($request->has('id_template')) {
            $query->where('id_template', $request->id_template);
        }

        // Filter by tanggal
        if ($request->has('dari_tanggal') && $request->has('sampai_tanggal')) {
            $query->whereBetween('tanggal_terbit', [$request->dari_tanggal, $request->sampai_tanggal]);
        }

        $sertifikats = $query->orderBy('tanggal_terbit', 'desc')
            ->orderBy('nomor_sertifikat', 'desc')
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'status' => 'success',
            'data' => $sertifikats
        ]);
    }

    /**
     * Get sertifikat dikelompokkan per peserta
     */
    public function grouped(Request $request)
    {
        $query = \App\Models\PesertaKelas::where('status_kelayakan', 'Disetujui')
            ->with([
                'mahasiswa:id_user,nama_lengkap,nomor_induk,email',
                'jadwal:id_jadwal,id_mk,id_kelas,id_dosen,fakultas,prodi',
                'jadwal.mataKuliah:id_mk,nama_mk,semester',
                'jadwal.kelas:id_kelas,nama_kelas',
                'jadwal.dosen:id_user,nama_lengkap,nomor_induk',
                'sertifikat',
                'sertifikat.template:id_template,nama_template,tipe_sertifikat,layout_data'
            ])
            ->whereHas('sertifikat'); // Hanya tampilkan yang sudah punya sertifikat!

        $user = $request->user();
        if ($user && $user->role === \App\Enums\RolePengguna::Mahasiswa) {
            $query->where('id_mahasiswa', $user->id_user);
            
            // Tandai notifikasi terkait sertifikat sebagai sudah dibaca secara otomatis
            \App\Models\Notifikasi::where('id_user', $user->id_user)
                ->where('tipe', 'sertifikat')
                ->update(['is_read' => true]);
        }

        // Filter by Fakultas
        if ($request->has('fakultas') && !empty($request->fakultas)) {
            $query->whereHas('jadwal', function ($q) use ($request) {
                $q->where('fakultas', $request->fakultas);
            });
        }

        // Filter by Program Studi
        if ($request->has('prodi') && !empty($request->prodi)) {
            $query->whereHas('jadwal', function ($q) use ($request) {
                $q->where('prodi', $request->prodi);
            });
        }

        $peserta = $query->paginate($request->get('per_page', 15));

        // Hitung status kelulusan untuk setiap peserta
        $peserta->getCollection()->transform(function ($p) {
            $sesiPertemuan = \App\Models\SesiPertemuan::where('id_jadwal', $p->id_jadwal)->get();
            $tugasList = \App\Models\Tugas::whereIn('id_sesi', $sesiPertemuan->pluck('id_sesi'))->get();
            $nilaiPeserta = \App\Models\NilaiCbt::whereIn('id_tugas', $tugasList->pluck('id_tugas'))
                ->where('id_peserta', $p->id_mahasiswa)
                ->get();
            
            $totalTugasDinilai = $nilaiPeserta->count();
            $rataRata = $totalTugasDinilai > 0 ? ($nilaiPeserta->sum('nilai') / $totalTugasDinilai) : 0;
            
            $p->status_kelulusan = $rataRata >= 70 ? 'LULUS' : 'TIDAK LULUS';
            return $p;
        });

        return response()->json([
            'status' => 'success',
            'data' => $peserta
        ]);
    }

    /**
     * Get sertifikat untuk peserta tertentu
     */
    public function getByPeserta($id_peserta)
    {
        $peserta = \App\Models\PesertaKelas::find($id_peserta);
        
        if (!$peserta || $peserta->status_kelayakan !== 'Disetujui') {
            return response()->json([
                'status' => 'success',
                'data' => []
            ]);
        }

        $sertifikats = Sertifikat::where('id_peserta', $id_peserta)
            ->with(['peserta.mahasiswa:id_user,nama_lengkap,nomor_induk,email', 'template:id_template,nama_template,file_background,layout_data'])
            ->orderBy('tanggal_terbit', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $sertifikats
        ]);
    }

    /**
     * Get detail sertifikat
     */
    public function show($id_sertifikat)
    {
        $sertifikat = Sertifikat::with(['peserta', 'template'])
            ->find($id_sertifikat);

        if (!$sertifikat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $sertifikat
        ]);
    }

    /**
     * Terbitkan sertifikat baru
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_peserta' => 'required|uuid|exists:peserta_kelas,id_peserta',
            'id_template' => 'required|uuid|exists:template_sertifikat,id_template',
            'tanggal_terbit' => 'nullable|date',
            'file_sertifikat' => 'nullable|file|mimes:pdf|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah template aktif
        $template = TemplateSertifikat::where('id_template', $request->id_template)
            ->where('is_aktif', true)
            ->first();

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak aktif atau tidak ditemukan'
            ], 404);
        }

        try {
            $fileUrl = null;

            // Upload file sertifikat jika ada
            if ($request->hasFile('file_sertifikat')) {
                $file = $request->file('file_sertifikat');
                $fileName = 'sertifikats/' . Str::uuid() . '_' . $file->getClientOriginalName();
                $fileUrl = $file->storeAs('public', $fileName);
            }

            $tanggalTerbit = $request->tanggal_terbit ?? now();

            // Generate nomor sertifikat
            $nomorSertifikat = Sertifikat::generateNomorSertifikat();

            $sertifikat = Sertifikat::create([
                'id_sertifikat' => Str::uuid(),
                'id_peserta' => $request->id_peserta,
                'id_template' => $request->id_template,
                'nomor_sertifikat' => $nomorSertifikat,
                'tanggal_terbit' => $tanggalTerbit,
                'file_url' => $fileUrl,
            ]);

            $sertifikat->load(['peserta', 'template']);

            return response()->json([
                'status' => 'success',
                'message' => 'Sertifikat berhasil diterbitkan',
                'data' => $sertifikat
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerbitkan sertifikat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Terbitkan sertifikat bulk (untuk banyak peserta sekaligus)
     */
    public function storeBulk(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_template' => 'required|uuid|exists:template_sertifikat,id_template',
            'peserta' => 'required|array|min:1',
            'peserta.*.id_peserta' => 'required|uuid|exists:peserta_kelas,id_peserta',
            'tanggal_terbit' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Cek apakah template aktif
        $template = TemplateSertifikat::where('id_template', $request->id_template)
            ->where('is_aktif', true)
            ->first();

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak aktif atau tidak ditemukan'
            ], 404);
        }

        try {
            $tanggalTerbit = $request->tanggal_terbit ?? now();
            $sertifikats = [];

            foreach ($request->peserta as $item) {
                $nomorSertifikat = Sertifikat::generateNomorSertifikat();

                $sertifikat = Sertifikat::create([
                    'id_sertifikat' => Str::uuid(),
                    'id_peserta' => $item['id_peserta'],
                    'id_template' => $request->id_template,
                    'nomor_sertifikat' => $nomorSertifikat,
                    'tanggal_terbit' => $tanggalTerbit,
                    'file_url' => null,
                ]);

                $sertifikats[] = $sertifikat;
            }

            return response()->json([
                'status' => 'success',
                'message' => count($sertifikats) . ' sertifikat berhasil diterbitkan',
                'data' => $sertifikats,
                'total' => count($sertifikats)
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menerbitkan sertifikat: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sertifikat
     */
    public function update(Request $request, $id_sertifikat)
    {
        $sertifikat = Sertifikat::find($id_sertifikat);

        if (!$sertifikat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal_terbit' => 'nullable|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];

        if ($request->has('tanggal_terbit')) {
            $updateData['tanggal_terbit'] = $request->tanggal_terbit;
        }

        $sertifikat->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Sertifikat berhasil diupdate',
            'data' => $sertifikat
        ]);
    }

    /**
     * Upload file sertifikat PDF
     */
    public function uploadFile(Request $request, $id_sertifikat)
    {
        $sertifikat = Sertifikat::find($id_sertifikat);

        if (!$sertifikat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'file_sertifikat' => 'required|file|mimes:pdf|max:10240', // max 10MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Hapus file lama jika ada
            if ($sertifikat->file_url) {
                Storage::delete($sertifikat->file_url);
            }

            // Upload file baru
            $file = $request->file('file_sertifikat');
            $fileName = 'sertifikats/' . Str::uuid() . '_' . $file->getClientOriginalName();
            $fileUrl = $file->storeAs('public', $fileName);

            $sertifikat->update([
                'file_url' => $fileUrl
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'File sertifikat berhasil diupload',
                'data' => $sertifikat
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal upload file: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete sertifikat (soft delete)
     */
    public function destroy($id_sertifikat)
    {
        $sertifikat = Sertifikat::find($id_sertifikat);

        if (!$sertifikat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        // Hapus file jika ada
        if ($sertifikat->file_url) {
            Storage::delete($sertifikat->file_url);
        }

        $sertifikat->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Sertifikat berhasil dihapus'
        ]);
    }

    /**
     * Get URL download sertifikat
     */
    public function download($id_sertifikat)
    {
        $sertifikat = Sertifikat::find($id_sertifikat);

        if (!$sertifikat) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak ditemukan'
            ], 404);
        }

        if (!$sertifikat->file_url) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat belum memiliki file'
            ], 404);
        }

        if (!Storage::exists($sertifikat->file_url)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File sertifikat tidak ditemukan'
            ], 404);
        }

        $url = Storage::url($sertifikat->file_url);

        return response()->json([
            'status' => 'success',
            'data' => [
                'url' => $url,
                'nomor_sertifikat' => $sertifikat->nomor_sertifikat,
                'nama_file' => basename($sertifikat->file_url)
            ]
        ]);
    }

    /**
     * Verifikasi sertifikat berdasarkan nomor sertifikat
     */
    public function verify($nomor_sertifikat)
    {
        $sertifikats = Sertifikat::where('nomor_sertifikat', $nomor_sertifikat)
            ->with([
                'peserta.mahasiswa:id_user,nama_lengkap,nomor_induk,email', 
                'peserta.jadwal.mataKuliah',
                'peserta.jadwal.kelas:id_kelas,nama_kelas',
                'peserta.jadwal.dosen:id_user,nama_lengkap',
                'template:id_template,nama_template,file_background,layout_data'
            ])
            ->get();

        if ($sertifikats->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sertifikat tidak valid atau tidak ditemukan',
                'valid' => false
            ], 404);
        }

        $first = $sertifikats->first();

        return response()->json([
            'status' => 'success',
            'valid' => true,
            'message' => 'Sertifikat valid',
            'data' => [
                'nomor_sertifikat' => $first->nomor_sertifikat,
                'tanggal_terbit' => $first->tanggal_terbit ? $first->tanggal_terbit->locale('id')->translatedFormat('d F Y') : '-',
                'peserta' => $first->peserta,
                'sertifikats' => $sertifikats
            ]
        ]);
    }

    /**
     * Get statistik sertifikat
     */
    public function getStatistik()
    {
        $stats = DB::table('sertifikat')
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_sertifikat,
                COUNT(DISTINCT id_peserta) as total_penerima,
                COUNT(DISTINCT id_template) as total_template_digunakan,
                MIN(tanggal_terbit) as terbit_pertama,
                MAX(tanggal_terbit) as terbit_terakhir
            ')
            ->first();

        // Statistik per bulan (6 bulan terakhir)
        $perBulan = DB::table('sertifikat')
            ->whereNull('deleted_at')
            ->where('tanggal_terbit', '>=', now()->subMonths(6))
            ->selectRaw('
                DATE_TRUNC(\'month\', tanggal_terbit) as bulan,
                COUNT(*) as jumlah
            ')
            ->groupBy('bulan')
            ->orderBy('bulan', 'desc')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => [
                'summary' => $stats,
                'per_bulan' => $perBulan
            ]
        ]);
    }
}
