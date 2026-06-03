<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Semester;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    // GET /api/mahasiswa/pembayaran
    public function index(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $pembayaran = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->with('semester')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $pembayaran,
        ]);
    }

    // POST /api/mahasiswa/pembayaran
    public function store(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        if (!$semesterAktif) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada semester aktif.',
            ], 422);
        }

        // Cek sudah pernah bayar semester ini
        $exists = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterAktif->id)
            ->where('status', 'Lunas')
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Pembayaran semester ini sudah lunas.',
            ], 422);
        }

        $request->validate([
            'nominal'      => 'required|integer|min:1',
            'no_referensi' => 'required|string',
        ]);

        $pembayaran = Pembayaran::updateOrCreate(
            ['mahasiswa_id' => $mahasiswa->id, 'semester_id' => $semesterAktif->id],
            [
                'nominal'      => $request->nominal,
                'no_referensi' => $request->no_referensi,
                'status'       => 'Menunggak',
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Bukti pembayaran berhasil dikirim. Menunggu konfirmasi admin.',
            'data'    => $pembayaran->load('semester'),
        ], 201);
    }
}
