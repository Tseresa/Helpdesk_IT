<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attachment;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * FR-10 - Lampiran File pada Tiket
 */
class AttachmentController extends Controller
{
    public function index(Ticket $ticket)
    {
        return response()->json($ticket->attachments()->with('uploader')->get());
    }

    public function store(Request $request, Ticket $ticket)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:10240'], // maksimal 10 MB
        ]);

        $file = $request->file('file');
        $path = $file->store('ticket-attachments/' . $ticket->ticket_id, 'public');

        $attachment = Attachment::create([
            'ticket_id'    => $ticket->ticket_id,
            'uploaded_by'  => $request->user()->user_id,
            'file_name'    => $file->getClientOriginalName(),
            'file_path'    => $path,
            'file_size_kb' => intdiv($file->getSize(), 1024),
        ]);

        return response()->json($attachment->load('uploader'), 201);
    }

    public function destroy(Attachment $attachment)
    {
        Storage::disk('public')->delete($attachment->file_path);
        $attachment->delete();

        return response()->json(['message' => 'Lampiran berhasil dihapus']);
    }
}
