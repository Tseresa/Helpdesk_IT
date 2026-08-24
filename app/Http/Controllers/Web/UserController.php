<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

/**
 * FR-14 - Manajemen Pengguna & Hak Akses (halaman web, khusus Admin).
 * Route dibungkus middleware 'role:Admin' di routes/web.php.
 */
class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['role', 'department']);

        if ($request->filled('role_id')) {
            $query->where('role_id', $request->role_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('full_name')->paginate(10)->withQueryString();
        $roles = Role::orderBy('role_name')->get();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('users.create', compact('roles', 'departments'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'role_id'       => ['required', 'exists:roles,role_id'],
            'department_id' => ['nullable', 'exists:departments,department_id'],
            'full_name'     => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:150', 'unique:users,email'],
            'password'      => ['required', 'string', 'min:8'],
        ]);

        User::create([
            'role_id'       => $data['role_id'],
            'department_id' => $data['department_id'] ?? null,
            'full_name'     => $data['full_name'],
            'email'         => $data['email'],
            'password_hash' => Hash::make($data['password']),
            'is_active'     => true,
        ]);

        return redirect()->route('users.index')->with('status', 'Pengguna baru berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::orderBy('role_name')->get();
        $departments = Department::orderBy('department_name')->get();

        return view('users.edit', compact('user', 'roles', 'departments'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'role_id'       => ['required', 'exists:roles,role_id'],
            'department_id' => ['nullable', 'exists:departments,department_id'],
            'full_name'     => ['required', 'string', 'max:150'],
            'email'         => ['required', 'email', 'max:150', 'unique:users,email,' . $user->user_id . ',user_id'],
            'password'      => ['nullable', 'string', 'min:8'],
        ]);

        $user->role_id = $data['role_id'];
        $user->department_id = $data['department_id'] ?? null;
        $user->full_name = $data['full_name'];
        $user->email = $data['email'];

        if (! empty($data['password'])) {
            $user->password_hash = Hash::make($data['password']);
        }

        $user->save();

        return redirect()->route('users.index')->with('status', 'Data pengguna berhasil diperbarui.');
    }

    public function toggleActive(User $user)
    {
        $user->update(['is_active' => ! $user->is_active]);

        $message = $user->is_active ? 'Akun diaktifkan kembali.' : 'Akun dinonaktifkan.';

        return back()->with('status', $message);
    }
}
