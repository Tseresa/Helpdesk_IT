<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $primaryKey = 'feedback_id';

    public $timestamps = false;

    protected $fillable = [
        'ticket_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id', 'ticket_id');
    }
}
