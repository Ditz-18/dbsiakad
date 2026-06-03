<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Nilai;
use App\Models\Semester;
use Illuminate\Http\Request;

class NilaiController extends Controller
{
    // GET /api/dosen/kelas/{kelasId}/nilai
    public function index(Request $request, $kelasId)
    {
        $dosen = $request->user()->dosen;

        $kelas = Kelas::where('id', $kelasId)
            ->where('dosen_id', $dosen->id)
            ->firstOrFail();

        $nilai = Nilai::where('kelas_id', $kelasId)
            ->with('mahasiswa')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'kelas' => $kelas->load('mataKuliah'),
                'nilai' => $nilai,
            ],
        ]);
    }

    // POST /api/dosen/kelas/{kelasId}/nilai
    public function store(Request $request, $kelasId)
    {
        $dosen = $request->user()->dosen;

        $kelas = Kelas::where('id', $kelasId)
            ->where('dosen_id', $dosen->id)
            ->firstOrFail();

        $semesterAktif = Semester::aktif()->first();

        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'nilai_tugas'  => 'nullable|numeric|min:0|max:100',
            'nilai_uts'    => 'nullable|numeric|min:0|max:100',
            'nilai_uas'    => 'nullable|numeric|min:0|max:100',
        ]);

        // Hitung nilai akhir: tugas 20%, UTS 35%, UAS 45%
        $nilaiTugas = $request->nilai_tugas ?? 0;
        $nilaiUts   = $request->nilai_uts ?? 0;
        $nilaiUas   = $request->nilai_uas ?? 0;
        $nilaiAkhir = ($nilaiTugas * 0.20) + ($nilaiUts * 0.35) + ($nilaiUas * 0.45);

        // Konversi ke grade
        [$grade, $bobot] = $this->hitungGrade($nilaiAkhir);

        $nilai = Nilai::updateOrCreate(
            [
                'mahasiswa_id' => $request->mahasiswa_id,
                'kelas_id'     => $kelasId,
                'semester_id'  => optional($semesterAktif)->id,
            ],
            [
                'nilai_tugas' => $request->nilai_tugas,
                'nilai_uts'   => $request->nilai_uts,
                'nilai_uas'   => $request->nilai_uas,
                'nilai_akhir' => round($nilaiAkhir, 2),
                'grade'       => $grade,
                'bobot'       => $bobot,
                'status'      => $bobot >= 2.0 ? 'Lulus' : 'Tidak Lulus',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Nilai berhasil disimpan.',
            'data'    => $nilai->load('mahasiswa'),
        ], 201);
    }

    // PUT /api/dosen/kelas/{kelasId}/nilai/{id}
    public function update(Request $request, $kelasId, $id)
    {
        $dosen = $request->user()->dosen;

        Kelas::where('id', $kelasId)->where('dosen_id', $dosen->id)->firstOrFail();

        $nilai = Nilai::where('id', $id)->where('kelas_id', $kelasId)->firstOrFail();

        $request->validate([
            'nilai_tugas' => 'nullable|numeric|min:0|max:100',
            'nilai_uts'   => 'nullable|numeric|min:0|max:100',
            'nilai_uas'   => 'nullable|numeric|min:0|max:100',
        ]);

        $nilaiTugas = $request->nilai_tugas ?? $nilai->nilai_tugas ?? 0;
        $nilaiUts   = $request->nilai_uts ?? $nilai->nilai_uts ?? 0;
        $nilaiUas   = $request->nilai_uas ?? $nilai->nilai_uas ?? 0;
        $nilaiAkhir = ($nilaiTugas * 0.20) + ($nilaiUts * 0.35) + ($nilaiUas * 0.45);

        [$grade, $bobot] = $this->hitungGrade($nilaiAkhir);

        $nilai->update([
            'nilai_tugas' => $request->nilai_tugas ?? $nilai->nilai_tugas,
            'nilai_uts'   => $request->nilai_uts ?? $nilai->nilai_uts,
            'nilai_uas'   => $request->nilai_uas ?? $nilai->nilai_uas,
            'nilai_akhir' => round($nilaiAkhir, 2),
            'grade'       => $grade,
            'bobot'       => $bobot,
            'status'      => $bobot >= 2.0 ? 'Lulus' : 'Tidak Lulus',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Nilai berhasil diperbarui.',
            'data'    => $nilai->load('mahasiswa'),
        ]);
    }

    private function hitungGrade(float $nilai): array
    {
        return match (true) {
            $nilai >= 85 => ['A',  4.0],
            $nilai >= 80 => ['A-', 3.7],
            $nilai >= 75 => ['B+', 3.3],
            $nilai >= 70 => ['B',  3.0],
            $nilai >= 65 => ['B-', 2.7],
            $nilai >= 60 => ['C+', 2.3],
            $nilai >= 55 => ['C',  2.0],
            $nilai >= 50 => ['C-', 1.7],
            $nilai >= 45 => ['D',  1.0],
            default      => ['E',  0.0],
        };
    }
}
