<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Semester;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    // GET /api/mahasiswa/absensi
    public function index(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        $absensi = Absensi::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['kelas.mataKuliah', 'kelas.dosen'])
            ->get()
            ->map(fn($a) => [
                'mata_kuliah'     => $a->kelas->mataKuliah->nama,
                'dosen'           => $a->kelas->dosen->nama,
                'total_pertemuan' => $a->total_pertemuan,
                'hadir'           => $a->hadir,
                'izin'            => $a->izin,
                'sakit'           => $a->sakit,
                'alpha'           => $a->alpha,
                'persentase'      => $a->persentase,
                'status'          => $a->persentase >= 75 ? 'Memenuhi' : 'Tidak Memenuhi',
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'semester' => $semesterAktif,
                'absensi'  => $absensi,
            ],
        ]);
    }
}
