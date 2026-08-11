<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SlaPolicy;
use Illuminate\Http\Request;

/**
 * FR-04 - Penetapan Prioritas & SLA
 * Mengelola batas waktu respons dan penyelesaian per kombinasi kategori+prioritas.
 */
class SlaPolicyController extends Controller
{
    public function index()
    {
        return response()->json(
            SlaPolicy::with(['category', 'priority'])->get()
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id'        => ['required', 'exists:categories,category_id'],
            'priority_id'        => ['required', 'exists:priorities,priority_id'],
            'response_minutes'   => ['required', 'integer', 'min:1'],
            'resolution_minutes' => ['required', 'integer', 'min:1'],
        ]);

        $exists = SlaPolicy::where('category_id', $data['category_id'])
            ->where('priority_id', $data['priority_id'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'Kebijakan SLA untuk kombinasi kategori dan prioritas ini sudah ada.',
            ], 409);
        }

        $sla = SlaPolicy::create($data);

        return response()->json($sla->load(['category', 'priority']), 201);
    }

    public function show(SlaPolicy $slaPolicy)
    {
        return response()->json($slaPolicy->load(['category', 'priority']));
    }

    public function update(Request $request, SlaPolicy $slaPolicy)
    {
        $data = $request->validate([
            'response_minutes'   => ['sometimes', 'integer', 'min:1'],
            'resolution_minutes' => ['sometimes', 'integer', 'min:1'],
        ]);

        $slaPolicy->update($data);

        return response()->json($slaPolicy->load(['category', 'priority']));
    }

    public function destroy(SlaPolicy $slaPolicy)
    {
        $slaPolicy->delete();

        return response()->json(['message' => 'Kebijakan SLA berhasil dihapus']);
    }
}
