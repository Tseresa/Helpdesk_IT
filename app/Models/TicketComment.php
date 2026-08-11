<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TicketComment extends Model
{
    protected $table = 'ticket_comments';
    protected $primaryKey = 'comment_id';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'user_id',
        'comment_text',
        'is_internal',
    ];

    protected $casts = [
        'is_internal' => 'boolean',
        'created_at'  => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
