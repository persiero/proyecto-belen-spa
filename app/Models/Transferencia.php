<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transferencia extends Model
{
    protected $table = 'transferencias';

    protected $fillable = [
        'id_producto',
        'origen',   // 'venta', 'insumo'
        'destino',  // 'venta', 'insumo'
        'cantidad',
        'fecha',
        'observaciones'
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