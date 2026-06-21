<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\SoalUjian;
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
            ->withCount('soal')
            ->orderBy('mulai_at')
            ->get();

        return response()->json(['status' => true, 'data' => $ujian]);
    }

    // POST /api/dosen/ujian
    public function store(Request $request)
    {
        $dosen = $request->user()->dosen;

        $request->validate([
            'nama'            => 'required|string',
            'kelas_id'        => 'required|exists:kelas,id',
            'tipe'            => 'required|in:Kuis,UTS,UAS',
            'durasi'          => 'required|integer|min:10',
            'mulai_at'        => 'required|date',
            'selesai_at'      => 'required|date|after:mulai_at',
            'max_pelanggaran' => 'required|integer|min:1',
        ]);

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
        $ujian = Ujian::where('id', $id)->where('dosen_id', $dosen->id)->firstOrFail();

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
            'nama','tipe','durasi','mulai_at','selesai_at','status','max_pelanggaran',
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

        return response()->json(['status' => true, 'message' => 'Ujian berhasil dihapus.']);
    }

    // GET /api/dosen/ujian/{id}/soal
    public function indexSoal(Request $request, $id)
    {
        $dosen = $request->user()->dosen;
        Ujian::where('id', $id)->where('dosen_id', $dosen->id)->firstOrFail();

        $soal = SoalUjian::where('ujian_id', $id)->orderBy('nomor')->get();

        return response()->json(['status' => true, 'data' => $soal]);
    }

    // POST /api/dosen/ujian/{id}/soal
    public function storeSoal(Request $request, $id)
    {
        $dosen = $request->user()->dosen;
        Ujian::where('id', $id)->where('dosen_id', $dosen->id)->firstOrFail();

        $request->validate([
            'pertanyaan'    => 'required|string',
            'tipe'          => 'required|in:pilihan_ganda,essay',
            'pilihan'       => 'required_if:tipe,pilihan_ganda|array',
            'jawaban_benar' => 'required_if:tipe,pilihan_ganda|string',
            'bobot'         => 'integer|min:1',
        ]);

        $nomorTerakhir = SoalUjian::where('ujian_id', $id)->max('nomor') ?? 0;

        $soal = SoalUjian::create([
            'ujian_id'      => $id,
            'nomor'         => $nomorTerakhir + 1,
            'pertanyaan'    => $request->pertanyaan,
            'tipe'          => $request->tipe,
            'pilihan'       => $request->pilihan,
            'jawaban_benar' => $request->jawaban_benar,
            'bobot'         => $request->bobot ?? 1,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Soal berhasil ditambahkan.',
            'data'    => $soal,
        ], 201);
    }

    // PUT /api/dosen/ujian/{ujianId}/soal/{soalId}
    public function updateSoal(Request $request, $ujianId, $soalId)
    {
        $dosen = $request->user()->dosen;
        Ujian::where('id', $ujianId)->where('dosen_id', $dosen->id)->firstOrFail();

        $soal = SoalUjian::where('id', $soalId)->where('ujian_id', $ujianId)->firstOrFail();

        $soal->update($request->only([
            'pertanyaan','tipe','pilihan','jawaban_benar','bobot',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Soal berhasil diperbarui.',
            'data'    => $soal,
        ]);
    }

    // DELETE /api/dosen/ujian/{ujianId}/soal/{soalId}
    public function destroySoal(Request $request, $ujianId, $soalId)
    {
        $dosen = $request->user()->dosen;
        Ujian::where('id', $ujianId)->where('dosen_id', $dosen->id)->firstOrFail();

        $soal = SoalUjian::where('id', $soalId)->where('ujian_id', $ujianId)->firstOrFail();
        $soal->delete();

        // Re-nomor soal yang tersisa
        SoalUjian::where('ujian_id', $ujianId)
            ->orderBy('nomor')
            ->get()
            ->each(function ($s, $idx) {
                $s->update(['nomor' => $idx + 1]);
            });

        return response()->json(['status' => true, 'message' => 'Soal berhasil dihapus.']);
    }

    // GET /api/dosen/ujian/{id}/hasil
    public function hasil(Request $request, $id)
    {
        $dosen = $request->user()->dosen;
        $ujian = \App\Models\Ujian::where('id', $id)->where('dosen_id', $dosen->id)->firstOrFail();

        $sesi = \App\Models\SesiUjian::where('ujian_id', $id)
            ->with('mahasiswa')
            ->get()
            ->map(fn($s) => [
                'nim'         => $s->mahasiswa->nim,
                'nama'        => $s->mahasiswa->nama,
                'nilai'       => $s->nilai,
                'status'      => $s->status,
                'pelanggaran' => $s->pelanggaran,
                'selesai_at'  => $s->selesai_at,
            ]);

        return response()->json([
            'status' => true,
            'data'   => [
                'ujian' => $ujian->only(['id', 'nama']),
                'hasil' => $sesi,
            ],
        ]);
    }
}
