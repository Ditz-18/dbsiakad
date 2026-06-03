<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // GET /api/dosen/kelas
    public function index(Request $request)
    {
        $dosen         = $request->user()->dosen;
        $semesterAktif = Semester::aktif()->first();

        $kelas = Kelas::where('dosen_id', $dosen->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['mataKuliah', 'semester'])
            ->get()
            ->map(fn($k) => [
                'id'          => $k->id,
                'kode_kelas'  => $k->kode_kelas,
                'mata_kuliah' => $k->mataKuliah->nama,
                'sks'         => $k->mataKuliah->sks,
                'ruangan'     => $k->ruangan,
                'hari'        => $k->hari,
                'jam_mulai'   => $k->jam_mulai,
                'jam_selesai' => $k->jam_selesai,
                'kuota'       => $k->kuota,
                'terisi'      => Krs::where('kelas_id', $k->id)
                                    ->where('status', 'Disetujui')->count(),
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'semester' => $semesterAktif,
                'kelas'    => $kelas,
            ],
        ]);
    }

    // GET /api/dosen/kelas/{id}
    public function show(Request $request, $id)
    {
        $dosen = $request->user()->dosen;

        $kelas = Kelas::where('id', $id)
            ->where('dosen_id', $dosen->id)
            ->with(['mataKuliah', 'semester'])
            ->firstOrFail();

        $mahasiswa = Krs::where('kelas_id', $kelas->id)
            ->where('status', 'Disetujui')
            ->with('mahasiswa')
            ->get()
            ->pluck('mahasiswa');

        return response()->json([
            'status' => true,
            'data'   => [
                'kelas'     => $kelas,
                'mahasiswa' => $mahasiswa,
            ],
        ]);
    }
}
