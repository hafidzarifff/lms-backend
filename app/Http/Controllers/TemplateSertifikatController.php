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
            ->get();

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

        return response()->json([
            'status' => 'success',
            'data' => $template
        ]);
    }

    /**
     * Create template baru dengan upload background
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_template' => 'required|string|max:100',
            'file_background' => 'nullable|image|mimes:jpeg,jpg,png|max:5120', // max 5MB
            'is_aktif' => 'nullable|boolean',
            'layout_data' => 'nullable|string', // Accept as JSON string or array, depend on frontend
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
            if (is_string($layoutData)) {
                $layoutData = json_decode($layoutData, true);
            }

            $template = TemplateSertifikat::create([
                'id_template' => Str::uuid(),
                'nama_template' => $request->nama_template,
                'file_background' => $filePath,
                'is_aktif' => $request->is_aktif ?? true,
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
            'is_aktif' => 'nullable|boolean',
            'layout_data' => 'nullable|array',
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

        if ($request->has('is_aktif')) {
            $updateData['is_aktif'] = $request->is_aktif;
        }

        if ($request->has('layout_data')) {
            $updateData['layout_data'] = $request->layout_data;
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

        $template->update([
            'is_aktif' => !$template->is_aktif
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

        return response()->file($path);
    }
}
