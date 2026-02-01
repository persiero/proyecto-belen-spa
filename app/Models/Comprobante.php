<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comprobante extends Model
{
    protected $table = 'comprobantes';

    protected $fillable = [
        // Claves Foráneas
        'id_venta',
        'id_tipo_comprobante',
        'id_serie_comprobante',

        // Datos de Emisión
        'serie',
        'correlativo',
        'fecha_emision',

        // --- CAMPOS NUEVOS (AGREGAR ESTOS) ---
        'id_comprobante_ref',      // <--- Faltaba
        'cod_motivo_nc',           // <--- Faltaba
        'descripcion_motivo_nc',   // <--- Faltaba
        'leyenda_sunat',           // <--- Faltaba
        'forma_pago',              // <--- Faltaba
        // -------------------------------------

        // Snapshot del Receptor (Cliente)
        'receptor_tipo_doc',
        'receptor_numero_doc',
        'receptor_razon_social',
        'receptor_direccion',

        // Montos Tributarios
        'op_gravadas',
        'op_exoneradas',
        'op_inafectas',
        'monto_igv',
        'total',
        'moneda',

        // Respuesta SUNAT / Archivos
        'nombre_xml',
        'hash_cpe',
        'estado_sunat',     // emitido, aceptado, rechazado
        'mensaje_sunat',
        'cdr_xml',
        'ruta_pdf',
        'enviado_sunat'
    ];

    protected $casts = [
        'fecha_emision' => 'datetime',
        'enviado_sunat' => 'boolean',
        'total' => 'float',
        'monto_igv' => 'float',
        'op_gravadas' => 'float',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }

    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'id_tipo_comprobante');
    }

    public function detalles()
    {
        return $this->hasMany(ComprobanteDetalle::class, 'id_comprobante');
    }

    public function serieComprobante()
    {
        return $this->belongsTo(SerieComprobante::class, 'id_serie_comprobante');
    }

    // Relación para Nota de Crédito (Saber cuál es el padre)
    public function comprobanteReferencia()
    {
        return $this->belongsTo(Comprobante::class, 'id_comprobante_ref');
    }
}