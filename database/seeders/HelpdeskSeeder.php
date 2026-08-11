<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Department;
use App\Models\Priority;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class HelpdeskSeeder extends Seeder
{
    public function run(): void
    {
        // Roles
        $roles = collect(['End User', 'Teknisi', 'Supervisor', 'Admin', 'Manajemen'])
            ->mapWithKeys(fn ($name) => [$name => Role::firstOrCreate(['role_name' => $name])]);

        // Departments
        $itDept = Department::firstOrCreate(['department_name' => 'IT']);
        Department::firstOrCreate(['department_name' => 'Keuangan']);
        Department::firstOrCreate(['department_name' => 'HR']);

        // Categories
        $categories = collect([
            ['category_name' => 'Hardware', 'description' => 'Masalah perangkat keras'],
            ['category_name' => 'Software', 'description' => 'Masalah aplikasi dan sistem operasi'],
            ['category_name' => 'Jaringan', 'description' => 'Masalah koneksi internet/intranet'],
            ['category_name' => 'Akun', 'description' => 'Masalah akun pengguna, password, akses'],
            ['category_name' => 'Lainnya', 'description' => 'Kategori umum lainnya'],
        ])->map(fn ($c) => Category::firstOrCreate(['category_name' => $c['category_name']], $c));

        // Priorities
        collect([
            ['priority_name' => 'Low', 'priority_level' => 1],
            ['priority_name' => 'Medium', 'priority_level' => 2],
            ['priority_name' => 'High', 'priority_level' => 3],
            ['priority_name' => 'Critical', 'priority_level' => 4],
        ])->each(fn ($p) => Priority::firstOrCreate(['priority_name' => $p['priority_name']], $p));

        // Contoh akun admin default
        User::firstOrCreate(
            ['email' => 'admin@helpdesk.local'],
            [
                'role_id'       => $roles['Admin']->role_id,
                'department_id' => $itDept->department_id,
                'full_name'     => 'Administrator',
                'password_hash' => Hash::make('password123'),
                'is_active'     => true,
            ]
        );
    }
}
