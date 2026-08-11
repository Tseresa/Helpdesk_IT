<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeArticle extends Model
{
    use HasFactory;

    protected $table = 'knowledge_articles';
    protected $primaryKey = 'article_id';

    protected $fillable = [
        'category_id',
        'created_by',
        'title',
        'content',
        'view_count',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }
}
