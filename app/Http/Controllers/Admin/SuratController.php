<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Surat;
use Illuminate\Http\Request;

class SuratController extends Controller
{
    // GET /api/admin/surat
    public function index(Request $request)
    {
        $query = Surat::with(['mahasiswa', 'diprosesOleh']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('no_pengajuan', 'like', "%{$request->search}%")
                  ->orWhereHas('mahasiswa', fn($q2) =>
                      $q2->where('nama', 'like', "%{$request->search}%")
                         ->orWhere('nim', 'like', "%{$request->search}%")
                  );
            });
        }

        $surat = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $surat,
        ]);
    }

    // GET /api/admin/surat/{id}
    public function show($id)
    {
        $surat = Surat::with(['mahasiswa.programStudi', 'diprosesOleh'])
            ->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $surat,
        ]);
    }

    // PUT /api/admin/surat/{id}/proses
    public function proses(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        if ($surat->status !== 'Menunggu') {
            return response()->json([
                'status'  => false,
                'message' => 'Hanya surat berstatus Menunggu yang bisa diproses.',
            ], 422);
        }

        $surat->update([
            'status'        => 'Diproses',
            'diproses_oleh' => $request->user()->admin?->id,
            'diproses_at'   => now(),
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Surat sedang diproses.',
            'data'    => $surat->load(['mahasiswa', 'diprosesOleh']),
        ]);
    }

    // PUT /api/admin/surat/{id}/selesai
    public function selesai(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        $request->validate([
            'catatan' => 'nullable|string',
        ]);

        $surat->update([
            'status'  => 'Selesai',
            'catatan' => $request->catatan,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Surat selesai diproses.',
            'data'    => $surat->load(['mahasiswa', 'diprosesOleh']),
        ]);
    }

    // PUT /api/admin/surat/{id}/tolak
    public function tolak(Request $request, $id)
    {
        $surat = Surat::findOrFail($id);

        $request->validate([
            'catatan' => 'required|string|min:5',
        ]);

        $surat->update([
            'status'        => 'Ditolak',
            'diproses_oleh' => $request->user()->admin?->id,
            'diproses_at'   => now(),
            'catatan'       => $request->catatan,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Surat ditolak.',
            'data'    => $surat->load(['mahasiswa', 'diprosesOleh']),
        ]);
    }
}
