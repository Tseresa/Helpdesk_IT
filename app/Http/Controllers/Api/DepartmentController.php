<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function index()
    {
        return response()->json(Department::orderBy('department_name')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'department_name' => ['required', 'string', 'max:100', 'unique:departments,department_name'],
        ]);

        return response()->json(Department::create($data), 201);
    }

    public function show(Department $department)
    {
        return response()->json($department);
    }

    public function update(Request $request, Department $department)
    {
        $data = $request->validate([
            'department_name' => [
                'sometimes', 'string', 'max:100',
                'unique:departments,department_name,' . $department->department_id . ',department_id',
            ],
        ]);

        $department->update($data);

        return response()->json($department);
    }

    public function destroy(Department $department)
    {
        if ($department->users()->exists()) {
            return response()->json([
                'message' => 'Departemen tidak dapat dihapus karena masih memiliki pengguna.',
            ], 409);
        }

        $department->delete();

        return response()->json(['message' => 'Departemen berhasil dihapus']);
    }
}
