<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProfilController extends Controller
{
    // GET /api/dosen/profil
    public function show(Request $request)
    {
        $dosen = $request->user()->dosen->load(['programStudi', 'user']);

        return response()->json([
            'status' => true,
            'data'   => $dosen,
        ]);
    }

    // PUT /api/dosen/profil
    public function update(Request $request)
    {
        $dosen = $request->user()->dosen;

        $request->validate([
            'no_hp'          => 'nullable|string|max:20',
            'email_akademik' => 'nullable|email',
        ]);

        $dosen->update($request->only(['no_hp', 'email_akademik']));

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $dosen->load('programStudi'),
        ]);
    }

    // POST /api/dosen/profil/foto
    public function uploadFoto(Request $request)
    {
        $dosen = $request->user()->dosen;

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        if ($dosen->foto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($dosen->foto);
        }

        $path = $request->file('foto')->store('foto-profil/dosen', 'public');
        $dosen->update(['foto' => $path]);

        return response()->json([
            'status'  => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data'    => ['foto_url' => asset('storage/' . $path)],
        ]);
    }

    // PUT /api/dosen/profil/password
    public function gantiPassword(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'password_lama'           => 'required|string',
            'password_baru'           => 'required|string|min:8',
            'password_baru_konfirmasi'=> 'required|same:password_baru',
        ]);

        if (!Hash::check($request->password_lama, $user->password)) {
            return response()->json([
                'status'  => false,
                'message' => 'Kata sandi lama tidak sesuai.',
            ], 422);
        }

        $user->update(['password' => $request->password_baru]);

        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }
}
