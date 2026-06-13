<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Krs;
use App\Models\Pembayaran;
use App\Models\Surat;
use App\Models\Semester;
use App\Models\Ujian;
use App\Models\Pengumuman;

class DashboardController extends Controller
{
    // GET /api/admin/dashboard
    public function index()
    {
        $semesterAktif = Semester::aktif()->first();

        // Mahasiswa terbaru (untuk tabel dashboard)
        $mahasiswaTerbaru = Mahasiswa::with(['programStudi'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'nama'          => $m->nama,
                'nim'           => $m->nim,
                'program_studi' => $m->programStudi?->nama,
                'angkatan'      => $m->angkatan,
                'status'        => $m->status,
            ]);

        // Surat masuk terbaru
        $suratTerbaru = Surat::with('mahasiswa')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(fn($s) => [
                'id'           => $s->id,
                'no_pengajuan' => $s->no_pengajuan,
                'mahasiswa'    => $s->mahasiswa->nama,
                'jenis_surat'  => $s->jenis_surat,
                'status'       => $s->status,
                'created_at'   => $s->created_at,
            ]);

        // Rekap pembayaran semester aktif
        $rekapPembayaran = [
            'lunas'     => 0,
            'menunggak' => 0,
        ];
        if ($semesterAktif) {
            $rekapPembayaran['lunas'] = Pembayaran::where('semester_id', $semesterAktif->id)
                ->where('status', 'Lunas')->count();
            $rekapPembayaran['menunggak'] = Pembayaran::where('semester_id', $semesterAktif->id)
                ->where('status', 'Menunggak')->count();
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'total_mahasiswa'           => Mahasiswa::where('status', 'Aktif')->count(),
                'total_dosen'               => Dosen::where('is_active', true)->count(),
                'total_krs_menunggu'        => Krs::where('status', 'Menunggu')
                    ->where('semester_id', optional($semesterAktif)->id)->count(),
                'total_pembayaran_menunggak'=> Pembayaran::menunggak()
                    ->where('semester_id', optional($semesterAktif)->id)->count(),
                'total_surat_menunggu'      => Surat::menunggu()->count(),
                'total_ujian_berlangsung'   => Ujian::berlangsung()->count(),
                'semester_aktif'            => $semesterAktif,
                'mahasiswa_terbaru'         => $mahasiswaTerbaru,
                'surat_terbaru'             => $suratTerbaru,
                'rekap_pembayaran'          => $rekapPembayaran,
            ],
        ]);
    }
}
