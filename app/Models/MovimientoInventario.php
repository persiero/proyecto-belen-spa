<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MovimientoInventario extends Model
{
    protected $table = 'movimientos_inventario';

    protected $fillable = [
        'id_producto',
        'tipo', // 'entrada', 'salida_venta', 'salida_insumo', 'ajuste'
        'cantidad',
        'referencia', // Nro de venta, compra, o nota manual
        'motivo',     // Explicación textual
        'fecha'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'cantidad' => 'integer',
    ];

    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}