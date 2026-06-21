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
        $mahasiswa = Mahasiswa::with(['programStudi'])->findOrFail($mahasiswaId);

        return response()->json([
            'status' => true,
            'data'   => [
                'nim'                => $mahasiswa->nim,
                'nama'               => $mahasiswa->nama,
                'program_studi'      => $mahasiswa->programStudi->nama,
                'fakultas'           => $mahasiswa->programStudi->fakultas,
                'angkatan'           => $mahasiswa->angkatan,
                'status'             => $mahasiswa->status,
                'foto'               => $mahasiswa->foto,
                'ktm_aktif'          => $mahasiswa->ktm_aktif,
                'ktm_berlaku_hingga' => $mahasiswa->ktm_berlaku_hingga,
                'ktm_generated_at'   => $mahasiswa->ktm_generated_at,
            ],
        ]);
    }

    // POST /api/admin/ktm/{mahasiswaId}/generate
    public function generate(Request $request, $mahasiswaId)
    {
        $mahasiswa = Mahasiswa::with(['programStudi'])->findOrFail($mahasiswaId);

        $request->validate([
            'berlaku_hingga' => 'nullable|date|after:today',
        ]);

        $berlakuHingga = $request->berlaku_hingga ?? now()->addYears(4)->toDateString();

        $mahasiswa->update([
            'ktm_aktif'          => true,
            'ktm_berlaku_hingga' => $berlakuHingga,
            'ktm_generated_at'   => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'KTM berhasil digenerate.',
            'data'    => [
                'nim'                => $mahasiswa->nim,
                'nama'               => $mahasiswa->nama,
                'program_studi'      => $mahasiswa->programStudi->nama,
                'angkatan'           => $mahasiswa->angkatan,
                'ktm_aktif'          => true,
                'ktm_berlaku_hingga' => $berlakuHingga,
                'ktm_generated_at'   => $mahasiswa->ktm_generated_at,
            ],
        ]);
    }
}
