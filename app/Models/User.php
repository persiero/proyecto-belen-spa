<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Testing\Fluent\Concerns\Has;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'usuarios';
   

    protected $fillable = [
        'id_rol',
        'nombre',
        'email',
        'password',
        'activo'
    ];

    
    protected $hidden = [
        'password',
        'remember_token',
    ];

    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'activo' => 'boolean',
    ];

    public function rol(){
        return $this->belongsTo(Rol::class, 'id_rol');
    }

    // Métodos helper para verificar roles
    public function isAdministrador(){
        return $this->rol && strtolower($this->rol->nombre) === 'administrador';
    }

    public function isCajero(){
        return $this->rol && strtolower($this->rol->nombre) === 'cajero';
    }

    public function isEncargado(){
        return $this->rol && strtolower($this->rol->nombre) === 'encargado';
    }

    // Método para verificar si tiene acceso a módulos de sistema
    public function canAccessSistema(){
        return $this->isAdministrador();
    }

    // Método para verificar si tiene acceso a estadísticas
    public function canAccessEstadisticas(){
        return $this->isAdministrador();
    }

    // Método para verificar si tiene acceso a almacén
    public function canAccessAlmacen(){
        return $this->isAdministrador() || $this->isEncargado();
    }
}
