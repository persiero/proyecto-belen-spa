<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void {
        Schema::table('estilistas', function (Blueprint $table) {
            $table->integer('porcentaje_comision')->default(0)->after('telefono'); 
            // Ej: 50 significa 50%
        });
    }
    public function down(): void {
        Schema::table('estilistas', function (Blueprint $table) {
            $table->dropColumn('porcentaje_comision');
        });
    }
};
