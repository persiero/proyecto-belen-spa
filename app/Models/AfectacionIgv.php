<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AfectacionIgv extends Model
{
    use SoftDeletes;

    protected $table = 'afectaciones_igv';

    protected $fillable = [
        'codigo',
        'descripcion',
        'gravado',
        'porcentaje'
    ];

    protected $casts = [
        'gravado' => 'boolean', 
        'porcentaje' => 'float'
    ];

}
