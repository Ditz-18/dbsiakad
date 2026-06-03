<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Nilai;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class BimbinganController extends Controller
{
    // GET /api/dosen/bimbingan
    public function index(Request $request)
    {
        $dosen = $request->user()->dosen;

        $mahasiswa = $dosen->mahasiswaBimbingan()
            ->with('programStudi')
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'nim'           => $m->nim,
                'nama'          => $m->nama,
                'program_studi' => $m->programStudi->nama,
                'angkatan'      => $m->angkatan,
                'semester'      => $m->semester,
                'status'        => $m->status,
            ]);

        return response()->json([
            'status' => true,
            'data'   => $mahasiswa,
        ]);
    }

    // GET /api/dosen/bimbingan/{mahasiswaId}
    public function show(Request $request, $mahasiswaId)
    {
        $dosen = $request->user()->dosen;

        $mahasiswa = Mahasiswa::where('id', $mahasiswaId)
            ->where('dosen_pa_id', $dosen->id)
            ->with(['programStudi', 'user'])
            ->firstOrFail();

        $semesterAktif = Semester::aktif()->first();

        // KRS semester aktif
        $krsAktif = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['kelas.mataKuliah'])
            ->get();

        // Riwayat nilai
        $nilaiAll = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->with(['kelas.mataKuliah', 'semester'])
            ->get();

        $totalSks   = $nilaiAll->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
        $totalBobot = $nilaiAll->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
        $ipk        = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        return response()->json([
            'status' => true,
            'data'   => [
                'mahasiswa'  => $mahasiswa,
                'ipk'        => $ipk,
                'total_sks'  => $totalSks,
                'krs_aktif'  => $krsAktif,
                'riwayat_nilai' => $nilaiAll,
            ],
        ]);
    }
}
