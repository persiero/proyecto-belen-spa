<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComprobanteDetalle extends Model
{
    protected $table = 'comprobantes_detalle';

    protected $fillable = [
        'id_comprobante',
        'tipo_item',        // 'producto' o 'servicio'
        'descripcion',
        'codigo_unidad',    // 'NIU', 'ZZ'
        'cantidad',
        'precio_unitario',  // Con IGV
        'valor_unitario',   // Sin IGV
        'subtotal',         // Base imponible
        'igv_total',
        'total'             // Importe final de la línea
    ];

    protected $casts = [
        'cantidad' => 'float',
        'precio_unitario' => 'float',
        'valor_unitario' => 'float',
        'subtotal' => 'float',
        'igv_total' => 'float',
        'total' => 'float',
    ];

    // Relación inversa: Pertenece a un Comprobante
    public function comprobante()
    {
        return $this->belongsTo(Comprobante::class, 'id_comprobante');
    }
}