<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_comments', function (Blueprint $table) {
            $table->id('comment_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users', 'user_id');
            $table->text('comment_text');
            $table->boolean('is_internal')->default(false)
                ->comment('TRUE = catatan internal teknisi, tidak terlihat end-user');
            $table->timestamp('created_at')->useCurrent();

            $table->index('ticket_id', 'idx_comments_ticket');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_comments');
    }
};
