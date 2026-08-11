<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlaPolicy extends Model
{
    use HasFactory;

    protected $table = 'sla_policies';
    protected $primaryKey = 'sla_id';

    protected $fillable = [
        'category_id',
        'priority_id',
        'response_minutes',
        'resolution_minutes',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function priority(): BelongsTo
    {
        return $this->belongsTo(Priority::class, 'priority_id', 'priority_id');
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'sla_id', 'sla_id');
    }
}
