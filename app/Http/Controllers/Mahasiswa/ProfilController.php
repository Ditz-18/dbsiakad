<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

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
            'no_hp'         => 'nullable|string|max:20',
            'alamat'        => 'nullable|string',
            'email'         => 'nullable|email',
            'tempat_lahir'  => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'nama_ayah'     => 'nullable|string|max:100',
            'nama_ibu'      => 'nullable|string|max:100',
            'no_hp_wali'    => 'nullable|string|max:20',
        ]);

        $mahasiswa->update($request->only([
            'no_hp', 'alamat', 'email',
            'tempat_lahir', 'tanggal_lahir',
            'nama_ayah', 'nama_ibu', 'no_hp_wali',
        ]));

        return response()->json([
            'status'  => true,
            'message' => 'Profil berhasil diperbarui.',
            'data'    => $mahasiswa->load(['programStudi', 'dosenPa']),
        ]);
    }

    // POST /api/mahasiswa/profil/foto
    public function uploadFoto(Request $request)
    {
        $mahasiswa = $request->user()->mahasiswa;

        $request->validate([
            'foto' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        // Hapus foto lama jika ada
        if ($mahasiswa->foto) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($mahasiswa->foto);
        }

        $path = $request->file('foto')->store('foto-profil/mahasiswa', 'public');
        $mahasiswa->update(['foto' => $path]);

        return response()->json([
            'status'  => true,
            'message' => 'Foto profil berhasil diperbarui.',
            'data'    => ['foto_url' => asset('storage/' . $path)],
        ]);
    }

    // PUT /api/mahasiswa/profil/password
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

        // Hapus semua token lama kecuali yang sedang dipakai
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Kata sandi berhasil diperbarui.',
        ]);
    }
}
