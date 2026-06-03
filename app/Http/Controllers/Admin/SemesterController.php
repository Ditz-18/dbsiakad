<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Semester;
use Illuminate\Http\Request;

class SemesterController extends Controller
{
    // GET /api/admin/semester
    public function index()
    {
        $semester = Semester::orderBy('created_at', 'desc')->get();

        return response()->json([
            'status' => true,
            'data'   => $semester,
        ]);
    }

    // GET /api/admin/semester/{id}
    public function show($id)
    {
        $semester = Semester::findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $semester,
        ]);
    }

    // POST /api/admin/semester
    public function store(Request $request)
    {
        $request->validate([
            'nama'            => 'required|string',
            'tahun_akademik'  => 'required|string',
            'tipe'            => 'required|in:Ganjil,Genap',
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'krs_buka'        => 'required|date',
            'krs_tutup'       => 'required|date|after:krs_buka',
            'nilai_buka'      => 'required|date',
            'nilai_tutup'     => 'required|date|after:nilai_buka',
            'status'          => 'required|in:Aktif,Arsip',
        ]);

        // Jika status Aktif, nonaktifkan semester lain
        if ($request->status === 'Aktif') {
            Semester::where('status', 'Aktif')->update(['status' => 'Arsip']);
        }

        $semester = Semester::create($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Semester berhasil ditambahkan.',
            'data'    => $semester,
        ], 201);
    }

    // PUT /api/admin/semester/{id}
    public function update(Request $request, $id)
    {
        $semester = Semester::findOrFail($id);

        $request->validate([
            'nama'            => 'sometimes|string',
            'tahun_akademik'  => 'sometimes|string',
            'tipe'            => 'sometimes|in:Ganjil,Genap',
            'tanggal_mulai'   => 'sometimes|date',
            'tanggal_selesai' => 'sometimes|date',
            'krs_buka'        => 'sometimes|date',
            'krs_tutup'       => 'sometimes|date',
            'nilai_buka'      => 'sometimes|date',
            'nilai_tutup'     => 'sometimes|date',
            'status'          => 'sometimes|in:Aktif,Arsip',
        ]);

        // Jika diubah jadi Aktif, nonaktifkan semester lain
        if ($request->status === 'Aktif') {
            Semester::where('status', 'Aktif')->where('id', '!=', $id)->update(['status' => 'Arsip']);
        }

        $semester->update($request->all());

        return response()->json([
            'status'  => true,
            'message' => 'Semester berhasil diperbarui.',
            'data'    => $semester,
        ]);
    }

    // DELETE /api/admin/semester/{id}
    public function destroy($id)
    {
        $semester = Semester::findOrFail($id);
        $semester->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Semester berhasil dihapus.',
        ]);
    }
}
