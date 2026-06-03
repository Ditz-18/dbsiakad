<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Nilai;
use App\Models\Semester;
use Illuminate\Http\Request;

class KhsController extends Controller
{
    // GET /api/mahasiswa/khs
    public function index(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $query = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->with(['kelas.mataKuliah', 'semester']);

        if ($request->semester_id) {
            $query->where('semester_id', $request->semester_id);
        }

        $nilai = $query->get();

        // Hitung IPS per semester
        $perSemester = $nilai->groupBy('semester_id')->map(function ($group) {
            $totalSks   = $group->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
            $totalBobot = $group->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
            $ips        = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

            return [
                'semester'   => $group->first()->semester,
                'nilai'      => $group,
                'total_sks'  => $totalSks,
                'ips'        => $ips,
            ];
        })->values();

        // Hitung IPK keseluruhan
        $totalSksAll   = $nilai->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
        $totalBobotAll = $nilai->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
        $ipk           = $totalSksAll > 0 ? round($totalBobotAll / $totalSksAll, 2) : 0;

        return response()->json([
            'status' => true,
            'data'   => [
                'ipk'          => $ipk,
                'total_sks'    => $totalSksAll,
                'per_semester' => $perSemester,
            ],
        ]);
    }
}
