<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SerieComprobante extends Model
{
    protected $table = 'series_comprobante';

    protected $fillable = [
        'id_tipo_comprobante',
        'serie',               // 'F001', 'B001'
        'correlativo_actual',  // 1500
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'correlativo_actual' => 'integer',
    ];

    // Relación inversa: Pertenece a un Tipo de Comprobante
    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'id_tipo_comprobante');
    }
}