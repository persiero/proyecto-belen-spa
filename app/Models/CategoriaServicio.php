<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaServicio extends Model
{
    use SoftDeletes;

    protected $table = 'categorias_servicio';

    protected $fillable = [
        'nombre',
        'descripcion',
        'activo'        
    ];

    protected $casts = [
        'activo' => 'boolean'
    ];

    //Relación con Servicios
    public function servicios(){
        return $this->hasMany(Servicio::class, 'id_categoria');
    }

}
