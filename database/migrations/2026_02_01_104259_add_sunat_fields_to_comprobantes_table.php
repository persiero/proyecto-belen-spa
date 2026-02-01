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
            Schema::table('comprobantes', function (Blueprint $table) {
            // 1. Referencia para Notas de Crédito (A qué comprobante afecta)
            $table->foreignId('id_comprobante_ref')->nullable()
                ->after('id_serie_comprobante')
                ->constrained('comprobantes'); 
                
            // 2. Motivo de la Nota de Crédito (Catálogo 09 SUNAT)
            $table->string('cod_motivo_nc', 2)->nullable()->after('id_comprobante_ref');
            $table->string('descripcion_motivo_nc', 200)->nullable()->after('cod_motivo_nc');

            // 3. Leyenda (Monto en letras - Obligatorio guardar)
            $table->string('leyenda_sunat', 255)->nullable()->after('mensaje_sunat');
            
            // 4. Forma de Pago (Requisito SUNAT desde 2021)
            $table->string('forma_pago', 20)->default('Contado')->after('total');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('comprobantes', function (Blueprint $table) {
            // 1. Primero borramos la llave foránea (Laravel usa el formato tabla_columna_foreign)
            $table->dropForeign(['id_comprobante_ref']);
            
            // 2. Luego borramos las columnas creadas
            $table->dropColumn([
                'id_comprobante_ref',
                'cod_motivo_nc',
                'descripcion_motivo_nc',
                'leyenda_sunat',
                'forma_pago'
            ]);
        });
    }
};
