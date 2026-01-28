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
        Schema::table('config_tributaria', function (Blueprint $table) {
            // Datos para conectar con SUNAT (Greenter)
            $table->string('usuario_sol')->nullable()->after('emision_automatica_cpe');
            $table->string('clave_sol')->nullable()->after('usuario_sol');
            $table->string('certificado_path')->nullable()->after('clave_sol'); // Ruta del archivo .pem o .pfx
            $table->string('certificado_password')->nullable()->after('certificado_path');
            $table->enum('modo', ['beta', 'produccion'])->default('beta')->after('certificado_password'); // Entorno
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('config_tributaria', function (Blueprint $table) {
            $table->dropColumn(['usuario_sol', 'clave_sol', 'certificado_path', 'certificado_password', 'modo']);
        });
    }
};
