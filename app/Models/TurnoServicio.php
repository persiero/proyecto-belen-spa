<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TurnoServicio extends Model
{
    protected $table = 'turno_servicios';

    protected $fillable = [
        'id_turno',
        'id_servicio',
        'id_estilista', // Importante para saber a quién pagarle
        'precio_aplicado',
        // 'comision_generada' (Opcional si decidimos guardarlo fijo)
    ];

    protected $casts = [
        'precio_aplicado' => 'float',
    ];

    // ==========================================
    // RELACIONES (Esto es lo que te faltaba)
    // ==========================================

    // 1. Relación con el Turno (Padre)
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turno');
    }

    // 2. Relación con el Servicio (Catálogo)
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'id_servicio')->withTrashed();
    }

    // 3. Relación con el Estilista (Quién lo hizo)
    public function estilista()
    {
        return $this->belongsTo(Estilista::class, 'id_estilista');
    }
}
