<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sla_policies', function (Blueprint $table) {
            $table->id('sla_id');
            $table->foreignId('category_id')->constrained('categories', 'category_id');
            $table->foreignId('priority_id')->constrained('priorities', 'priority_id');
            $table->unsignedInteger('response_minutes')->comment('Batas waktu respons pertama (menit)');
            $table->unsignedInteger('resolution_minutes')->comment('Batas waktu penyelesaian (menit)');
            $table->timestamps();

            $table->unique(['category_id', 'priority_id'], 'uq_sla_cat_priority');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sla_policies');
    }
};
