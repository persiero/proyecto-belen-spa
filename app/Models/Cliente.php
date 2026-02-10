<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'nombre',
        'apellido',
        'tipo_documento',
        'numero_documento',
        'fecha_nacimiento',
        'genero',
        'telefono',
        'email',
        'direccion',
        'procedencia'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
    ];

    // Relaciones
    public function turnos()
    {
        return $this->hasMany(Turno::class, 'id_cliente');
    }

    public function ventas()
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }
}
