<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('feedback', function (Blueprint $table) {
            $table->id('feedback_id');
            $table->foreignId('ticket_id')->unique()->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->unsignedTinyInteger('rating')->comment('Skala 1-5');
            $table->string('comment', 500)->nullable();
            $table->timestamp('submitted_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
    }
};
