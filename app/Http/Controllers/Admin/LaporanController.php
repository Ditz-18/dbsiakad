<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Pembayaran;
use App\Models\Nilai;
use App\Models\Semester;
use Illuminate\Http\Request;

class LaporanController extends Controller
{
    // GET /api/admin/laporan/mahasiswa
    public function mahasiswa(Request $request)
    {
        $data = Mahasiswa::with(['programStudi'])
            ->when($request->program_studi_id, fn($q) => $q->where('program_studi_id', $request->program_studi_id))
            ->when($request->angkatan, fn($q) => $q->where('angkatan', $request->angkatan))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->get();

        $rekap = [
            'total'   => $data->count(),
            'aktif'   => $data->where('status', 'Aktif')->count(),
            'cuti'    => $data->where('status', 'Cuti')->count(),
            'lulus'   => $data->where('status', 'Lulus')->count(),
            'dropout' => $data->where('status', 'Dropout')->count(),
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'rekap'     => $rekap,
                'mahasiswa' => $data,
            ],
        ]);
    }

    // GET /api/admin/laporan/keuangan
    public function keuangan(Request $request)
    {
        $query = Pembayaran::with(['mahasiswa', 'semester'])
            ->when($request->semester_id, fn($q) => $q->where('semester_id', $request->semester_id));

        $data = $query->get();

        $rekap = [
            'total_tagihan' => $data->count(),
            'lunas'         => $data->where('status', 'Lunas')->count(),
            'menunggak'     => $data->where('status', 'Menunggak')->count(),
            'total_nominal' => $data->where('status', 'Lunas')->sum('nominal'),
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'rekap'      => $rekap,
                'pembayaran' => $data,
            ],
        ]);
    }

    // GET /api/admin/laporan/akademik
    public function akademik(Request $request)
    {
        $request->validate([
            'semester_id' => 'required|exists:semester,id',
        ]);

        $nilai = Nilai::with(['mahasiswa', 'kelas.mataKuliah'])
            ->where('semester_id', $request->semester_id)
            ->get();

        $rekap = [
            'total_nilai' => $nilai->count(),
            'lulus'       => $nilai->where('status', 'Lulus')->count(),
            'tidak_lulus' => $nilai->where('status', 'Tidak Lulus')->count(),
            'rata_rata'   => round($nilai->avg('nilai_akhir'), 2),
        ];

        return response()->json([
            'status' => true,
            'data'   => [
                'rekap' => $rekap,
                'nilai' => $nilai,
            ],
        ]);
    }
}
