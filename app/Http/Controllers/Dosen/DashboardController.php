<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Ujian;
use App\Models\Semester;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // GET /api/dosen/dashboard
    public function index(Request $request)
    {
        $dosen         = $request->user()->dosen;
        $semesterAktif = Semester::aktif()->first();

        $totalKelas = Kelas::where('dosen_id', $dosen->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->count();

        // Total mahasiswa unik di semua kelas yang diampu (bukan mahasiswa bimbingan PA)
        $kelasIds = Kelas::where('dosen_id', $dosen->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->pluck('id');
        $totalMahasiswa = Krs::whereIn('kelas_id', $kelasIds)
            ->where('status', 'Disetujui')
            ->distinct('mahasiswa_id')
            ->count('mahasiswa_id');

        $totalMahasiswaBimbingan = $dosen->mahasiswaBimbingan()->count();

        $totalKrsMenunggu = Krs::whereHas('kelas', fn($q) => $q->where('dosen_id', $dosen->id))
            ->where('semester_id', optional($semesterAktif)->id)
            ->where('status', 'Menunggu')
            ->count();

        $ujianBerlangsung = Ujian::where('dosen_id', $dosen->id)
            ->where('status', 'Berlangsung')
            ->count();

        return response()->json([
            'status' => true,
            'data'   => [
                'dosen'                   => $dosen->load('programStudi'),
                'semester_aktif'          => $semesterAktif,
                'total_kelas'             => $totalKelas,
                'total_mahasiswa'         => $totalMahasiswa,
                'total_mahasiswa_bimbingan' => $totalMahasiswaBimbingan,
                'total_krs_menunggu'      => $totalKrsMenunggu,
                'ujian_berlangsung'       => $ujianBerlangsung,
            ],
        ]);
    }
}
