<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KtmController extends Controller
{
    /**
     * GET /api/mahasiswa/ktm
     * Mengembalikan data KTM milik mahasiswa yang sedang login.
     */
    public function show(Request $request)
    {
        $user      = $request->user();
        $mahasiswa = $user->mahasiswa; // relasi User → Mahasiswa

        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan.'], 404);
        }

        // Cek apakah ada tabel/model KTM khusus
        $ktm = null;
        if (class_exists(\App\Models\Ktm::class)) {
            $ktm = \App\Models\Ktm::where('mahasiswa_id', $mahasiswa->id)->latest()->first();
        }

        // Hitung berlaku_hingga: akhir tahun akademik berdasarkan angkatan
        // Konvensi: lulus normal = angkatan + 4 tahun, akhir bulan Agustus
        $angkatan     = (int) ($mahasiswa->angkatan ?? date('Y'));
        $tahunLulus   = $angkatan + 4;
        $berlakuHingga = $ktm?->berlaku_hingga
            ?? "{$tahunLulus}-08-31";

        $tanggalTerbit = $ktm?->tanggal_terbit
            ?? $ktm?->created_at
            ?? $mahasiswa->created_at
            ?? now();

        $nomorKtm = $ktm?->nomor_ktm
            ?? $ktm?->no_ktm
            ?? $mahasiswa->nim; // fallback ke NIM

        $status = $ktm?->status ?? ($mahasiswa->status ?? 'aktif');

        return response()->json([
            'success' => true,
            'data'    => [
                'nomor_ktm'      => $nomorKtm,
                'nama'           => $mahasiswa->nama,
                'nim'            => $mahasiswa->nim,
                'program_studi'  => [
                    'nama'     => $mahasiswa->programStudi?->nama
                                   ?? $mahasiswa->prodi
                                   ?? null,
                    'fakultas' => $mahasiswa->programStudi?->fakultas
                                   ?? $mahasiswa->fakultas
                                   ?? null,
                ],
                'angkatan'       => $mahasiswa->angkatan,
                'tanggal_terbit' => $tanggalTerbit instanceof \Carbon\Carbon
                                        ? $tanggalTerbit->toDateString()
                                        : $tanggalTerbit,
                'berlaku_hingga' => $berlakuHingga,
                'status'         => $status,
                'foto'           => $mahasiswa->foto ?? null,
            ],
        ]);
    }
}
