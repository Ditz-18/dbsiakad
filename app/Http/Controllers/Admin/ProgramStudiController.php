<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ProgramStudi;
use Illuminate\Http\Request;

class ProgramStudiController extends Controller
{
    // GET /api/admin/program-studi
    public function index()
    {
        $prodi = ProgramStudi::where('is_active', true)
            ->orderBy('nama')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $prodi,
        ]);
    }

    // POST /api/admin/program-studi
    public function store(Request $request)
    {
        $request->validate([
            'kode'     => 'required|string|unique:program_studi,kode',
            'nama'     => 'required|string',
            'fakultas' => 'required|string',
            'jenjang'  => 'required|in:D3,S1,S2',
        ]);

        $prodi = ProgramStudi::create([
            'kode'      => strtoupper($request->kode),
            'nama'      => $request->nama,
            'fakultas'  => $request->fakultas,
            'jenjang'   => $request->jenjang,
            'is_active' => true,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Program studi berhasil ditambahkan.',
            'data'    => $prodi,
        ], 201);
    }

    // PUT /api/admin/program-studi/{id}
    public function update(Request $request, $id)
    {
        $prodi = ProgramStudi::findOrFail($id);

        $request->validate([
            'nama'      => 'sometimes|string',
            'fakultas'  => 'sometimes|string',
            'jenjang'   => 'sometimes|in:D3,S1,S2',
            'is_active' => 'sometimes|boolean',
        ]);

        $prodi->update($request->only(['nama', 'fakultas', 'jenjang', 'is_active']));

        return response()->json([
            'status'  => true,
            'message' => 'Program studi berhasil diperbarui.',
            'data'    => $prodi,
        ]);
    }
}
