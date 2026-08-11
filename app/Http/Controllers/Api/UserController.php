<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * FR-14 - Manajemen Pengguna & Hak Akses
 */
class UserController extends Controller
{
    /**
     * Daftar pengguna dengan filter opsional (role, department, status) dan pencarian nama/email.
     */
    public function index(Request $request)
    {
        $query = User::with(['role', 'department']);

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('is_active')) {
            $query->where('is_active', (bool) $request->is_active);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        return response()->json(
            $query->orderBy('full_name')->paginate($request->get('per_page', 15))
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id'       => ['required', 'exists:roles,role_id'],
            'department_id' => ['nullable', 'exists:departments,department_id'],
            'full_name'     => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
            'phone'         => ['nullable', 'string', 'max:30'],
        ]);

        $user = User::create([
            'role_id'       => $data['role_id'],
            'department_id' => $data['department_id'] ?? null,
            'full_name'     => $data['full_name'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'phone'         => $data['phone'] ?? null,
            'is_active'     => true,
        ]);

        return response()->json($user->load(['role', 'department']), 201);
    }

    public function show(User $user)
    {
        return response()->json($user->load(['role', 'department']));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id'       => ['sometimes', 'exists:roles,role_id'],
            'department_id' => ['nullable', 'exists:departments,department_id'],
            'full_name'     => ['sometimes', 'string', 'max:150'],
            'email'         => ['sometimes', 'email', 'max:150', 'unique:users,email,' . $user->user_id . ',user_id'],
            'phone'         => ['nullable', 'string', 'max:30'],
        ]);

        $user->update($data);

        return response()->json($user->load(['role', 'department']));
    }

    /**
     * Mengubah password pengguna (dipanggil terpisah dari update data profil).
     */
    public function changePassword(Request $request, User $user)
    {
        $data = $request->validate([
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user->update(['password_hash' => Hash::make($data['password'])]);

        return response()->json(['message' => 'Password berhasil diperbarui']);
    }

    /**
     * Menonaktifkan (bukan menghapus) akun pengguna untuk menjaga riwayat tiket.
     */
    public function deactivate(User $user)
    {
        $user->update(['is_active' => false]);

        return response()->json(['message' => 'Akun pengguna dinonaktifkan', 'user' => $user]);
    }

    public function activate(User $user)
    {
        $user->update(['is_active' => true]);

        return response()->json(['message' => 'Akun pengguna diaktifkan kembali', 'user' => $user]);
    }

    public function destroy(User $user)
    {
        if ($user->assignedTickets()->exists() || $user->requestedTickets()->exists()) {
            return response()->json([
                'message' => 'Pengguna tidak dapat dihapus karena memiliki riwayat tiket. Nonaktifkan akun sebagai gantinya.',
            ], 409);
        }

        $user->delete();

        return response()->json(['message' => 'Pengguna berhasil dihapus']);
    }
}
