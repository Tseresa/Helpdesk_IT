<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * FR-02 - Autentikasi Pengguna
 * Menggunakan Laravel Sanctum untuk penerbitan token API.
 */
class AuthController extends Controller
{
    /**
     * Login pengguna dan menerbitkan token akses.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password_hash)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Akun anda tidak aktif. Hubungi admin.'],
            ]);
        }

        $token = $user->createToken('helpdesk-token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user'    => $user->load(['role', 'department']),
            'token'   => $token,
        ]);
    }

    /**
     * Logout - mencabut token akses yang sedang digunakan.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logout berhasil']);
    }

    /**
     * Mengambil data pengguna yang sedang login.
     */
    public function me(Request $request)
    {
        return response()->json(
            $request->user()->load(['role', 'department'])
        );
    }
}
