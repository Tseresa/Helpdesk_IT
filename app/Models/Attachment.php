<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attachment extends Model
{
    protected $primaryKey = 'attachment_id';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'uploaded_by',
        'file_name',
        'file_path',
        'file_size_kb',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'user_id');
    }
}
