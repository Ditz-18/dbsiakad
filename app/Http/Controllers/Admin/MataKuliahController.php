<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MataKuliah;
use Illuminate\Http\Request;

class MataKuliahController extends Controller
{
    // GET /api/admin/mata-kuliah
    public function index(Request $request)
    {
        $query = MataKuliah::with('programStudi');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kode', 'like', "%{$request->search}%")
                  ->orWhere('nama', 'like', "%{$request->search}%");
            });
        }

        if ($request->program_studi_id) {
            $query->where('program_studi_id', $request->program_studi_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $mataKuliah = $query->orderBy('nama')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $mataKuliah,
        ]);
    }

    // GET /api/admin/mata-kuliah/{id}
    public function show($id)
    {
        $mataKuliah = MataKuliah::with('programStudi')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $mataKuliah,
        ]);
    }

    // POST /api/admin/mata-kuliah
    public function store(Request $request)
    {
        $request->validate([
            'kode'             => 'required|string|unique:mata_kuliah,kode',
            'nama'             => 'required|string',
            'sks'              => 'required|integer|min:1|max:6',
            'semester_anjuran' => 'required|integer|min:1|max:14',
            'program_studi_id' => 'required|exists:program_studi,id',
            'status'           => 'required|in:Aktif,Nonaktif',
        ]);

        $mataKuliah = MataKuliah::create($request->only([
            'kode', 'nama', 'sks', 'semester_anjuran', 'program_studi_id', 'status',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Mata kuliah berhasil ditambahkan.',
            'data'    => $mataKuliah->load('programStudi'),
        ], 201);
    }

    // PUT /api/admin/mata-kuliah/{id}
    public function update(Request $request, $id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);

        $request->validate([
            'kode'             => 'sometimes|string|unique:mata_kuliah,kode,' . $id,
            'nama'             => 'sometimes|string',
            'sks'              => 'sometimes|integer|min:1|max:6',
            'semester_anjuran' => 'sometimes|integer|min:1|max:14',
            'program_studi_id' => 'sometimes|exists:program_studi,id',
            'status'           => 'sometimes|in:Aktif,Nonaktif',
        ]);

        $mataKuliah->update($request->only([
            'kode', 'nama', 'sks', 'semester_anjuran', 'program_studi_id', 'status',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Mata kuliah berhasil diperbarui.',
            'data'    => $mataKuliah->load('programStudi'),
        ]);
    }

    // DELETE /api/admin/mata-kuliah/{id}
    public function destroy($id)
    {
        $mataKuliah = MataKuliah::findOrFail($id);
        $mataKuliah->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Mata kuliah berhasil dihapus.',
        ]);
    }
}
