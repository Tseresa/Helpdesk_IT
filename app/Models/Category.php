<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    protected $primaryKey = 'category_id';

    protected $fillable = [
        'category_name',
        'description',
    ];

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'category_id', 'category_id');
    }

    public function slaPolicies(): HasMany
    {
        return $this->hasMany(SlaPolicy::class, 'category_id', 'category_id');
    }

    public function knowledgeArticles(): HasMany
    {
        return $this->hasMany(KnowledgeArticle::class, 'category_id', 'category_id');
    }
}
