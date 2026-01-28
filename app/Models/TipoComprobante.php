<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoComprobante extends Model
{
    protected $table = 'tipos_comprobante';

    protected $fillable = [
        'codigo_sunat',       // '01', '03', '07'
        'descripcion',        // 'Factura Electrónica'
        'requiere_cliente_doc' // true/false
    ];

    protected $casts = [
        'requiere_cliente_doc' => 'boolean',
    ];

    // Relación: Un tipo tiene muchas series (Ej: Factura puede tener F001, F002)
    public function series()
    {
        return $this->hasMany(SerieComprobante::class, 'id_tipo_comprobante');
    }
}