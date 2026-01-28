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
        // 1. Roles
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Métodos de Pago
        Schema::create('metodos_pago', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
            $table->string('descripcion', 255)->nullable();
            $table->boolean('activo')->default(1);
            $table->timestamps();
            $table->softDeletes();
        });

        // 7. Unidades SUNAT
        Schema::create('unidades_sunat', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 6)->unique();
            $table->string('descripcion', 100);
            $table->timestamps();
            $table->softDeletes();
        });

        // 8. Afectaciones IGV
        Schema::create('afectaciones_igv', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 2)->unique();
            $table->string('descripcion', 100);
            $table->boolean('gravado'); // 1=Si, 0=No
            $table->decimal('porcentaje', 5, 2)->default(18.00);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('system_catalogs_tables');
    }
};
