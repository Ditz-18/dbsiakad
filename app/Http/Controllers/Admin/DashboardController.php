<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\Dosen;
use App\Models\Krs;
use App\Models\Pembayaran;
use App\Models\Surat;
use App\Models\Semester;

class DashboardController extends Controller
{
    // GET /api/admin/dashboard
    public function index()
    {
        $semesterAktif = Semester::aktif()->first();

        return response()->json([
            'status' => true,
            'data'   => [
                'total_mahasiswa'    => Mahasiswa::where('status', 'Aktif')->count(),
                'total_dosen'        => Dosen::where('is_active', true)->count(),
                'total_krs_menunggu' => Krs::where('status', 'Menunggu')->count(),
                'total_pembayaran_menunggak' => Pembayaran::menunggak()->count(),
                'total_surat_menunggu' => Surat::menunggu()->count(),
                'semester_aktif'     => $semesterAktif,
            ],
        ]);
    }
}
