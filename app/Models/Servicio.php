<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Servicio extends Model
{
    use SoftDeletes;

    
    protected $fillable = [
        'id_categoria',
        'id_afectacion',
        'id_unidad',
        'nombre',
        'precio',
        'duracion_minutos',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'precio' => 'float',
        'duracion_minutos' => 'integer'
    ];

    //Relaciones
    public function categoria(){
        return $this->belongsTo(CategoriaServicio::class,'id_Categoria');
    }

    public function unidad(){
        return $this->belongsTo(UnidadSunat::class, 'id_unidad');
    }
    public function afectacion(){
        return $this->belongsTo(AfectacionIgv::class, 'id_afectacion');
    }
    
}
