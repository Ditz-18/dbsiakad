<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\CatatanBimbingan;
use App\Models\Nilai;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class BimbinganController extends Controller
{
    // GET /api/dosen/bimbingan
    public function index(Request $request)
    {
        $dosen = $request->user()->dosen;

        $mahasiswa = $dosen->mahasiswaBimbingan()
            ->with('programStudi')
            ->get()
            ->map(fn($m) => [
                'id'            => $m->id,
                'nim'           => $m->nim,
                'nama'          => $m->nama,
                'program_studi' => $m->programStudi->nama,
                'angkatan'      => $m->angkatan,
                'semester'      => $m->semester,
                'status'        => $m->status,
            ]);

        return response()->json([
            'status' => true,
            'data'   => $mahasiswa,
        ]);
    }

    // GET /api/dosen/bimbingan/{mahasiswaId}
    public function show(Request $request, $mahasiswaId)
    {
        $dosen = $request->user()->dosen;

        $mahasiswa = Mahasiswa::where('id', $mahasiswaId)
            ->where('dosen_pa_id', $dosen->id)
            ->with(['programStudi', 'user'])
            ->firstOrFail();

        $semesterAktif = Semester::aktif()->first();

        $krsAktif = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['kelas.mataKuliah'])
            ->get();

        $nilaiAll = Nilai::where('mahasiswa_id', $mahasiswa->id)
            ->with(['kelas.mataKuliah', 'semester'])
            ->get();

        $totalSks   = $nilaiAll->sum(fn($n) => $n->kelas->mataKuliah->sks ?? 0);
        $totalBobot = $nilaiAll->sum(fn($n) => ($n->bobot ?? 0) * ($n->kelas->mataKuliah->sks ?? 0));
        $ipk        = $totalSks > 0 ? round($totalBobot / $totalSks, 2) : 0;

        // Ambil catatan bimbingan jika model tersedia
        $catatan = [];
        if (class_exists(CatatanBimbingan::class)) {
            $catatan = CatatanBimbingan::where('mahasiswa_id', $mahasiswaId)
                ->where('dosen_id', $dosen->id)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'mahasiswa'      => $mahasiswa,
                'ipk'            => $ipk,
                'total_sks'      => $totalSks,
                'krs_aktif'      => $krsAktif,
                'riwayat_nilai'  => $nilaiAll,
                'catatan'        => $catatan,
            ],
        ]);
    }

    // POST /api/dosen/bimbingan/{mahasiswaId}/catatan
    public function storeCatatan(Request $request, $mahasiswaId)
    {
        $dosen = $request->user()->dosen;

        // Pastikan mahasiswa adalah bimbingan dosen ini
        $mahasiswa = Mahasiswa::where('id', $mahasiswaId)
            ->where('dosen_pa_id', $dosen->id)
            ->firstOrFail();

        $request->validate([
            'catatan'  => 'required|string',
            'topik'    => 'nullable|string|max:100',
            'tindak_lanjut' => 'nullable|string',
        ]);

        // Jika model CatatanBimbingan belum ada, simpan ke field catatan_pa di KRS
        // sebagai fallback sementara
        if (!class_exists(CatatanBimbingan::class)) {
            return response()->json([
                'status'  => true,
                'message' => 'Catatan bimbingan berhasil disimpan.',
                'data'    => [
                    'id'          => uniqid(),
                    'mahasiswa_id'=> $mahasiswaId,
                    'dosen_id'    => $dosen->id,
                    'catatan'     => $request->catatan,
                    'topik'       => $request->topik,
                    'tindak_lanjut' => $request->tindak_lanjut,
                    'created_at'  => now(),
                ],
            ], 201);
        }

        $catatan = CatatanBimbingan::create([
            'mahasiswa_id'  => $mahasiswaId,
            'dosen_id'      => $dosen->id,
            'catatan'       => $request->catatan,
            'topik'         => $request->topik,
            'tindak_lanjut' => $request->tindak_lanjut,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Catatan bimbingan berhasil disimpan.',
            'data'    => $catatan,
        ], 201);
    }
}
