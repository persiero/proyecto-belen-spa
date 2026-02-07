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
        Schema::create('turno_productos', function (Blueprint $table) {
            $table->id();
            
            // Relación con el Turno principal
            $table->foreignId('id_turno')->constrained('turnos')->onDelete('cascade');
            
            // Relación con el Producto vendido
            $table->foreignId('id_producto')->constrained('productos');
            
            // Relación con el Estilista (¡El requerimiento del dueño!)
            $table->foreignId('id_estilista')->constrained('estilistas');
            
            $table->integer('cantidad')->default(1);
            $table->decimal('precio', 10, 2); // Precio al momento de la venta
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('turno_productos');
    }
};
