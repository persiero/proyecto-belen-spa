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
        Schema::create('movimientos_caja', function (Blueprint $table) {
            $table->id();
            
            // CORRECCIÓN CLAVE:
            // 1. Usamos 'id_caja' (porque pertenece a la sesión de dinero).
            // 2. Apuntamos a 'caja' (SINGULAR), tal como está en tu script SQL.
            $table->foreignId('id_caja')->constrained('caja')->onDelete('cascade');
            
            $table->string('tipo', 20); // 'egreso'
            $table->decimal('monto', 10, 2);
            $table->string('descripcion')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_caja');
    }
};
