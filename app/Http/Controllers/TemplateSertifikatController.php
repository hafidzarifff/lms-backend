<?php

namespace App\Http\Controllers;

use App\Models\TemplateSertifikat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TemplateSertifikatController extends Controller
{
    /**
     * Get semua template sertifikat
     */
    public function index(Request $request)
    {
        $query = TemplateSertifikat::query();

        // Filter by status aktif
        if ($request->has('aktif')) {
            if ($request->aktif === 'true') {
                $query->aktif();
            }
        }

        $templates = $query->orderBy('nama_template')->get();

        return response()->json([
            'status' => 'success',
            'data' => $templates
        ]);
    }

    /**
     * Get template aktif saja
     */
    public function getAktif()
    {
        $templates = TemplateSertifikat::aktif()
            ->orderBy('nama_template')
            ->get()
            ->map(function ($t) {
                $arr = $t->toArray();
                $arr['background_url'] = $t->file_background
                    ? request()->getSchemeAndHttpHost() . '/storage/' . $t->file_background
                    : null;
                return $arr;
            });

        return response()->json([
            'status' => 'success',
            'data' => $templates
        ]);
    }

    /**
     * Get detail template
     */
    public function show($id_template)
    {
        $template = TemplateSertifikat::find($id_template);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        $data = $template->toArray();
        $data['background_url'] = $template->file_background
            ? request()->getSchemeAndHttpHost() . '/storage/' . $template->file_background
            : null;

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Create template baru dengan upload background
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:100',
            'tipe_sertifikat' => 'required|in:pelatihan,kelulusan,nilai',
            'file_background' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // max 5MB
            'is_aktif' => 'nullable',
            'layout_data' => 'nullable|string', // Accept as JSON string from FormData
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $filePath = null;

            // Upload file background jika ada
            if ($request->hasFile('file_background')) {
                $file = $request->file('file_background');
                $fileName = 'templates/sertifikat/' . Str::uuid() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('', $fileName, 'public');
            }

            // Handle layout_data jika dikirim sebagai string JSON
            $layoutData = $request->layout_data;
            if (is_string($layoutData) && !empty($layoutData)) {
                $decoded = json_decode($layoutData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $layoutData = $decoded;
                }
            }

            $isAktif = filter_var($request->is_aktif ?? true, FILTER_VALIDATE_BOOLEAN);
            if ($isAktif) {
                TemplateSertifikat::where('tipe_sertifikat', $request->tipe_sertifikat)->update(['is_aktif' => false]);
            }

            $template = TemplateSertifikat::create([
                'id_template' => Str::uuid(),
                'nama_template' => $request->nama_template,
                'tipe_sertifikat' => $request->tipe_sertifikat,
                'file_background' => $filePath,
                'is_aktif' => $isAktif,
                'layout_data' => $layoutData,
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Template sertifikat berhasil dibuat',
                'data' => $template
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat template: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update template
     */
    public function update(Request $request, $id_template)
    {
        $template = TemplateSertifikat::find($id_template);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_template' => 'nullable|string|max:100',
            'tipe_sertifikat' => 'nullable|in:pelatihan,kelulusan,nilai',
            'is_aktif' => 'nullable',
            'layout_data' => 'nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = [];

        if ($request->has('nama_template')) {
            $updateData['nama_template'] = $request->nama_template;
        }

        if ($request->has('tipe_sertifikat')) {
            $updateData['tipe_sertifikat'] = $request->tipe_sertifikat;
        }

        if ($request->has('is_aktif')) {
            $isAktif = filter_var($request->is_aktif, FILTER_VALIDATE_BOOLEAN);
            $updateData['is_aktif'] = $isAktif;
            if ($isAktif) {
                $tipe = $request->tipe_sertifikat ?? $template->tipe_sertifikat;
                TemplateSertifikat::where('tipe_sertifikat', $tipe)
                    ->where('id_template', '!=', $id_template)
                    ->update(['is_aktif' => false]);
            }
        }

        if ($request->has('layout_data')) {
            $layoutData = $request->layout_data;
            // Handle jika dikirim sebagai string JSON
            if (is_string($layoutData) && !empty($layoutData)) {
                $decoded = json_decode($layoutData, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    $layoutData = $decoded;
                }
            }
            $updateData['layout_data'] = $layoutData;
        }

        $template->update($updateData);

        return response()->json([
            'status' => 'success',
            'message' => 'Template berhasil diupdate',
            'data' => $template
        ]);
    }

    /**
     * Upload/Update file background template
     */
    public function uploadBackground(Request $request, $id_template)
    {
        $template = TemplateSertifikat::find($id_template);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'file_background' => 'required|image|mimes:jpeg,jpg,png|max:5120', // max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Hapus file lama jika ada
            if ($template->file_background) {
                Storage::disk('public')->delete($template->file_background);
            }

            // Upload file baru
            $file = $request->file('file_background');
            $fileName = 'templates/sertifikat/' . Str::uuid() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('', $fileName, 'public');

            $template->update([
                'file_background' => $filePath
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Background template berhasil diupload',
                'data' => $template
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal upload background: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete template (soft delete)
     */
    public function destroy($id_template)
    {
        $template = TemplateSertifikat::find($id_template);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        // Hapus file background jika ada
        if ($template->file_background) {
            Storage::disk('public')->delete($template->file_background);
        }

        $template->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Template berhasil dihapus'
        ]);
    }

    /**
     * Toggle status aktif template
     */
    public function toggleAktif($id_template)
    {
        $template = TemplateSertifikat::find($id_template);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        $newStatus = !$template->is_aktif;
        
        if ($newStatus) {
            TemplateSertifikat::where('tipe_sertifikat', $template->tipe_sertifikat)
                ->where('id_template', '!=', $id_template)
                ->update(['is_aktif' => false]);
        }

        $template->update([
            'is_aktif' => $newStatus
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Status aktif berhasil diubah',
            'data' => $template
        ]);
    }

    /**
     * Get URL download background template
     */
    public function downloadBackground($id_template)
    {
        $template = TemplateSertifikat::find($id_template);

        if (!$template) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak ditemukan'
            ], 404);
        }

        if (!$template->file_background) {
            return response()->json([
                'status' => 'error',
                'message' => 'Template tidak memiliki file background'
            ], 404);
        }

        if (!Storage::disk('public')->exists($template->file_background)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File background tidak ditemukan'
            ], 404);
        }

        $path = storage_path('app/public/' . $template->file_background);

        if (!file_exists($path)) {
            return response()->json([
                'status' => 'error',
                'message' => 'File background tidak ditemukan di server'
            ], 404);
        }

        $file = file_get_contents($path);
        $type = mime_content_type($path);
        return response($file, 200)
            ->header('Content-Type', $type)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization')
            ->header('Cross-Origin-Resource-Policy', 'cross-origin');
    }
}
