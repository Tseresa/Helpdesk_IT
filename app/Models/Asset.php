<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Asset extends Model
{
    use HasFactory;

    protected $primaryKey = 'asset_id';

    protected $fillable = [
        'owner_id',
        'asset_tag',
        'asset_type',
        'brand_model',
        'serial_number',
        'location',
        'status',
        'purchased_at',
    ];

    protected $casts = [
        'purchased_at' => 'date',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id', 'user_id');
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(
            Ticket::class,
            'ticket_assets',
            'asset_id',
            'ticket_id'
        );
    }
}
