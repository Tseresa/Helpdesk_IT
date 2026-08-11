<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;

/**
 * Manajemen data referensi Peran Pengguna (Admin only).
 */
class RoleController extends Controller
{
    public function index()
    {
        return response()->json(Role::orderBy('role_name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_name'   => ['required', 'string', 'max:50', 'unique:roles,role_name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role = Role::create($data);

        return response()->json($role, 201);
    }

    public function show(Role $role)
    {
        return response()->json($role);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'role_name'   => ['sometimes', 'string', 'max:50', 'unique:roles,role_name,' . $role->role_id . ',role_id'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $role->update($data);

        return response()->json($role);
    }

    public function destroy(Role $role)
    {
        if ($role->users()->exists()) {
            return response()->json([
                'message' => 'Peran tidak dapat dihapus karena masih digunakan oleh pengguna.',
            ], 409);
        }

        $role->delete();

        return response()->json(['message' => 'Peran berhasil dihapus']);
    }
}
