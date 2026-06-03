<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    // GET /api/mahasiswa/jadwal
    public function index(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        if (!$semesterAktif) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada semester aktif.',
            ], 422);
        }

        $jadwal = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterAktif->id)
            ->where('status', 'Disetujui')
            ->with(['kelas.mataKuliah', 'kelas.dosen'])
            ->get()
            ->map(fn($krs) => [
                'kelas_id'    => $krs->kelas->id,
                'mata_kuliah' => $krs->kelas->mataKuliah->nama,
                'kode'        => $krs->kelas->mataKuliah->kode,
                'sks'         => $krs->kelas->mataKuliah->sks,
                'dosen'       => $krs->kelas->dosen->nama,
                'ruangan'     => $krs->kelas->ruangan,
                'hari'        => $krs->kelas->hari,
                'jam_mulai'   => $krs->kelas->jam_mulai,
                'jam_selesai' => $krs->kelas->jam_selesai,
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'semester' => $semesterAktif,
                'jadwal'   => $jadwal,
            ],
        ]);
    }
}
