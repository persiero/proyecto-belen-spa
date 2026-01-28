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
        Schema::table('movimientos_caja', function (Blueprint $table) {
            // Agregamos la columna relacionando con la tabla 'usuarios' (o 'users' si usas la default)
            // Según tu código anterior usas 'usuarios', así que:
            $table->foreignId('id_usuario')
                ->after('descripcion') // Para ordenarlo visualmente
                ->constrained('usuarios'); // Relación FK
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropForeign(['id_usuario']);
            $table->dropColumn('id_usuario');
        });
    }
};
