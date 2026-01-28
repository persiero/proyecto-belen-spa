<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venta extends Model
{
    use SoftDeletes;

    protected $table = 'ventas';

    protected $fillable = [
        'id_turno',
        'id_cliente',
        'fecha',
        'op_gravadas',
        'op_exoneradas',
        'op_inafectas',
        'monto_igv',
        'total',
        'estado' // 'pagada', 'anulada'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'op_gravadas' => 'float',
        'monto_igv' => 'float',
        'total' => 'float',
    ];

    // ================= RELACIONES =================

    // Una venta pertenece a un cliente (opcional)
    public function cliente()
    {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    // Una venta puede venir de un turno
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turno');
    }

    // Una venta tiene muchos detalles (items)
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class, 'id_venta');
    }

    // Una venta tiene uno o varios pagos
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'id_venta');
    }

    // Relación para saber si ya tiene factura
    public function comprobante()
    {
        return $this->hasOne(Comprobante::class, 'id_venta');
    }

    // Relación para obtener específicamente la Nota de Crédito asociada
    public function notaCredito()
    {
        // Buscamos un comprobante que pertenezca a esta venta Y que sea tipo Nota de Crédito
        // Asumiendo que el ID del tipo "Nota de Crédito" en tu tabla 'tipos_comprobante' es 3 (según tus fotos anteriores)
        // O mejor, buscamos por código SUNAT '07' usando una subconsulta o relación avanzada, 
        // pero para hacerlo simple, usaremos el 'hasOne' filtrado.
        
        return $this->hasOne(Comprobante::class, 'id_venta')
                    ->whereHas('tipoComprobante', function($q){
                        $q->where('codigo_sunat', '07');
                    });
    }
}