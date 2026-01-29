<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Estilista extends Model
{
    use SoftDeletes;
    
    protected $table = 'estilistas';

    protected $fillable = ['nombre', 'especialidad', 'telefono', 'activo'];
    
    protected $casts = ['activo' => 'boolean'];

    // RELACIÓN: Atenciones en curso
    public function atencionesEnCurso()
    {
        return $this->hasMany(TurnoServicio::class, 'id_estilista')
                    ->whereHas('turno', function ($query) {
                        $query->where('estado', 'activo');
                    })
                    ->with(['turno.cliente', 'servicio']); 
    }
}