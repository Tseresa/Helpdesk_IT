<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id('user_id');
            $table->foreignId('role_id')->constrained('roles', 'role_id');
            $table->foreignId('department_id')->nullable()->constrained('departments', 'department_id');
            $table->string('full_name', 150);
            $table->string('email', 150)->unique();
            $table->string('password_hash', 255);
            $table->string('phone', 30)->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();

            $table->index('email', 'idx_users_email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
