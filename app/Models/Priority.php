<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Priority extends Model
{
    use HasFactory;

    protected $primaryKey = 'priority_id';

    protected $fillable = [
        'priority_name',
        'priority_level',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'priority_id', 'priority_id');
    }

    public function slaPolicies(): HasMany
    {
        return $this->hasMany(SlaPolicy::class, 'priority_id', 'priority_id');
    }
}
