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
        // 3. Clientes
        Schema::create('clientes', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('apellido', 150)->nullable();
            $table->enum('tipo_documento', ['DNI','RUC','CE','PAS','OTRO'])->nullable();
            $table->string('numero_documento', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['masculino','femenino','otro'])->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('direccion', 255)->nullable();
            $table->string('procedencia', 100)->nullable(); // Origen
            $table->timestamps();
            $table->softDeletes();

            // Índices optimizados
            $table->index('numero_documento');
            $table->index(['nombre', 'apellido']);
        });

        // 4. Estilistas
        Schema::create('estilistas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 150);
            $table->string('especialidad', 150)->nullable();
            $table->string('telefono', 50)->nullable();
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
        Schema::dropIfExists('bussines_actors_tables');
    }
};
