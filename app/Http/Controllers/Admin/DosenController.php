<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DosenController extends Controller
{
    // GET /api/admin/dosen
    public function index(Request $request)
    {
        $query = Dosen::with(['user', 'programStudi']);

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('nip', 'like', "%{$request->search}%")
                  ->orWhere('nama', 'like', "%{$request->search}%");
            });
        }

        if ($request->program_studi_id) {
            $query->where('program_studi_id', $request->program_studi_id);
        }

        if ($request->is_active !== null) {
            $query->where('is_active', $request->is_active);
        }

        $dosen = $query->orderBy('nama')->paginate($request->per_page ?? 15);

        return response()->json([
            'status' => true,
            'data'   => $dosen,
        ]);
    }

    // GET /api/admin/dosen/{id}
    public function show($id)
    {
        $dosen = Dosen::with(['user', 'programStudi', 'kelas'])->findOrFail($id);

        return response()->json([
            'status' => true,
            'data'   => $dosen,
        ]);
    }

    // POST /api/admin/dosen
    public function store(Request $request)
    {
        $request->validate([
            'username'         => 'required|string|unique:users,username',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|string|min:8',
            'nip'              => 'required|string|unique:dosen,nip',
            'nama'             => 'required|string',
            'program_studi_id' => 'required|exists:program_studi,id',
            'fakultas'         => 'required|string',
            'jabatan'          => 'required|in:Tenaga Pengajar,Asisten Ahli,Lektor,Lektor Kepala,Guru Besar',
            'golongan'         => 'nullable|string',
            'email_akademik'   => 'nullable|email',
            'no_hp'            => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $user = User::create([
                'username'  => $request->username,
                'email'     => $request->email,
                'password'  => $request->password,
                'role'      => 'dosen',
                'is_active' => true,
            ]);

            $dosen = Dosen::create([
                'user_id'          => $user->id,
                'nip'              => $request->nip,
                'nama'             => $request->nama,
                'program_studi_id' => $request->program_studi_id,
                'fakultas'         => $request->fakultas,
                'jabatan'          => $request->jabatan,
                'golongan'         => $request->golongan,
                'email_akademik'   => $request->email_akademik,
                'no_hp'            => $request->no_hp,
                'is_active'        => true,
            ]);

            DB::commit();

            return response()->json([
                'status'  => true,
                'message' => 'Dosen berhasil ditambahkan.',
                'data'    => $dosen->load(['user', 'programStudi']),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status'  => false,
                'message' => 'Gagal menambahkan dosen.',
            ], 500);
        }
    }

    // PUT /api/admin/dosen/{id}
    public function update(Request $request, $id)
    {
        $dosen = Dosen::findOrFail($id);

        $request->validate([
            'nama'           => 'sometimes|string',
            'program_studi_id' => 'sometimes|exists:program_studi,id',
            'fakultas'       => 'sometimes|string',
            'jabatan'        => 'sometimes|in:Tenaga Pengajar,Asisten Ahli,Lektor,Lektor Kepala,Guru Besar',
            'golongan'       => 'nullable|string',
            'email_akademik' => 'nullable|email',
            'no_hp'          => 'nullable|string',
            'is_active'      => 'sometimes|boolean',
        ]);

        $dosen->update($request->only([
            'nama', 'program_studi_id', 'fakultas', 'jabatan',
            'golongan', 'email_akademik', 'no_hp', 'is_active',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Data dosen berhasil diperbarui.',
            'data'    => $dosen->load(['user', 'programStudi']),
        ]);
    }

    // DELETE /api/admin/dosen/{id}
    public function destroy($id)
    {
        $dosen = Dosen::findOrFail($id);
        $dosen->user()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Dosen berhasil dihapus.',
        ]);
    }
}
