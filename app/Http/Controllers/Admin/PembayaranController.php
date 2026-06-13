<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Mahasiswa;
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

        $pembayaran = $query->orderBy('created_at', 'desc')
            ->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $pembayaran,
        ]);
    }

    // POST /api/admin/pembayaran  (tambah tagihan UKT)
    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswa,id',
            'semester_id'  => 'required|exists:semester,id',
            'nominal'      => 'required|integer|min:1',
        ]);

        // Cegah duplikat tagihan per mahasiswa per semester
        $exists = Pembayaran::where('mahasiswa_id', $request->mahasiswa_id)
            ->where('semester_id', $request->semester_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status'  => false,
                'message' => 'Tagihan untuk mahasiswa dan semester ini sudah ada.',
            ], 422);
        }

        $pembayaran = Pembayaran::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'semester_id'  => $request->semester_id,
            'nominal'      => $request->nominal,
            'status'       => 'Menunggak',
        ]);

        return response()->json([
            'status'  => true,
            'message' => 'Tagihan berhasil ditambahkan.',
            'data'    => $pembayaran->load(['mahasiswa', 'semester']),
        ], 201);
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

    // DELETE /api/admin/pembayaran/{id}
    public function destroy($id)
    {
        $pembayaran = Pembayaran::findOrFail($id);

        // Jangan hapus yang sudah lunas untuk menjaga history
        if ($pembayaran->status === 'Lunas') {
            return response()->json([
                'status'  => false,
                'message' => 'Tagihan yang sudah lunas tidak dapat dihapus.',
            ], 422);
        }

        $pembayaran->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Tagihan berhasil dihapus.',
        ]);
    }
}
