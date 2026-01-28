<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UnidadSunat extends Model
{
    use SoftDeletes;

    protected $table = 'unidades_sunat';

    protected $fillable = [
        'codigo',
        'descripcion'
    ];
}
