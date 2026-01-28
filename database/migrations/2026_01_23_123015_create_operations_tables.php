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
        // 10. PRODUCTOS E INSUMOS
        Schema::create('productos', function (Blueprint $table) {
            $table->id();
            $table->enum('tipo', ['venta','insumo','mixto'])->default('venta');
            $table->string('nombre', 150)->index();
            $table->string('descripcion', 255)->nullable();
            
            // FKs Sunat
            $table->foreignId('id_afectacion')->constrained('afectaciones_igv');
            $table->foreignId('id_unidad')->constrained('unidades_sunat');

            $table->decimal('costo_compra', 10, 2)->default(0.00);
            $table->decimal('precio_venta', 10, 2)->nullable(); // Null para insumos puros
            
            $table->integer('stock_actual')->default(0);
            $table->integer('stock_minimo')->default(0);
            $table->string('codigo_barras', 50)->nullable()->index();
            $table->boolean('activo')->default(1);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 11 y 12. CONFIGURACIONES (Negocio y Tributaria)
        Schema::create('config_negocio', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_comercial', 255);
            $table->string('direccion', 255);
            $table->string('telefono', 50)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('ruc', 11)->nullable();
            $table->boolean('precio_incluye_igv')->default(1);
            $table->timestamps();
        });

        Schema::create('config_tributaria', function (Blueprint $table) {
            $table->id();
            $table->decimal('igv_porcentaje', 5, 2)->default(18.00);
            $table->boolean('emision_automatica_cpe')->default(0);
            $table->timestamps();
        });

        // --- FASE OPERATIVA ---

        // TURNOS
        Schema::create('turnos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_cliente')->constrained('clientes');
            $table->foreignId('id_estilista')->nullable()->constrained('estilistas'); // Estilista principal
            
            $table->dateTime('hora_inicio');
            $table->dateTime('hora_fin')->nullable();
            $table->enum('estado', ['activo','cerrado','cancelado'])->default('activo');
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // TURNO SERVICIOS (Detalle del turno antes de la venta)
        Schema::create('turno_servicios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_turno')->constrained('turnos')->onDelete('cascade');
            $table->foreignId('id_servicio')->constrained('servicios');
            $table->foreignId('id_estilista')->nullable()->constrained('estilistas'); // Override por servicio
            
            $table->decimal('precio_aplicado', 10, 2);
            $table->timestamps();
            $table->softDeletes();
        });

        // VENTAS (Cabecera)
        Schema::create('ventas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_turno')->nullable()->constrained('turnos');
            $table->foreignId('id_cliente')->nullable()->constrained('clientes');
            
            $table->dateTime('fecha')->index();
            
            // Snapshots Tributarios
            $table->decimal('op_gravadas', 10, 2)->default(0.00);
            $table->decimal('op_exoneradas', 10, 2)->default(0.00);
            $table->decimal('op_inafectas', 10, 2)->default(0.00);
            $table->decimal('monto_igv', 10, 2)->default(0.00);
            $table->decimal('total', 10, 2);
            
            $table->enum('estado', ['pagada','anulada'])->default('pagada');
            $table->timestamps();
            $table->softDeletes();
        });

        // DETALLES DE VENTA (Items)
        Schema::create('detalles_venta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade');
            
            $table->enum('tipo_item', ['servicio','producto']);
            
            $table->foreignId('id_servicio')->nullable()->constrained('servicios');
            $table->foreignId('id_producto')->nullable()->constrained('productos');
            
            // Snapshot UBL (Datos fijos al momento de vender)
            $table->string('nombre_item', 255);
            $table->string('codigo_afectacion_igv', 5);
            $table->decimal('porcentaje_igv', 5, 2);
            $table->string('codigo_unidad', 10);
            
            $table->integer('cantidad')->default(1);
            $table->decimal('valor_unitario', 10, 2); // Sin IGV
            $table->decimal('precio_unitario', 10, 2); // Con IGV
            
            $table->decimal('igv_total', 10, 2);
            $table->decimal('subtotal', 10, 2); // (Precio * Cantidad)
            
            $table->timestamps();
        });

        // PAGOS
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_venta')->constrained('ventas')->onDelete('cascade');
            $table->foreignId('id_metodo_pago')->constrained('metodos_pago');
            
            $table->decimal('monto', 10, 2);
            $table->string('referencia', 100)->nullable();
            $table->dateTime('fecha');
            
            $table->timestamps();
        });

        // CAJA
        Schema::create('caja', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_cierre')->nullable();
            
            $table->decimal('monto_apertura', 10, 2)->default(0.00);
            $table->decimal('monto_cierre', 10, 2)->nullable();
            
            $table->enum('estado', ['abierta','cerrada'])->default('abierta')->index();
            
            // Usuarios (Asumiendo tabla 'usuarios' personalizada)
            $table->foreignId('id_usuario_apertura')->constrained('usuarios');
            $table->foreignId('id_usuario_cierre')->nullable()->constrained('usuarios');
            
            $table->timestamps();
        });

        // COMPRAS
        Schema::create('compras', function (Blueprint $table) {
            $table->id();
            $table->dateTime('fecha');
            $table->enum('tipo_documento', ['ticket','boleta','factura','sin_documento'])->default('sin_documento');
            $table->string('numero_documento', 100)->nullable();
            $table->decimal('costo_total', 10, 2);
            $table->text('observaciones')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // DETALLE COMPRAS
        Schema::create('compras_detalle', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_compra')->constrained('compras')->onDelete('cascade');
            $table->foreignId('id_producto')->constrained('productos');
            $table->integer('cantidad');
            $table->decimal('costo_unitario', 10, 2);
            $table->decimal('costo_total', 10, 2);
            $table->timestamps();
        });

        // MOVIMIENTOS INVENTARIO
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_producto')->constrained('productos');
            $table->enum('tipo', ['entrada','salida_venta','salida_insumo','ajuste']);
            $table->integer('cantidad'); // Puede ser positivo o negativo según lógica, o absoluto y el tipo define el signo
            $table->string('referencia', 100)->nullable();
            $table->string('motivo', 255)->nullable();
            $table->dateTime('fecha')->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // El orden inverso es importante por las FK
        Schema::dropIfExists('movimientos_inventario');
        Schema::dropIfExists('compras_detalle');
        Schema::dropIfExists('compras');
        Schema::dropIfExists('caja');
        Schema::dropIfExists('pagos');
        Schema::dropIfExists('detalles_venta');
        Schema::dropIfExists('ventas');
        Schema::dropIfExists('turno_servicios');
        Schema::dropIfExists('turnos');
        Schema::dropIfExists('config_tributaria');
        Schema::dropIfExists('config_negocio');
        Schema::dropIfExists('productos');
    }
};
