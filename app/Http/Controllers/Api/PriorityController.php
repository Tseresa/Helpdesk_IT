<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Priority;
use Illuminate\Http\Request;

/**
 * FR-04 - Penetapan Prioritas
 */
class PriorityController extends Controller
{
    public function index()
    {
        return response()->json(Priority::orderBy('priority_level')->get());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'priority_name'  => ['required', 'string', 'max:50', 'unique:priorities,priority_name'],
            'priority_level' => ['required', 'integer', 'min:1', 'max:4'],
        ]);

        return response()->json(Priority::create($data), 201);
    }

    public function show(Priority $priority)
    {
        return response()->json($priority);
    }

    public function update(Request $request, Priority $priority)
    {
        $data = $request->validate([
            'priority_name' => [
                'sometimes', 'string', 'max:50',
                'unique:priorities,priority_name,' . $priority->priority_id . ',priority_id',
            ],
            'priority_level' => ['sometimes', 'integer', 'min:1', 'max:4'],
        ]);

        $priority->update($data);

        return response()->json($priority);
    }

    public function destroy(Priority $priority)
    {
        if ($priority->tickets()->exists()) {
            return response()->json([
                'message' => 'Prioritas tidak dapat dihapus karena masih digunakan oleh tiket.',
            ], 409);
        }

        $priority->delete();

        return response()->json(['message' => 'Prioritas berhasil dihapus']);
    }
}
