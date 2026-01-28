<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rol extends Model
{
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'name',
        'description'
    ];

    //Relación inversa (Opcional, pero util para el scope)
    public function usuarios(){
        return $this->hasMany(User::class, 'id_rol');
    }
}
