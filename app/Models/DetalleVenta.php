<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    protected $table = 'detalles_venta';

    protected $fillable = [
        'id_venta',
        'tipo_item', // 'servicio', 'producto'
        'id_servicio',
        'id_producto',
        'id_estilista',
        'nombre_item',
        'codigo_afectacion_igv',
        'porcentaje_igv',
        'codigo_unidad',
        'cantidad',
        'valor_unitario', // Precio SIN IGV
        'precio_unitario', // Precio CON IGV
        'igv_total',
        'subtotal'
    ];

    protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'float',
        'subtotal' => 'float',
    ];

    // Relaciones para saber qué se vendió originalmente
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio')->withTrashed();
    }

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto')->withTrashed();
    }
    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}
