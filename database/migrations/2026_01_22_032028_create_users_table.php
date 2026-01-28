<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            // Relación con Roles
            $table->foreignId('id_rol')->constrained('roles')->onUpdate('cascade')->onDelete('restrict');
            
            $table->string('nombre', 150);
            $table->string('email', 150)->unique();
            $table->string('password');
            $table->boolean('activo')->default(1);
            $table->rememberToken();
            $table->timestamps();
            $table->softDeletes();
        });

        // Tablas auxiliares de Laravel (Password Reset) - Es bueno mantenerlas
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
