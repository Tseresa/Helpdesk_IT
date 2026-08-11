<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Ticket extends Model
{
    use HasFactory;

    protected $primaryKey = 'ticket_id';

    protected $fillable = [
        'requester_id',
        'assigned_to',
        'category_id',
        'priority_id',
        'sla_id',
        'subject',
        'description',
        'status',
        'due_at',
        'resolved_at',
        'closed_at',
    ];

    protected $casts = [
        'due_at'      => 'datetime',
        'resolved_at' => 'datetime',
        'closed_at'   => 'datetime',
    ];

    // ------------------------------------------------------------------
    // Relasi
    // ------------------------------------------------------------------

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id', 'user_id');
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to', 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id', 'priority_id');
    }

    public function sla(): BelongsTo
    {
        return $this->belongsTo(SlaPolicy::class, 'sla_id', 'sla_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(TicketHistory::class, 'ticket_id', 'ticket_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(TicketComment::class, 'ticket_id', 'ticket_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(Attachment::class, 'ticket_id', 'ticket_id');
    }

    public function feedback(): HasOne
    {
        return $this->hasOne(Feedback::class, 'ticket_id', 'ticket_id');
    }

    public function assets(): BelongsToMany
    {
        return $this->belongsToMany(
            Asset::class,
            'ticket_assets',
            'ticket_id',
            'asset_id'
        );
    }
}
