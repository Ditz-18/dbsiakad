<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Kelas;
use App\Models\Krs;
use App\Models\Semester;
use Illuminate\Http\Request;

class KelasController extends Controller
{
    // GET /api/admin/kelas
    public function index(Request $request)
    {
        $semesterAktif = Semester::aktif()->first();
        $semesterId    = $request->semester_id ?? optional($semesterAktif)->id;

        $query = Kelas::with(['mataKuliah.programStudi', 'dosen', 'semester'])
            ->where('semester_id', $semesterId);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kode_kelas', 'like', "%{$request->search}%")
                  ->orWhereHas('mataKuliah', fn($q2) =>
                      $q2->where('nama', 'like', "%{$request->search}%")
                         ->orWhere('kode', 'like', "%{$request->search}%")
                  );
            });
        }

        if ($request->mata_kuliah_id) {
            $query->where('mata_kuliah_id', $request->mata_kuliah_id);
        }

        if ($request->dosen_id) {
            $query->where('dosen_id', $request->dosen_id);
        }

        $kelas = $query->orderBy('hari')->orderBy('jam_mulai')
            ->paginate($request->per_page ?? 50);

        // Tambah info terisi per kelas
        $kelas->getCollection()->transform(function ($k) {
            $k->terisi = Krs::where('kelas_id', $k->id)
                ->where('status', 'Disetujui')->count();
            return $k;
        });

        return response()->json([
            'status' => true,
            'data'   => $kelas,
        ]);
    }

    // GET /api/admin/kelas/{id}
    public function show($id)
    {
        $kelas = Kelas::with(['mataKuliah.programStudi', 'dosen', 'semester'])
            ->findOrFail($id);

        $kelas->terisi = Krs::where('kelas_id', $kelas->id)
            ->where('status', 'Disetujui')->count();

        return response()->json([
            'status' => true,
            'data'   => $kelas,
        ]);
    }

    // POST /api/admin/kelas
    public function store(Request $request)
    {
        $request->validate([
            'kode_kelas'     => 'required|string',
            'mata_kuliah_id' => 'required|exists:mata_kuliah,id',
            'dosen_id'       => 'required|exists:dosen,id',
            'semester_id'    => 'required|exists:semester,id',
            'ruangan'        => 'nullable|string',
            'hari'           => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'      => 'required',
            'jam_selesai'    => 'required|after:jam_mulai',
            'kuota'          => 'required|integer|min:1',
        ]);

        // Cegah duplikat kode_kelas dalam semester yang sama
        $exists = Kelas::where('kode_kelas', $request->kode_kelas)
            ->where('semester_id', $request->semester_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Kode kelas sudah digunakan pada semester ini.',
            ], 422);
        }

        $kelas = Kelas::create([
            'kode_kelas'     => $request->kode_kelas,
            'mata_kuliah_id' => $request->mata_kuliah_id,
            'dosen_id'       => $request->dosen_id,
            'semester_id'    => $request->semester_id,
            'ruangan'        => $request->ruangan,
            'hari'           => $request->hari,
            'jam_mulai'      => $request->jam_mulai,
            'jam_selesai'    => $request->jam_selesai,
            'kuota'          => $request->kuota,
            'is_active'      => true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Kelas berhasil ditambahkan.',
            'data'    => $kelas->load(['mataKuliah', 'dosen', 'semester']),
        ], 201);
    }

    // PUT /api/admin/kelas/{id}
    public function update(Request $request, $id)
    {
        $kelas = Kelas::findOrFail($id);

        $request->validate([
            'kode_kelas'     => 'sometimes|string',
            'mata_kuliah_id' => 'sometimes|exists:mata_kuliah,id',
            'dosen_id'       => 'sometimes|exists:dosen,id',
            'ruangan'        => 'nullable|string',
            'hari'           => 'sometimes|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu',
            'jam_mulai'      => 'sometimes',
            'jam_selesai'    => 'sometimes',
            'kuota'          => 'sometimes|integer|min:1',
            'is_active'      => 'sometimes|boolean',
        ]);

        $kelas->update($request->only([
            'kode_kelas', 'mata_kuliah_id', 'dosen_id',
            'ruangan', 'hari', 'jam_mulai', 'jam_selesai',
            'kuota', 'is_active',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Kelas berhasil diperbarui.',
            'data'    => $kelas->load(['mataKuliah', 'dosen', 'semester']),
        ]);
    }

    // DELETE /api/admin/kelas/{id}
    public function destroy($id)
    {
        $kelas = Kelas::findOrFail($id);

        $adaKrs = Krs::where('kelas_id', $id)->exists();
        if ($adaKrs) {
            return response()->json([
                'status'  => false,
                'message' => 'Kelas tidak dapat dihapus karena sudah memiliki data KRS.',
            ], 422);
        }

        $kelas->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Kelas berhasil dihapus.',
        ]);
    }
}
