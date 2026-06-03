<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Nilai;
use App\Models\Pembayaran;
use App\Models\Pengumuman;
use App\Models\Semester;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // GET /api/mahasiswa/dashboard
    public function index(Request $request)
    {
        $mahasiswa      = $request->user()->mahasiswa;
        $semesterAktif  = Semester::aktif()->first();

        $totalSks = 0;
        $ipk      = 0;

        if ($semesterAktif) {
            $krsAktif = Krs::where('mahasiswa_id', $mahasiswa->id)
                ->where('semester_id', $semesterAktif->id)
                ->where('status', 'Disetujui')
                ->with('kelas.mataKuliah')
                ->get();

            $totalSks = $krsAktif->sum(fn($k) => $k->kelas->mataKuliah->sks ?? 0);
        }

        $nilaiAll = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotNull('bobot')
            ->get();

        if ($nilaiAll->count() > 0) {
            $totalBobot = $nilaiAll->sum('bobot');
            $totalSksNilai = $nilaiAll->count();
            $ipk = $totalSksNilai > 0 ? round($nilaiAll->avg('bobot'), 2) : 0;
        }

        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->first();

        $pengumuman = Pengumuman::aktif()
            ->untuk('Mahasiswa')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'mahasiswa'      => $mahasiswa->load('programStudi'),
                'semester_aktif' => $semesterAktif,
                'total_sks'      => $totalSks,
                'ipk'            => $ipk,
                'pembayaran'     => $pembayaran,
                'pengumuman'     => $pengumuman,
            ],
        ]);
    }
}
