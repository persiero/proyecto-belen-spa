<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Compra extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id_proveedor', // Nuevo campo
        'fecha',
        'tipo_documento', // boleta, factura, ticket, sin_documento
        'numero_documento',
        'costo_total',
        'observaciones'
    ];

    protected $casts = [
        'fecha' => 'datetime',
        'costo_total' => 'float',
    ];

    // Relaciones
    public function proveedor() {
        return $this->belongsTo(Proveedor::class, 'id_proveedor');
    }

    public function detalles() {
        return $this->hasMany(CompraDetalle::class, 'id_compra');
    }
}