<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Model notifikasi kustom (tabel 'notifications' dengan struktur sendiri).
 * Bukan sistem notifikasi database bawaan Laravel (Illuminate\Notifications).
 */
class Notification extends Model
{
    protected $primaryKey = 'notification_id';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'ticket_id',
        'message',
        'is_read',
    ];

    protected $casts = [
        'is_read'    => 'boolean',
        'created_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}
