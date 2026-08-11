<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attachments', function (Blueprint $table) {
            $table->id('attachment_id');
            $table->foreignId('ticket_id')->constrained('tickets', 'ticket_id')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->constrained('users', 'user_id');
            $table->string('file_name', 255);
            $table->string('file_path', 500);
            $table->unsignedInteger('file_size_kb')->nullable();
            $table->timestamp('uploaded_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
    }
};
