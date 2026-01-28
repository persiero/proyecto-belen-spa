<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turno extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id_cliente',
        'id_estilista', // Estilista principal (opcional)
        'hora_inicio',
        'hora_fin',
        'estado',       // activo, cerrado, cancelado
        'observaciones'
    ];

    protected $casts = [
        'hora_inicio' => 'datetime',
        'hora_fin' => 'datetime',
    ];

    // Relaciones
    public function cliente() {
        return $this->belongsTo(Cliente::class, 'id_cliente');
    }

    public function estilista() {
        return $this->belongsTo(Estilista::class, 'id_estilista');
    }

    // Un turno tiene muchos servicios detallados
    public function servicios() {
        return $this->hasMany(TurnoServicio::class, 'id_turno');
    }
}