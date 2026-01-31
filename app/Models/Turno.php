<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Turno extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'id_cliente',
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

    // Un turno tiene muchos servicios detallados
    public function servicios() {
        return $this->hasMany(TurnoServicio::class, 'id_turno');
    }
}