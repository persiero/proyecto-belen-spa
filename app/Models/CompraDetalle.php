<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CompraDetalle extends Model
{
    protected $table = 'compras_detalle'; // Singular/Plural cuidado

    protected $fillable = [
        'id_compra',
        'id_producto',
        'cantidad',
        'costo_unitario',
        'costo_total'
    ];

    public function producto() {
        return $this->belongsTo(Producto::class, 'id_producto');
    }
}