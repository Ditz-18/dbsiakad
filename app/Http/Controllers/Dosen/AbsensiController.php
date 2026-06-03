<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\Kelas;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    // GET /api/dosen/kelas/{kelasId}/absensi
    public function index(Request $request, $kelasId)
    {
        $dosen = $request->user()->dosen;

        $kelas = Kelas::where('id', $kelasId)
            ->where('dosen_id', $dosen->id)
            ->firstOrFail();

        $absensi = Absensi::where('kelas_id', $kelasId)
            ->with('mahasiswa')
            ->get()
            ->map(fn($a) => [
                'id'              => $a->id,
                'mahasiswa'       => $a->mahasiswa->nama,
                'nim'             => $a->mahasiswa->nim,
                'total_pertemuan' => $a->total_pertemuan,
                'hadir'           => $a->hadir,
                'izin'            => $a->izin,
                'sakit'           => $a->sakit,
                'alpha'           => $a->alpha,
                'persentase'      => $a->persentase,
                'status'          => $a->persentase >= 75 ? 'Memenuhi' : 'Tidak Memenuhi',
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'kelas'   => $kelas->load('mataKuliah'),
                'absensi' => $absensi,
            ],
        ]);
    }

    // PUT /api/dosen/kelas/{kelasId}/absensi/{id}
    public function update(Request $request, $kelasId, $id)
    {
        $dosen = $request->user()->dosen;

        Kelas::where('id', $kelasId)->where('dosen_id', $dosen->id)->firstOrFail();

        $absensi = Absensi::where('id', $id)->where('kelas_id', $kelasId)->firstOrFail();

        $request->validate([
            'total_pertemuan' => 'required|integer|min:0',
            'hadir'           => 'required|integer|min:0',
            'izin'            => 'required|integer|min:0',
            'sakit'           => 'required|integer|min:0',
            'alpha'           => 'required|integer|min:0',
        ]);

        $persentase = $request->total_pertemuan > 0
            ? round(($request->hadir / $request->total_pertemuan) * 100, 2)
            : 0;

        $absensi->update([
            'total_pertemuan' => $request->total_pertemuan,
            'hadir'           => $request->hadir,
            'izin'            => $request->izin,
            'sakit'           => $request->sakit,
            'alpha'           => $request->alpha,
            'persentase'      => $persentase,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Absensi berhasil diperbarui.',
            'data'    => $absensi->load('mahasiswa'),
        ]);
    }
}
