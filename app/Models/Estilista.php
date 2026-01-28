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

    // RELACIÓN NUEVA: Traer solo los servicios que están cursando en turnos ACTIVOS
    public function atencionesEnCurso()
    {
        return $this->hasMany(TurnoServicio::class, 'id_estilista')
                    ->whereHas('turno', function ($query) {
                        $query->where('estado', 'activo');
                    })
                    ->with(['turno.cliente', 'servicio']); 
                    // Traemos el turno y el cliente para mostrar "Atendiendo a Juan Perez"
    }
}
