<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use Illuminate\Http\Request;

class PembayaranController extends Controller
{
    // GET /api/admin/pembayaran
    public function index(Request $request)
    {
        $query = Pembayaran::with(['mahasiswa', 'semester', 'dikonfirmasiOleh']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->semester_id) {
            $query->where('semester_id', $request->semester_id);
        }

        if ($request->search) {
            $query->whereHas('mahasiswa', function ($q) use ($request) {
                $q->where('nim', 'like', "%{$request->search}%")
                  ->orWhere('nama', 'like', "%{$request->search}%");
            });
        }

        $pembayaran = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $pembayaran,
        ]);
    }

    // PUT /api/admin/pembayaran/{id}/konfirmasi
    public function konfirmasi(Request $request, $id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        $request->validate([
            'no_referensi' => 'nullable|string',
        ]);

        $pembayaran->update([
            'status'            => 'Lunas',
            'tanggal_bayar'     => now(),
            'no_referensi'      => $request->no_referensi,
            'dikonfirmasi_oleh' => $request->user()->admin->id,
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Pembayaran berhasil dikonfirmasi.',
            'data'    => $pembayaran->load(['mahasiswa', 'semester']),
        ]);
    }
}
