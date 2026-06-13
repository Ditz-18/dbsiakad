<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class TranskripController extends Controller
{
    // GET /api/mahasiswa/transkrip
    public function index(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa->load(['programStudi', 'dosenPa', 'user']);

        // Semua nilai yang sudah ada
        $nilaiAll = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->with(['kelas.mataKuliah', 'semester'])
            ->whereNotNull('nilai_akhir')
            ->get();

        // Kelompokkan per semester
        $perSemester = $nilaiAll
            ->groupBy('semester_id')
            ->map(function ($group) {
                $semester   = $group->first()->semester;
                $totalSks   = $group->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
                $totalBobot = $group->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
                $ips        = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

                return [
                    'semester'   => $semester,
                    'mataKuliah' => $group->map(fn($n) => [
                        'kode'         => $n->kelas->mataKuliah->kode,
                        'nama'         => $n->kelas->mataKuliah->nama,
                        'sks'          => $n->kelas->mataKuliah->sks,
                        'nilai_akhir'  => $n->nilai_akhir,
                        'grade'        => $n->grade,
                        'bobot'        => $n->bobot,
                        'status'       => $n->status,
                    ])->values(),
                    'total_sks'  => $totalSks,
                    'ips'        => $ips,
                ];
            })
            ->sortBy('semester.nama')
            ->values();

        // Hitung IPK kumulatif
        $totalSksAll   = $nilaiAll->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
        $totalBobotAll = $nilaiAll->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
        $ipk           = $totalSksAll > 0 ? round($totalBobotAll / $totalSksAll, 2) : 0;

        // Total SKS lulus
        $totalSksLulus = $nilaiAll->where('status', 'Lulus')
            ->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);

        return response()->json([
            'status' => true,
            'data'   => [
                'mahasiswa'      => [
                    'nama'          => $mahasiswa->nama,
                    'nim'           => $mahasiswa->nim,
                    'program_studi' => $mahasiswa->programStudi?->nama,
                    'fakultas'      => $mahasiswa->programStudi?->fakultas,
                    'angkatan'      => $mahasiswa->angkatan,
                    'semester'      => $mahasiswa->semester,
                    'status'        => $mahasiswa->status,
                    'dosen_pa'      => $mahasiswa->dosenPa?->nama,
                ],
                'ipk'             => $ipk,
                'total_sks_lulus' => $totalSksLulus,
                'total_sks_ambil' => $totalSksAll,
                'per_semester'    => $perSemester,
            ],
        ]);
    }
}
