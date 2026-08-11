<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id('asset_id');
            $table->foreignId('owner_id')->nullable()->constrained('users', 'user_id');
            $table->string('asset_tag', 50)->unique();
            $table->string('asset_type', 100)->comment('mis. Laptop, Printer, Lisensi Software');
            $table->string('brand_model', 150)->nullable();
            $table->string('serial_number', 150)->nullable();
            $table->string('location', 150)->nullable();
            $table->enum('status', ['Active', 'In Repair', 'Retired', 'Lost'])->default('Active');
            $table->date('purchased_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
