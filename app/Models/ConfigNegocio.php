<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConfigNegocio extends Model
{
    // Definimos la tabla porque Laravel buscaría 'config_negocios' (plural) y tu tabla es 'config_negocio'
    protected $table = 'config_negocio';

    protected $fillable = [
        'nombre_comercial',
        'direccion',
        'telefono',
        'email',
        'ruc',
        'precio_incluye_igv'
    ];
}