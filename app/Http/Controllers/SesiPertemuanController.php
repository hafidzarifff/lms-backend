<?php

namespace App\Http\Controllers;

use App\Models\SesiPertemuan;
use App\Http\Requests\StoreSesiPertemuanRequest;

class SesiPertemuanController extends Controller
{
    public function store(StoreSesiPertemuanRequest $request)
    {
        $validatedData = $request->validated();

        $duplicatePertemuanKe = SesiPertemuan::where('id_jadwal', $validatedData['id_jadwal'])
            ->where('pertemuan_ke', $validatedData['pertemuan_ke'])
            ->exists();

        if ($duplicatePertemuanKe) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pertemuan ke-'.$validatedData['pertemuan_ke'].' sudah ada untuk jadwal ini.'
            ], 422);
        }

        $overlappingSesi = SesiPertemuan::where('id_jadwal', $validatedData['id_jadwal'])
            ->where('tanggal_pelaksanaan', $validatedData['tanggal_pelaksanaan'])
            ->where(function ($query) use ($validatedData) {
                $query->where('jam_mulai', '<', $validatedData['jam_berakhir'])
                      ->where('jam_berakhir', '>', $validatedData['jam_mulai']);
            })
            ->exists();

        if ($overlappingSesi) {
            return response()->json([
                'status' => 'error',
                'message' => 'Waktu sesi bentrok dengan sesi lain pada tanggal yang sama.'
            ], 422);
        }

        $sesiPertemuan = SesiPertemuan::create($validatedData);

        return response()->json([
            'status' => 'success',
            'message' => 'Sesi pertemuan berhasil dibuat.',
            'data' => $sesiPertemuan
        ], 201);
    }
}
