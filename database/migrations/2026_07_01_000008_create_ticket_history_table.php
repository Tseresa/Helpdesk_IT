<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_history', function (Blueprint $table) {
            $table->id('history_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->foreignId('changed_by')->constrained('users', 'user_id');
            $table->string('field_changed', 50)->comment('mis. status, assigned_to, priority_id');
            $table->string('old_value', 255)->nullable();
            $table->string('new_value', 255)->nullable();
            $table->dateTime('changed_at')->useCurrent();

            $table->index('ticket_id', 'idx_history_ticket');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_history');
    }
};
