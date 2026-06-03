<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ujian;
use Illuminate\Http\Request;

class UjianController extends Controller
{
    // GET /api/admin/ujian
    public function index(Request $request)
    {
        $query = Ujian::with(['kelas.mataKuliah', 'dosen', 'semester']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->semester_id) {
            $query->where('semester_id', $request->semester_id);
        }

        $ujian = $query->orderBy('mulai_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $ujian,
        ]);
    }

    // GET /api/admin/ujian/{id}
    public function show($id)
    {
        $ujian = Ujian::with(['kelas.mataKuliah', 'dosen', 'semester', 'logUjian.mahasiswa'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $ujian,
        ]);
    }

    // PUT /api/admin/ujian/{id}/batalkan
    public function batalkan(Request $request, $id)
    {
        $ujian = Ujian::findOrFail($id);

        $ujian->update(['status' => 'Dibatalkan']);

        return response()->json([
            'status'  => true,
            'message' => 'Ujian berhasil dibatalkan.',
            'data'    => $ujian,
        ]);
    }
}
