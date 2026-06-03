<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    // POST /api/auth/login
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        // Coba login pakai username atau email
        $user = User::where('username', $request->username)
                    ->orWhere('email', $request->username)
                    ->first();

        if (!$user || !Auth::attempt(['email' => $user->email, 'password' => $request->password])) {
            return response()->json([
                'status'  => false,
                'message' => 'Username atau password salah.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => 'Akun Anda tidak aktif. Hubungi administrator.',
            ], 403);
        }

        // Update last login
        $user->update(['last_login_at' => now()]);

        // Hapus token lama, buat token baru
        $user->tokens()->delete();
        $token = $user->createToken('siakad-token')->plainTextToken;

        // Load profil sesuai role
        $profil = match ($user->role) {
            'mahasiswa' => $user->mahasiswa,
            'dosen'     => $user->dosen,
            'admin'     => $user->admin,
            default     => null,
        };

        return response()->json([
            'status'  => true,
            'message' => 'Login berhasil.',
            'data'    => [
                'token' => $token,
                'user'  => [
                    'id'       => $user->id,
                    'username' => $user->username,
                    'email'    => $user->email,
                    'role'     => $user->role,
                    'profil'   => $profil,
                ],
            ],
        ]);
    }

    // POST /api/auth/logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'status'  => true,
            'message' => 'Logout berhasil.',
        ]);
    }

    // GET /api/auth/me
    public function me(Request $request)
    {
        $user  = $request->user();

        $profil = match ($user->role) {
            'mahasiswa' => $user->mahasiswa,
            'dosen'     => $user->dosen,
            'admin'     => $user->admin,
            default     => null,
        };

        return response()->json([
            'status' => true,
            'data'   => [
                'id'       => $user->id,
                'username' => $user->username,
                'email'    => $user->email,
                'role'     => $user->role,
                'profil'   => $profil,
            ],
        ]);
    }
}