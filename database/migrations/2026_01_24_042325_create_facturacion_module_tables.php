<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. TIPOS DE COMPROBANTE
        Schema::create('tipos_comprobante', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_sunat', 2)->unique(); // 01, 03, 07
            $table->string('descripcion', 100);
            $table->boolean('requiere_cliente_doc')->default(false);
            $table->timestamps();
        });

        // Insertar Semillas (Seeds)
        DB::table('tipos_comprobante')->insert([
            ['codigo_sunat' => '01', 'descripcion' => 'Factura Electrónica', 'requiere_cliente_doc' => 1],
            ['codigo_sunat' => '03', 'descripcion' => 'Boleta Electrónica', 'requiere_cliente_doc' => 0],
            ['codigo_sunat' => '07', 'descripcion' => 'Nota de Crédito', 'requiere_cliente_doc' => 1],
        ]);

        // 2. SERIES COMPROBANTE
        Schema::create('series_comprobante', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_tipo_comprobante')->constrained('tipos_comprobante');
            $table->string('serie', 4); // F001, B001
            $table->unsignedBigInteger('correlativo_actual')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->unique(['id_tipo_comprobante', 'serie']);
        });

        // Insertar Series por defecto
        // Asumimos IDs 1, 2, 3 por el orden de inserción arriba
        DB::table('series_comprobante')->insert([
            ['id_tipo_comprobante' => 1, 'serie' => 'F001', 'correlativo_actual' => 0], // Factura
            ['id_tipo_comprobante' => 2, 'serie' => 'B001', 'correlativo_actual' => 0], // Boleta
            ['id_tipo_comprobante' => 3, 'serie' => 'BC01', 'correlativo_actual' => 0], // Nota Credito (Boleta)
            ['id_tipo_comprobante' => 3, 'serie' => 'FC01', 'correlativo_actual' => 0], // Nota Credito (Factura)
        ]);

        // 3. COMPROBANTES (Cabecera)
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->nullable()->constrained('ventas')->nullOnDelete();
            $table->foreignId('id_tipo_comprobante')->constrained('tipos_comprobante');
            $table->foreignId('id_serie_comprobante')->constrained('series_comprobante');
            
            $table->string('serie', 4);
            $table->integer('correlativo');
            $table->dateTime('fecha_emision');

            // Snapshot Receptor (Para que sea inmutable)
            $table->string('receptor_tipo_doc', 2)->nullable();
            $table->string('receptor_numero_doc', 20)->nullable();
            $table->string('receptor_razon_social', 255)->nullable();
            $table->string('receptor_direccion', 255)->nullable();

            // Totales
            $table->decimal('op_gravadas', 10, 2)->default(0);
            $table->decimal('op_exoneradas', 10, 2)->default(0);
            $table->decimal('op_inafectas', 10, 2)->default(0);
            $table->decimal('monto_igv', 10, 2)->default(0);
            $table->decimal('total', 10, 2);

            // Datos SUNAT
            $table->string('nombre_xml')->nullable();
            $table->string('hash_cpe')->nullable();
            $table->string('estado_sunat')->default('emitido'); // emitido, aceptado, rechazado, anulado
            $table->text('mensaje_sunat')->nullable(); // Descripción del CDR o error
            $table->text('cdr_xml')->nullable(); // Ruta
            $table->text('ruta_pdf')->nullable(); // Ruta
            $table->boolean('enviado_sunat')->default(false);

            $table->timestamps();
            $table->unique(['id_tipo_comprobante', 'serie', 'correlativo']);
        });

        // 4. DETALLE COMPROBANTE (Snapshot de items)
        Schema::create('comprobantes_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_comprobante')->constrained('comprobantes')->onDelete('cascade');
            
            $table->string('tipo_item', 20); // producto, servicio
            $table->string('descripcion', 255);
            $table->string('codigo_unidad', 10)->default('NIU');
            $table->decimal('cantidad', 10, 2);
            $table->decimal('precio_unitario', 10, 2); // Con IGV
            $table->decimal('valor_unitario', 10, 2); // Sin IGV (Base)
            $table->decimal('subtotal', 10, 2); // Base * Cantidad
            $table->decimal('igv_total', 10, 2);
            $table->decimal('total', 10, 2); // Subtotal + IGV

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comprobantes_detalle');
        Schema::dropIfExists('comprobantes');
        Schema::dropIfExists('series_comprobante');
        Schema::dropIfExists('tipos_comprobante');
    }
};
