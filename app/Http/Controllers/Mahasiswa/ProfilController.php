<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    // GET /api/mahasiswa/profil
    public function show(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa->load(['programStudi', 'dosenPa', 'user']);

        return response()->json([
            'status' => true,
            'data'   => $mahasiswa,
        ]);
    }

    // PUT /api/mahasiswa/profil
    public function update(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $request->validate([
            'no_hp'  => 'nullable|string|max:20',
            'alamat' => 'nullable|string',
            'email'  => 'nullable|email',
        ]);

        $mahasiswa->update($request->only(['no_hp', 'alamat', 'email']));

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $mahasiswa->load(['programStudi', 'dosenPa']),
        ]);
    }
}
