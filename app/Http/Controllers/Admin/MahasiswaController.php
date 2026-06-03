<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MahasiswaController extends Controller
{
    // GET /api/admin/mahasiswa
    public function index(Request $request)
    {
        $query = Mahasiswa::with(['user', 'programStudi', 'dosenPa']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nim', 'like', "%{$request->search}%")
                  ->orWhere('nama', 'like', "%{$request->search}%");
            });
        }

        if ($request->program_studi_id) {
            $query->where('program_studi_id', $request->program_studi_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->angkatan) {
            $query->where('angkatan', $request->angkatan);
        }

        $mahasiswa = $query->orderBy('nama')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $mahasiswa,
        ]);
    }

    // GET /api/admin/mahasiswa/{id}
    public function show($id)
    {
        $mahasiswa = Mahasiswa::with(['user', 'programStudi', 'dosenPa'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $mahasiswa,
        ]);
    }

    // POST /api/admin/mahasiswa
    public function store(Request $request)
    {
        $request->validate([
            'username'         => 'required|string|unique:users,username',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:8',
            'nim'              => 'required|string|unique:mahasiswa,nim',
            'nama'             => 'required|string',
            'program_studi_id' => 'required|exists:program_studi,id',
            'angkatan'         => 'required|digits:4',
            'dosen_pa_id'      => 'nullable|exists:dosen,id',
            'no_hp'            => 'nullable|string',
            'alamat'           => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username'  => $request->username,
                'email'     => $request->email,
                'password'  => $request->password,
                'role'      => 'mahasiswa',
                'is_active' => true,
            ]);

            $mahasiswa = Mahasiswa::create([
                'user_id'          => $user->id,
                'nim'              => $request->nim,
                'nama'             => $request->nama,
                'program_studi_id' => $request->program_studi_id,
                'angkatan'         => $request->angkatan,
                'semester'         => 1,
                'status'           => 'Aktif',
                'dosen_pa_id'      => $request->dosen_pa_id,
                'email'            => $request->email,
                'no_hp'            => $request->no_hp,
                'alamat'           => $request->alamat,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Mahasiswa berhasil ditambahkan.',
                'data'    => $mahasiswa->load(['user', 'programStudi']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menambahkan mahasiswa.',
            ], 500);
        }
    }

    // PUT /api/admin/mahasiswa/{id}
    public function update(Request $request, $id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);

        $request->validate([
            'nama'             => 'sometimes|string',
            'program_studi_id' => 'sometimes|exists:program_studi,id',
            'angkatan'         => 'sometimes|digits:4',
            'semester'         => 'sometimes|integer|min:1|max:14',
            'status'           => 'sometimes|in:Aktif,Cuti,Lulus,Dropout',
            'dosen_pa_id'      => 'nullable|exists:dosen,id',
            'no_hp'            => 'nullable|string',
            'alamat'           => 'nullable|string',
        ]);

        $mahasiswa->update($request->only([
            'nama', 'program_studi_id', 'angkatan', 'semester',
            'status', 'dosen_pa_id', 'no_hp', 'alamat',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Data mahasiswa berhasil diperbarui.',
            'data'    => $mahasiswa->load(['user', 'programStudi']),
        ]);
    }

    // DELETE /api/admin/mahasiswa/{id}
    public function destroy($id)
    {
        $mahasiswa = Mahasiswa::findOrFail($id);
        $mahasiswa->user()->delete(); // cascade delete user

        return response()->json([
            'status'  => true,
            'message' => 'Mahasiswa berhasil dihapus.',
        ]);
    }
}
