<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    protected $table = 'pagos';

    protected $fillable = [
        'id_venta',
        'id_metodo_pago',
        'monto',
        'referencia', // Nro de operación, voucher, etc.
        'fecha',
        'confirmado' // Boolean (T3: Validación de transferencia)
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'monto' => 'float',
        'confirmado' => 'boolean',
    ];

    public function metodoPago()
    {
        return $this->belongsTo(MetodoPago::class, 'id_metodo_pago');
    }

    public function venta()
    {
        return $this->belongsTo(Venta::class, 'id_venta');
    }
}