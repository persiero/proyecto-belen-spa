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
        // 6. Categorías
        Schema::create('categorias_servicio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // 9. Servicios (La tabla central de este módulo)
        Schema::create('servicios', function (Blueprint $table) {
            $table->id();
            
            // Relaciones
            $table->foreignId('id_categoria')->nullable()->constrained('categorias_servicio')->nullOnDelete();
            $table->foreignId('id_afectacion')->constrained('afectaciones_igv');
            $table->foreignId('id_unidad')->constrained('unidades_sunat');

            $table->string('nombre', 150);
            $table->decimal('precio', 10, 2); // Precio Final
            $table->integer('duracion_minutos')->nullable();
            $table->boolean('activo')->default(1);
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services_tables');
    }
};
