<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    // GET /api/mahasiswa/ujian
    public function index(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        // Ambil kelas yang diikuti mahasiswa
        $kelasIds = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->where('status', 'Disetujui')
            ->pluck('kelas_id');

        $ujian = Ujian::whereIn('kelas_id', $kelasIds)
            ->with(['kelas.mataKuliah', 'dosen'])
            ->orderBy('mulai_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $ujian,
        ]);
    }

    // GET /api/mahasiswa/ujian/{id}
    public function show(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $ujian = Ujian::with(['kelas.mataKuliah', 'dosen'])->findOrFail($id);

        // Pastikan mahasiswa terdaftar di kelas ujian ini
        $terdaftar = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('kelas_id', $ujian->kelas_id)
            ->where('status', 'Disetujui')
            ->exists();

        if (!$terdaftar) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak terdaftar di kelas ujian ini.',
            ], 403);
        }

        return response()->json([
            'status' => true,
            'data'   => $ujian,
        ]);
    }
}
