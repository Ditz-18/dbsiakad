<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;

class KtmController extends Controller
{
    // GET /api/admin/ktm/{mahasiswaId}
    public function show($mahasiswaId)
    {
        $mahasiswa = Mahasiswa::with(['programStudi', 'user'])->findOrFail($mahasiswaId);

        return response()->json([
            'status' => true,
            'data'   => [
                'nim'           => $mahasiswa->nim,
                'nama'          => $mahasiswa->nama,
                'program_studi' => $mahasiswa->programStudi->nama,
                'fakultas'      => $mahasiswa->programStudi->fakultas,
                'angkatan'      => $mahasiswa->angkatan,
                'status'        => $mahasiswa->status,
                'foto'          => $mahasiswa->foto,
            ],
        ]);
    }

    // POST /api/admin/ktm/{mahasiswaId}/generate
    public function generate($mahasiswaId)
    {
        $mahasiswa = Mahasiswa::with(['programStudi'])->findOrFail($mahasiswaId);

        // Placeholder — generate KTM PDF bisa diintegrasikan dengan library seperti DomPDF
        return response()->json([
            'status'  => true,
            'message' => 'KTM berhasil digenerate.',
            'data'    => [
                'nim'           => $mahasiswa->nim,
                'nama'          => $mahasiswa->nama,
                'program_studi' => $mahasiswa->programStudi->nama,
                'angkatan'      => $mahasiswa->angkatan,
                'download_url'  => url("api/admin/ktm/{$mahasiswaId}/download"),
            ],
        ]);
    }
}
