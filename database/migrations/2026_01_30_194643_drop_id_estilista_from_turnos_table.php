<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('turnos', function (Blueprint $table) {
            // 1. Primero eliminamos la restricción de llave foránea
            // Laravel buscará automáticamente 'turnos_id_estilista_foreign'
            $table->dropForeign(['id_estilista']); 

            // 2. Ahora sí eliminamos la columna
            $table->dropColumn('id_estilista');
        });
    }

    public function down()
    {
        // Esto es por si algún día quieres revertir el cambio
        Schema::table('turnos', function (Blueprint $table) {
            $table->foreignId('id_estilista')->nullable()->constrained('estilistas');
        });
    }
};
