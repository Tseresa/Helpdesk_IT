<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Ticket;
use Illuminate\Http\Request;

/**
 * FR-11 - Manajemen Aset IT
 */
class AssetController extends Controller
{
    public function index(Request $request)
    {
        $query = Asset::with('owner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('owner_id')) {
            $query->where('owner_id', $request->owner_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('asset_tag', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand_model', 'like', "%{$search}%");
            });
        }

        return response()->json($query->orderBy('asset_tag')->paginate($request->get('per_page', 15)));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'owner_id'      => ['nullable', 'exists:users,user_id'],
            'asset_tag'     => ['required', 'string', 'max:50', 'unique:assets,asset_tag'],
            'asset_type'    => ['required', 'string', 'max:100'],
            'brand_model'   => ['nullable', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'location'      => ['nullable', 'string', 'max:150'],
            'status'        => ['sometimes', 'in:Active,In Repair,Retired,Lost'],
            'purchased_at'  => ['nullable', 'date'],
        ]);

        return response()->json(Asset::create($data)->load('owner'), 201);
    }

    public function show(Asset $asset)
    {
        return response()->json($asset->load(['owner', 'tickets']));
    }

    public function update(Request $request, Asset $asset)
    {
        $data = $request->validate([
            'owner_id'      => ['nullable', 'exists:users,user_id'],
            'asset_type'    => ['sometimes', 'string', 'max:100'],
            'brand_model'   => ['nullable', 'string', 'max:150'],
            'serial_number' => ['nullable', 'string', 'max:150'],
            'location'      => ['nullable', 'string', 'max:150'],
            'status'        => ['sometimes', 'in:Active,In Repair,Retired,Lost'],
        ]);

        $asset->update($data);

        return response()->json($asset->fresh('owner'));
    }

    /**
     * Menghubungkan aset dengan tiket terkait (tabel pivot ticket_assets).
     */
    public function attachToTicket(Request $request, Ticket $ticket)
    {
        $data = $request->validate([
            'asset_id' => ['required', 'exists:assets,asset_id'],
        ]);

        $ticket->assets()->syncWithoutDetaching([$data['asset_id']]);

        return response()->json($ticket->load('assets'));
    }

    public function destroy(Asset $asset)
    {
        $asset->delete();

        return response()->json(['message' => 'Aset berhasil dihapus']);
    }
}
