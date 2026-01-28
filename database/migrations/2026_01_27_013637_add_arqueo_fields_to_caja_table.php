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
        Schema::table('caja', function (Blueprint $table) {
            // El dinero que el cajero cuenta físicamente
            $table->decimal('monto_real', 10, 2)->nullable()->after('monto_cierre');
            
            // La resta (Real - Sistema). Si es negativo, faltó dinero.
            $table->decimal('diferencia', 10, 2)->default(0)->after('monto_real');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('caja', function (Blueprint $table) {
            $table->dropColumn(['monto_real', 'diferencia']);
        });
    }
};
