<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    // GET /api/admin/pengumuman
    public function index(Request $request)
    {
        $query = Pengumuman::with('dibuatOleh');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        $pengumuman = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $pengumuman,
        ]);
    }

    // GET /api/admin/pengumuman/{id}
    public function show($id)
    {
        $pengumuman = Pengumuman::with('dibuatOleh')->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $pengumuman,
        ]);
    }

    // POST /api/admin/pengumuman
    public function store(Request $request)
    {
        $request->validate([
            'judul'    => 'required|string',
            'isi'      => 'required|string',
            'kategori' => 'required|in:Akademik,Keuangan,Umum',
            'target'   => 'required|in:Semua,Mahasiswa,Dosen',
            'penting'  => 'boolean',
            'status'   => 'required|in:Aktif,Arsip',
        ]);

        $pengumuman = Pengumuman::create([
            'judul'      => $request->judul,
            'isi'        => $request->isi,
            'kategori'   => $request->kategori,
            'target'     => $request->target,
            'penting'    => $request->penting ?? false,
            'baru'       => true,
            'status'     => $request->status,
            'dibuat_oleh'=> $request->user()->admin->id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Pengumuman berhasil dibuat.',
            'data'    => $pengumuman->load('dibuatOleh'),
        ], 201);
    }

    // PUT /api/admin/pengumuman/{id}
    public function update(Request $request, $id)
    {
        $pengumuman = Pengumuman::findOrFail($id);

        $request->validate([
            'judul'    => 'sometimes|string',
            'isi'      => 'sometimes|string',
            'kategori' => 'sometimes|in:Akademik,Keuangan,Umum',
            'target'   => 'sometimes|in:Semua,Mahasiswa,Dosen',
            'penting'  => 'sometimes|boolean',
            'status'   => 'sometimes|in:Aktif,Arsip',
        ]);

        $pengumuman->update($request->only([
            'judul', 'isi', 'kategori', 'target', 'penting', 'status',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Pengumuman berhasil diperbarui.',
            'data'    => $pengumuman->load('dibuatOleh'),
        ]);
    }

    // DELETE /api/admin/pengumuman/{id}
    public function destroy($id)
    {
        $pengumuman = Pengumuman::findOrFail($id);
        $pengumuman->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Pengumuman berhasil dihapus.',
        ]);
    }
}
