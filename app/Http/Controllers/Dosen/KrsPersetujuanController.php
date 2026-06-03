<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class KrsPersetujuanController extends Controller
{
    // GET /api/dosen/krs
    public function index(Request $request)
    {
        $dosen         = $request->user()->dosen;
        $semesterAktif = Semester::aktif()->first();

        // Hanya tampilkan KRS mahasiswa bimbingan dosen ini
        $krs = Krs::whereHas('mahasiswa', fn($q) => $q->where('dosen_pa_id', $dosen->id))
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['mahasiswa', 'kelas.mataKuliah', 'semester'])
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->orderBy('diajukan_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $krs,
        ]);
    }

    // PUT /api/dosen/krs/{id}/setujui
    public function setujui(Request $request, $id)
    {
        $dosen = $request->user()->dosen;

        $krs = Krs::whereHas('mahasiswa', fn($q) => $q->where('dosen_pa_id', $dosen->id))
            ->findOrFail($id);

        $request->validate([
            'catatan_pa' => 'nullable|string',
        ]);

        $krs->update([
            'status'      => 'Disetujui',
            'diproses_at' => now(),
            'catatan_pa'  => $request->catatan_pa,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'KRS berhasil disetujui.',
            'data'    => $krs->load(['mahasiswa', 'kelas.mataKuliah']),
        ]);
    }

    // PUT /api/dosen/krs/{id}/tolak
    public function tolak(Request $request, $id)
    {
        $dosen = $request->user()->dosen;

        $krs = Krs::whereHas('mahasiswa', fn($q) => $q->where('dosen_pa_id', $dosen->id))
            ->findOrFail($id);

        $request->validate([
            'catatan_pa' => 'required|string',
        ]);

        $krs->update([
            'status'      => 'Ditolak',
            'diproses_at' => now(),
            'catatan_pa'  => $request->catatan_pa,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'KRS ditolak.',
            'data'    => $krs->load(['mahasiswa', 'kelas.mataKuliah']),
        ]);
    }
}
