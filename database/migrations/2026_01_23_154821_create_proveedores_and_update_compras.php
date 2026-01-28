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
        // 1. Crear tabla Proveedores
        Schema::create('proveedores', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_empresa', 150); // Razón Social o Nombre
            $table->string('ruc_dni', 20)->nullable()->unique();
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('contacto', 100)->nullable(); // Nombre del vendedor
            $table->string('direccion', 255)->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Modificar tabla Compras para vincular proveedor
        Schema::table('compras', function (Blueprint $table) {
            $table->foreignId('id_proveedor')
                  ->nullable() // <--- CLAVE: Es opcional (RN-INVT-05)
                  ->after('id')
                  ->constrained('proveedores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('compras', function (Blueprint $table) {
            $table->dropForeign(['id_proveedor']);
            $table->dropColumn('id_proveedor');
        });

        Schema::dropIfExists('proveedores');
    }

};
