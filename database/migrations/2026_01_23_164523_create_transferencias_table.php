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
        Schema::create('transferencias', function (Blueprint $table) {
            $table->id();
            
            // Relación con Productos
            $table->foreignId('id_producto')
                  ->constrained('productos')
                  ->onUpdate('cascade')
                  ->onDelete('restrict');

            // Campos de la transferencia
            $table->enum('origen', ['venta', 'insumo']);
            $table->enum('destino', ['venta', 'insumo']);
            $table->integer('cantidad');
            $table->dateTime('fecha');
            $table->string('observaciones', 255)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transferencias');
    }
};
