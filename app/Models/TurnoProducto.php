<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TurnoProducto extends Model
{
    // Si usas softDeletes en la migración, es buena práctica usarlo aquí también
    use SoftDeletes;

    protected $table = 'turno_productos';

    protected $fillable = [
        'id_turno',
        'id_producto',
        'id_estilista', // Importante para saber quién vendió el producto
        'cantidad',
        'precio',       // Precio unitario aplicado
    ];

    protected $casts = [
        'precio' => 'float',
        'cantidad' => 'integer',
    ];

    // ==========================================
    // RELACIONES
    // ==========================================

    // 1. Relación con el Turno (Padre)
    public function turno()
    {
        return $this->belongsTo(Turno::class, 'id_turno');
    }

    // 2. Relación con el Producto (Catálogo)
    public function producto()
    {
        return $this->belongsTo(Producto::class, 'id_producto')->withTrashed();
    }

    // 3. Relación con el Estilista (Vendedor)
    public function estilista()
    {
        return $this->belongsTo(Estilista::class, 'id_estilista');
    }
}
