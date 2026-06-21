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
                'mahasiswa_id'    => $a->mahasiswa_id,
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

    // POST /api/dosen/kelas/{kelasId}/absensi/catat-pertemuan
    // Mencatat kehadiran untuk 1 pertemuan baru — menambah counter kumulatif setiap mahasiswa
    public function catatPertemuan(Request $request, $kelasId)
    {
        $dosen = $request->user()->dosen;
        Kelas::where('id', $kelasId)->where('dosen_id', $dosen->id)->firstOrFail();

        $request->validate([
            'tanggal'           => 'required|date',
            'kehadiran'         => 'required|array|min:1',
            'kehadiran.*.mahasiswa_id' => 'required|exists:mahasiswa,id',
            'kehadiran.*.status'       => 'required|in:Hadir,Izin,Sakit,Alpha',
        ]);

        $semesterAktif = \App\Models\Semester::aktif()->first();
        $diperbarui = 0;

        foreach ($request->kehadiran as $item) {
            $absensi = Absensi::firstOrCreate(
                [
                    'mahasiswa_id' => $item['mahasiswa_id'],
                    'kelas_id'     => $kelasId,
                    'semester_id'  => optional($semesterAktif)->id,
                ],
                [
                    'total_pertemuan' => 0, 'hadir' => 0, 'izin' => 0,
                    'sakit' => 0, 'alpha' => 0, 'persentase' => 0,
                ]
            );

            $absensi->total_pertemuan += 1;
            $kolom = strtolower($item['status']);
            $absensi->{$kolom} += 1;
            $absensi->persentase = $absensi->total_pertemuan > 0
                ? round(($absensi->hadir / $absensi->total_pertemuan) * 100, 2)
                : 0;
            $absensi->save();
            $diperbarui++;
        }

        return response()->json([
            'status'  => true,
            'message' => "Kehadiran pertemuan berhasil dicatat untuk {$diperbarui} mahasiswa.",
        ]);
    }
}
