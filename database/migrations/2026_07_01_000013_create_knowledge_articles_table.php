<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_articles', function (Blueprint $table) {
            $table->id('article_id');
            $table->foreignId('category_id')->constrained('categories', 'category_id');
            $table->foreignId('created_by')->constrained('users', 'user_id');
            $table->string('title', 200);
            $table->longText('content');
            $table->unsignedInteger('view_count')->default(0);
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->fullText(['title', 'content'], 'idx_kb_search');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_articles');
    }
};
