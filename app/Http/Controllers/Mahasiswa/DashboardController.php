<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Krs;
use App\Models\Nilai;
use App\Models\Pembayaran;
use App\Models\Pengumuman;
use App\Models\Semester;
use App\Models\Ujian;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    // GET /api/mahasiswa/dashboard
    public function index(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        // SKS semester aktif
        $totalSks = 0;
        $jadwalHariIni = [];

        if ($semesterAktif) {
            $krsAktif = Krs::where('mahasiswa_id', $mahasiswa->id)
                ->where('semester_id', $semesterAktif->id)
                ->where('status', 'Disetujui')
                ->with('kelas.mataKuliah')
                ->get();

            $totalSks = $krsAktif->sum(fn($k) => $k->kelas->mataKuliah->sks ?? 0);

            // Jadwal hari ini
            $hariIni = now()->locale('id')->isoFormat('dddd');
            $hariMap = [
                'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
                'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
            ];
            $hariIniId = $hariMap[now()->format('l')] ?? now()->format('l');

            $jadwalHariIni = $krsAktif
                ->filter(fn($k) => $k->kelas->hari === $hariIniId)
                ->map(fn($k) => [
                    'mata_kuliah' => $k->kelas->mataKuliah->nama,
                    'jam_mulai'   => $k->kelas->jam_mulai,
                    'jam_selesai' => $k->kelas->jam_selesai,
                    'ruangan'     => $k->kelas->ruangan,
                ])
                ->values();
        }

        // IPK kumulatif (formula benar: bobot × sks / total sks)
        $nilaiAll   = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotNull('bobot')
            ->with('kelas.mataKuliah')
            ->get();
        $totalSksNilai   = $nilaiAll->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
        $totalBobotNilai = $nilaiAll->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
        $ipk = $totalSksNilai > 0 ? round($totalBobotNilai / $totalSksNilai, 2) : 0;
        $totalSksLulus = $nilaiAll->where('status', 'Lulus')
            ->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);

        // Pembayaran semester aktif
        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->first();

        // Ujian mendatang
        $ujianMendatang = [];
        if ($semesterAktif) {
            $kelasIds = Krs::where('mahasiswa_id', $mahasiswa->id)
                ->where('semester_id', $semesterAktif->id)
                ->where('status', 'Disetujui')
                ->pluck('kelas_id');

            $ujianMendatang = Ujian::whereIn('kelas_id', $kelasIds)
                ->whereIn('status', ['Draft', 'Berlangsung'])
                ->where('mulai_at', '>=', now())
                ->with('kelas.mataKuliah')
                ->orderBy('mulai_at')
                ->take(3)
                ->get();
        }

        // Pengumuman terbaru
        $pengumuman = Pengumuman::aktif()
            ->untuk('Mahasiswa')
            ->orderBy('penting', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return response()->json([
            'status' => true,
            'data'   => [
                'mahasiswa'       => $mahasiswa->load('programStudi'),
                'semester_aktif'  => $semesterAktif,
                'total_sks'       => $totalSks,
                'total_sks_lulus' => $totalSksLulus,
                'ipk'             => $ipk,
                'pembayaran'      => $pembayaran,
                'jadwal_hari_ini' => $jadwalHariIni,
                'ujian_mendatang' => $ujianMendatang,
                'pengumuman'      => $pengumuman,
            ],
        ]);
    }
}
