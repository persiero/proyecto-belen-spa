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
        Schema::table('detalles_venta', function (Blueprint $table) {
        
            // 1. Creamos la columna
            $table->unsignedBigInteger('id_estilista')->nullable()->after('id_producto');
            
            // 2. Creamos la relación (Llave foránea)
            // Esto obliga a que el ID que guardes aquí REALMENTE exista en la tabla estilistas
            $table->foreign('id_estilista')
                ->references('id')
                ->on('estilistas')
                ->onDelete('set null'); // Si borras la estilista, la venta queda pero sin dueño.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('detalles_venta', function (Blueprint $table) {
            $table->dropColumn('id_estilista');
        });
    }
};
