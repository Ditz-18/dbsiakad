<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class KrsController extends Controller
{
    // GET /api/mahasiswa/krs
    public function index(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        $krs = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', optional($semesterAktif)->id)
            ->with(['kelas.mataKuliah', 'kelas.dosen', 'semester'])
            ->get();

        $totalSks = $krs->where('status', 'Disetujui')
            ->sum(fn($k) => $k->kelas->mataKuliah->sks ?? 0);

        return response()->json([
            'status' => true,
            'data'   => [
                'semester'  => $semesterAktif,
                'krs'       => $krs,
                'total_sks' => $totalSks,
            ],
        ]);
    }

    // GET /api/mahasiswa/krs/katalog  — daftar kelas tersedia untuk diambil
    public function katalog(Request $request)
    {
        $mahasiswa     = $request->user()->mahasiswa;
        $semesterAktif = Semester::aktif()->first();

        if (!$semesterAktif) {
            return response()->json([
                'status'  => false,
                'message' => 'Tidak ada semester aktif.',
            ], 422);
        }

        // Cek periode KRS
        $krsOpen = now()->between($semesterAktif->krs_buka, $semesterAktif->krs_tutup);

        // ID kelas yang sudah diambil mahasiswa ini semester ini
        $kelasdiambil = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('semester_id', $semesterAktif->id)
            ->pluck('kelas_id');

        // Ambil semua kelas aktif semester ini
        $kelas = Kelas::where('semester_id', $semesterAktif->id)
            ->where('is_active', true)
            ->with(['mataKuliah.programStudi', 'dosen'])
            ->get()
            ->map(function ($k) use ($kelasdiambil) {
                $terisi = Krs::where('kelas_id', $k->id)
                    ->where('status', 'Disetujui')
                    ->count();
                return [
                    'id'           => $k->id,
                    'kode_kelas'   => $k->kode_kelas,
                    'mata_kuliah'  => $k->mataKuliah->nama,
                    'kode_mk'      => $k->mataKuliah->kode,
                    'sks'          => $k->mataKuliah->sks,
                    'semester_anjuran' => $k->mataKuliah->semester_anjuran,
                    'program_studi'=> $k->mataKuliah->programStudi->nama,
                    'dosen'        => $k->dosen->nama,
                    'hari'         => $k->hari,
                    'jam_mulai'    => $k->jam_mulai,
                    'jam_selesai'  => $k->jam_selesai,
                    'ruangan'      => $k->ruangan,
                    'kuota'        => $k->kuota,
                    'terisi'       => $terisi,
                    'sisa_kuota'   => max(0, $k->kuota - $terisi),
                    'sudah_diambil'=> $kelasdiambil->contains($k->id),
                ];
            });

        return response()->json([
            'status' => true,
            'data'   => [
                'semester'  => $semesterAktif,
                'krs_open'  => $krsOpen,
                'katalog'   => $kelas,
            ],
        ]);
    }

    // POST /api/mahasiswa/krs
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

        // Cek periode KRS
        if (now() < $semesterAktif->krs_buka || now() > $semesterAktif->krs_tutup) {
            return response()->json([
                'status'  => false,
                'message' => 'Periode pengisian KRS sudah tutup.',
            ], 422);
        }

        $request->validate([
            'kelas_id' => 'required|exists:kelas,id',
        ]);

        // Cek sudah pernah ambil kelas ini
        $exists = Krs::where('mahasiswa_id', $mahasiswa->id)
            ->where('kelas_id', $request->kelas_id)
            ->where('semester_id', $semesterAktif->id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Kelas ini sudah ada di KRS Anda.',
            ], 422);
        }

        // Cek kuota
        $kelas  = Kelas::findOrFail($request->kelas_id);
        $terisi = Krs::where('kelas_id', $request->kelas_id)
            ->where('status', 'Disetujui')
            ->count();

        if ($terisi >= $kelas->kuota) {
            return response()->json([
                'status'  => false,
                'message' => 'Kuota kelas sudah penuh.',
            ], 422);
        }

        $krs = Krs::create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_id'     => $request->kelas_id,
            'semester_id'  => $semesterAktif->id,
            'status'       => 'Menunggu',
            'diajukan_at'  => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Kelas berhasil ditambahkan ke KRS.',
            'data'    => $krs->load(['kelas.mataKuliah', 'kelas.dosen']),
        ], 201);
    }

    // DELETE /api/mahasiswa/krs/{id}
    public function destroy(Request $request, $id)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $krs = Krs::where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->firstOrFail();

        if (!in_array($krs->status, ['Draft', 'Menunggu', 'Ditolak'])) {
            return response()->json([
                'status'  => false,
                'message' => 'KRS yang sudah diajukan tidak dapat dihapus.',
            ], 422);
        }

        $krs->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Kelas berhasil dihapus dari KRS.',
        ]);
    }
}
