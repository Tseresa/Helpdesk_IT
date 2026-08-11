<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('priorities', function (Blueprint $table) {
            $table->id('priority_id');
            $table->string('priority_name', 50)->unique();
            $table->unsignedTinyInteger('priority_level')->comment('1=Low ... 4=Critical');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('priorities');
    }
};
