<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DokumenController extends Controller
{
    // GET /api/mahasiswa/dokumen
    public function index(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $surat = Surat::where('mahasiswa_id', $mahasiswa->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $surat,
        ]);
    }

    // POST /api/mahasiswa/dokumen
    public function store(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $request->validate([
            'jenis_surat' => 'required|string',
            'keperluan'   => 'required|string',
        ]);

        $noPengajuan = 'SRT-' . strtoupper(Str::random(8)) . '-' . now()->format('Ymd');

        $surat = Surat::create([
            'no_pengajuan' => $noPengajuan,
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat'  => $request->jenis_surat,
            'keperluan'    => $request->keperluan,
            'status'       => 'Menunggu',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Pengajuan surat berhasil dikirim.',
            'data'    => $surat,
        ], 201);
    }

    // GET /api/mahasiswa/dokumen/{id}
    public function show(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $surat = Surat::where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->firstOrFail();

        return response()->json([
            'status' => true,
            'data'   => $surat,
        ]);
    }
}
