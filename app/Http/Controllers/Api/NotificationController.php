<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;

/**
 * FR-07 - Notifikasi & Pengingat
 */
class NotificationController extends Controller
{
    /**
     * Daftar notifikasi milik pengguna yang sedang login.
     */
    public function index(Request $request)
    {
        $query = $request->user()->notifications()->orderByDesc('created_at');

        if ($request->boolean('unread_only')) {
            $query->where('is_read', false);
        }

        return response()->json($query->paginate($request->get('per_page', 20)));
    }

    public function unreadCount(Request $request)
    {
        return response()->json([
            'unread_count' => $request->user()->notifications()->where('is_read', false)->count(),
        ]);
    }

    public function markAsRead(Request $request, Notification $notification)
    {
        // Pastikan notifikasi ini memang milik pengguna yang login
        if ($notification->user_id !== $request->user()->user_id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $notification->update(['is_read' => true]);

        return response()->json($notification);
    }

    public function markAllAsRead(Request $request)
    {
        $request->user()->notifications()->where('is_read', false)->update(['is_read' => true]);

        return response()->json(['message' => 'Semua notifikasi ditandai sudah dibaca']);
    }

    public function destroy(Request $request, Notification $notification)
    {
        if ($notification->user_id !== $request->user()->user_id) {
            return response()->json(['message' => 'Tidak diizinkan'], 403);
        }

        $notification->delete();

        return response()->json(['message' => 'Notifikasi berhasil dihapus']);
    }
}
