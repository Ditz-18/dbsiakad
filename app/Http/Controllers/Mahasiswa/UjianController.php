<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use App\Models\SoalUjian;
use App\Models\JawabanUjian;
use App\Models\SesiUjian;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UjianController extends Controller
{
    // GET /api/mahasiswa/ujian
    public function index(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        $kelasIds = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->where('status', 'Disetujui')
            ->pluck('kelas_id');

        $ujian = Ujian::whereIn('kelas_id', $kelasIds)
            ->with(['kelas.mataKuliah', 'dosen'])
            ->orderBy('mulai_at')
            ->get()
            ->map(function ($u) use ($mahasiswa) {
                $sesi = SesiUjian::where('ujian_id', $u->id)
                    ->where('mahasiswa_id', $mahasiswa->id)
                    ->first();
                $u->sesi = $sesi;
                return $u;
            });

        return response()->json([
            'status' => true,
            'data'   => $ujian,
        ]);
    }

    // GET /api/mahasiswa/ujian/{id}  — detail + cek akses
    public function show(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;
        $ujian     = Ujian::with(['kelas.mataKuliah', 'dosen'])->findOrFail($id);

        $terdaftar = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('kelas_id', $ujian->kelas_id)
            ->where('status', 'Disetujui')
            ->exists();

        if (!$terdaftar) {
            return response()->json([
                'status'  => false,
                'message' => 'Anda tidak terdaftar di kelas ujian ini.',
            ], 403);
        }

        $sesi = SesiUjian::where('ujian_id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        return response()->json([
            'status' => true,
            'data'   => array_merge($ujian->toArray(), ['sesi' => $sesi]),
        ]);
    }

    // GET /api/mahasiswa/ujian/{id}/soal  — ambil soal (tanpa jawaban_benar)
    public function soal(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;
        $ujian     = Ujian::findOrFail($id);

        // Cek akses
        $terdaftar = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('kelas_id', $ujian->kelas_id)
            ->where('status', 'Disetujui')
            ->exists();

        if (!$terdaftar) {
            return response()->json(['status' => false, 'message' => 'Akses ditolak.'], 403);
        }

        // Ujian harus Berlangsung atau mahasiswa punya sesi aktif
        $sesi = SesiUjian::where('ujian_id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->first();

        if ($ujian->status !== 'Berlangsung' && !$sesi) {
            return response()->json([
                'status'  => false,
                'message' => 'Ujian belum dimulai.',
            ], 403);
        }

        // Buat sesi jika belum ada
        if (!$sesi) {
            $sesi = SesiUjian::create([
                'ujian_id'     => $id,
                'mahasiswa_id' => $mahasiswa->id,
                'mulai_at'     => now(),
                'status'       => 'Berlangsung',
            ]);
        }

        // Ambil soal — sembunyikan jawaban_benar
        $soal = SoalUjian::where('ujian_id', $id)
            ->orderBy('nomor')
            ->get()
            ->makeHidden(['jawaban_benar']);

        // Gabungkan dengan jawaban yang sudah diisi
        $jawabanSudah = JawabanUjian::where('ujian_id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->get()
            ->keyBy('soal_id');

        $soalDenganJawaban = $soal->map(function ($s) use ($jawabanSudah) {
            $j = $jawabanSudah->get($s->id);
            $s->jawaban_saya = $j?->jawaban;
            $s->ragu         = $j?->ragu ?? false;
            return $s;
        });

        // Hitung sisa waktu
        $sisaDetik = null;
        if ($sesi->mulai_at) {
            $batasSelesai = $sesi->mulai_at->addMinutes($ujian->durasi);
            $sisaDetik    = max(0, now()->diffInSeconds($batasSelesai, false));
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'ujian'      => $ujian->only(['id','nama','tipe','durasi','max_pelanggaran']),
                'sesi'       => $sesi,
                'sisa_detik' => $sisaDetik,
                'soal'       => $soalDenganJawaban,
                'total_soal' => $soal->count(),
            ],
        ]);
    }

    // POST /api/mahasiswa/ujian/{id}/jawab  — simpan satu jawaban (auto-save)
    public function jawab(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $request->validate([
            'soal_id' => 'required|exists:soal_ujian,id',
            'jawaban' => 'nullable|string',
            'ragu'    => 'boolean',
        ]);

        $sesi = SesiUjian::where('ujian_id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'Berlangsung')
            ->firstOrFail();

        JawabanUjian::updateOrCreate(
            [
                'ujian_id'     => $id,
                'mahasiswa_id' => $mahasiswa->id,
                'soal_id'      => $request->soal_id,
            ],
            [
                'jawaban' => $request->jawaban,
                'ragu'    => $request->ragu ?? false,
            ]
        );

        return response()->json(['status' => true, 'message' => 'Jawaban disimpan.']);
    }

    // POST /api/mahasiswa/ujian/{id}/submit  — kumpulkan ujian
    public function submit(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $sesi = SesiUjian::where('ujian_id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'Berlangsung')
            ->firstOrFail();

        $ujian = Ujian::with('soal')->findOrFail($id);

        // Hitung nilai otomatis (hanya untuk pilihan ganda)
        $totalBobot  = 0;
        $bobotBenar  = 0;

        foreach ($ujian->soal as $soal) {
            if ($soal->tipe !== 'pilihan_ganda') continue;

            $totalBobot += $soal->bobot;

            $jawaban = JawabanUjian::where('ujian_id', $id)
                ->where('mahasiswa_id', $mahasiswa->id)
                ->where('soal_id', $soal->id)
                ->first();

            if ($jawaban && $jawaban->jawaban === $soal->jawaban_benar) {
                $bobotBenar += $soal->bobot;
            }
        }

        $nilai = $totalBobot > 0 ? round(($bobotBenar / $totalBobot) * 100) : 0;

        $sesi->update([
            'selesai_at' => now(),
            'nilai'      => $nilai,
            'status'     => 'Selesai',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Ujian berhasil dikumpulkan.',
            'data'    => [
                'nilai'      => $nilai,
                'benar'      => $bobotBenar,
                'total'      => $totalBobot,
                'selesai_at' => $sesi->selesai_at,
            ],
        ]);
    }

    // POST /api/mahasiswa/ujian/{id}/pelanggaran  — catat pelanggaran
    public function pelanggaran(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $request->validate([
            'deskripsi' => 'required|string',
        ]);

        $sesi = SesiUjian::where('ujian_id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'Berlangsung')
            ->firstOrFail();

        $ujian = Ujian::findOrFail($id);

        $sesi->increment('pelanggaran');

        // Log pelanggaran
        \App\Models\LogUjian::create([
            'ujian_id'     => $id,
            'mahasiswa_id' => $mahasiswa->id,
            'no_pelanggaran' => $sesi->pelanggaran,
            'deskripsi'    => $request->deskripsi,
            'dibatalkan'   => false,
            'terjadi_at'   => now(),
        ]);

        // Auto-submit jika melebihi batas
        if ($sesi->pelanggaran >= $ujian->max_pelanggaran) {
            $sesi->update(['status' => 'Dibatalkan', 'selesai_at' => now()]);

            return response()->json([
                'status'     => true,
                'dibatalkan' => true,
                'message'    => 'Ujian dibatalkan karena melebihi batas pelanggaran.',
            ]);
        }

        return response()->json([
            'status'      => true,
            'dibatalkan'  => false,
            'pelanggaran' => $sesi->pelanggaran,
            'sisa_izin'   => $ujian->max_pelanggaran - $sesi->pelanggaran,
        ]);
    }
}
