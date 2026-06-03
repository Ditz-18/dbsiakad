<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\Pengumuman;
use Illuminate\Http\Request;

class PengumumanController extends Controller
{
    // GET /api/mahasiswa/pengumuman
    public function index(Request $request)
    {
        $query = Pengumuman::aktif()->untuk('Mahasiswa');

        if ($request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        $pengumuman = $query->orderBy('penting', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 10);

        return response()->json([
            'status' => true,
            'data'   => $pengumuman,
        ]);
    }

    // GET /api/mahasiswa/pengumuman/{id}
    public function show($id)
    {
        $pengumuman = Pengumuman::aktif()->findOrFail($id);

        // Tandai sudah dibaca
        $pengumuman->update(['baru' => false]);

        return response()->json([
            'status' => true,
            'data'   => $pengumuman,
        ]);
    }
}
