<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\LogUjian;
use App\Models\Kelas;
use App\Models\Semester;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    // GET /api/dosen/ujian
    public function index(Request $request)
    {
        $dosen         = $request->user()->dosen;
        $semesterAktif = Semester::aktif()->first();

        $ujian = Ujian::where('dosen_id', $dosen->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['kelas.mataKuliah', 'semester'])
            ->orderBy('mulai_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $ujian,
        ]);
    }

    // POST /api/dosen/ujian
    public function store(Request $request)
    {
        $dosen = $request->user()->dosen;

        $request->validate([
            'nama'             => 'required|string',
            'kelas_id'         => 'required|exists:kelas,id',
            'tipe'             => 'required|in:Kuis,UTS,UAS',
            'durasi'           => 'required|integer|min:10',
            'mulai_at'         => 'required|date',
            'selesai_at'       => 'required|date|after:mulai_at',
            'max_pelanggaran'  => 'required|integer|min:1',
        ]);

        // Pastikan kelas milik dosen ini
        $kelas = Kelas::where('id', $request->kelas_id)
            ->where('dosen_id', $dosen->id)
            ->firstOrFail();

        $ujian = Ujian::create([
            'nama'            => $request->nama,
            'kelas_id'        => $request->kelas_id,
            'dosen_id'        => $dosen->id,
            'semester_id'     => $kelas->semester_id,
            'tipe'            => $request->tipe,
            'durasi'          => $request->durasi,
            'mulai_at'        => $request->mulai_at,
            'selesai_at'      => $request->selesai_at,
            'status'          => 'Draft',
            'max_pelanggaran' => $request->max_pelanggaran,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Ujian berhasil dibuat.',
            'data'    => $ujian->load(['kelas.mataKuliah']),
        ], 201);
    }

    // PUT /api/dosen/ujian/{id}
    public function update(Request $request, $id)
    {
        $dosen = $request->user()->dosen;

        $ujian = Ujian::where('id', $id)
            ->where('dosen_id', $dosen->id)
            ->firstOrFail();

        $request->validate([
            'nama'            => 'sometimes|string',
            'tipe'            => 'sometimes|in:Kuis,UTS,UAS',
            'durasi'          => 'sometimes|integer|min:10',
            'mulai_at'        => 'sometimes|date',
            'selesai_at'      => 'sometimes|date',
            'status'          => 'sometimes|in:Draft,Berlangsung,Selesai,Dibatalkan',
            'max_pelanggaran' => 'sometimes|integer|min:1',
        ]);

        $ujian->update($request->only([
            'nama', 'tipe', 'durasi', 'mulai_at', 'selesai_at', 'status', 'max_pelanggaran',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Ujian berhasil diperbarui.',
            'data'    => $ujian->load(['kelas.mataKuliah']),
        ]);
    }

    // DELETE /api/dosen/ujian/{id}
    public function destroy(Request $request, $id)
    {
        $dosen = $request->user()->dosen;

        $ujian = Ujian::where('id', $id)
            ->where('dosen_id', $dosen->id)
            ->where('status', 'Draft')
            ->firstOrFail();

        $ujian->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Ujian berhasil dihapus.',
        ]);
    }
}
