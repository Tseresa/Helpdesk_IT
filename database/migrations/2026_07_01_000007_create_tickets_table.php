<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id('ticket_id');
            $table->foreignId('requester_id')->constrained('users', 'user_id');
            $table->foreignId('assigned_to')->nullable()->constrained('users', 'user_id');
            $table->foreignId('category_id')->constrained('categories', 'category_id');
            $table->foreignId('priority_id')->constrained('priorities', 'priority_id');
            $table->foreignId('sla_id')->nullable()->constrained('sla_policies', 'sla_id');
            $table->string('subject', 200);
            $table->text('description');
            $table->enum('status', ['Open', 'In Progress', 'Pending', 'Resolved', 'Closed'])->default('Open');
            $table->dateTime('due_at')->nullable()->comment('Tenggat waktu SLA');
            $table->dateTime('resolved_at')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();

            $table->index('status', 'idx_tickets_status');
            $table->index('requester_id', 'idx_tickets_requester');
            $table->index('assigned_to', 'idx_tickets_assignee');
            $table->index('created_at', 'idx_tickets_created');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};
