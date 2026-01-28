<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class MovimientoCaja extends Model
{
    use HasFactory;

    protected $table = 'movimientos_caja';

    protected $fillable = [
        'id_caja',
        'tipo',
        'monto',
        'descripcion',
        'id_usuario',
    ];

    public function usuario() { return $this->belongsTo(User::class, 'id_usuario'); }
}
