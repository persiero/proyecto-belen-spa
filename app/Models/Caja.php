<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja'; // Singular según tu SQL

    protected $fillable = [
        'fecha_apertura',
        'fecha_cierre',
        'monto_apertura',
        'monto_cierre',
        'monto_real',
        'diferencia',
        'estado', // 'abierta', 'cerrada'
        'id_usuario_apertura',
        'id_usuario_cierre'
    ];

    protected $casts = [
        'fecha_apertura' => 'datetime',
        'fecha_cierre' => 'datetime',
        'monto_apertura' => 'float',
        'monto_cierre' => 'float',
    ];

    public function usuarioApertura() {
        return $this->belongsTo(User::class, 'id_usuario_apertura');
    }
}