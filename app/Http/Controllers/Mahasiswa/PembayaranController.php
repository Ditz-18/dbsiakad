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
            ->get()
            ->map(fn($p) => [
                'id'             => $p->id,
                'semester'       => $p->semester->nama ?? '—',
                'nominal'        => $p->nominal,
                'status'         => $p->status,
                'tanggal_bayar'  => $p->tanggal_bayar,
                'no_referensi'   => $p->no_referensi,
                'dikonfirmasi'   => $p->dikonfirmasi_oleh ? true : false,
            ]);

        // Tagihan semester aktif
        $semesterAktif = Semester::aktif()->first();
        $tagihanAktif  = null;
        if ($semesterAktif) {
            $tagihanAktif = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
                ->where('semester_id', $semesterAktif->id)
                ->with('semester')
                ->first();
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'tagihan_aktif' => $tagihanAktif,
                'riwayat'       => $pembayaran,
            ],
        ]);
    }

    // POST /api/mahasiswa/pembayaran  — upload bukti bayar
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

        // Cek sudah lunas
        $sudahLunas = Pembayaran::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterAktif->id)
            ->where('status', 'Lunas')
            ->exists();

        if ($sudahLunas) {
            return response()->json([
                'status'  => false,
                'message' => 'Pembayaran semester ini sudah lunas.',
            ], 422);
        }

        $request->validate([
            'no_referensi' => 'required|string|max:255',
            'nominal'      => 'required|integer|min:1',
        ]);

        // Update tagihan yang ada, atau buat baru jika belum ada tagihan
        $pembayaran = Pembayaran::updateOrCreate(
            [
                'mahasiswa_id' => $mahasiswa->id,
                'semester_id'  => $semesterAktif->id,
            ],
            [
                'nominal'       => $request->nominal,
                'no_referensi'  => $request->no_referensi,
                'tanggal_bayar' => now()->toDateString(),
                'status'        => 'Menunggak', // Menunggu konfirmasi admin
            ]
        );

        return response()->json([
            'status'  => true,
            'message' => 'Bukti pembayaran berhasil dikirim. Menunggu konfirmasi admin.',
            'data'    => $pembayaran->load('semester'),
        ], 201);
    }
}
