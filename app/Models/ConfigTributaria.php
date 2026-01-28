<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigTributaria extends Model
{
    protected $table = 'config_tributaria';

    protected $fillable = [
        'igv_porcentaje',
        'emision_automatica_cpe',
        // Nuevos campos para Facturación Electrónica (SUNAT)
        'usuario_sol',
        'clave_sol',
        'certificado_path',
        'certificado_password',
        'modo' // 'beta' o 'produccion'
    ];

    protected $casts = [
        'igv_porcentaje' => 'float',
        'emision_automatica_cpe' => 'boolean',
    ];
    
    // Opcional: Ocultar datos sensibles si conviertes el modelo a JSON
    protected $hidden = [
        'clave_sol',
        'certificado_password',
    ];
}