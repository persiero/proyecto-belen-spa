<?php

namespace App\Livewire\Admin\Usuarios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\User;
use App\Models\Rol;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class GestionUsuarios extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    // Propiedades del Formulario
    public $user_id;
    public $nombre, $email, $password, $id_rol, $activo = 1;
    
    // Filtros
    public $search = '';

    // Reglas de Validación
    protected function rules()
    {
        return [
            'nombre' => 'required|min:3',
            'email' => 'required|email|unique:usuarios,email,' . $this->user_id,
            // CORRECCIÓN: Asegúrate que la tabla en BD se llame 'roles' o 'rol'. 
            // Si tu tabla es 'roles', pon 'exists:roles,id'.
            'id_rol' => 'required|exists:roles,id', 
            'password' => $this->user_id ? 'nullable|min:6' : 'required|min:6', 
        ];
    }

    #[Layout('layouts.admin')]
    #[Title('Gestión de Usuarios')]
    public function render()
    {
        $users = User::with('rol')
            ->where('nombre', 'like', '%' . $this->search . '%')
            ->orWhere('email', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        // CORRECCIÓN: Guardamos en variable plural y la pasamos a la vista
        $roles = Rol::all();

        // CORRECCIÓN: Agregamos 'roles' al compact
        return view('livewire.admin.usuarios.gestion-usuarios', compact('users', 'roles'));
    }

    // Resetear formulario
    public function resetInput()
    {
        $this->nombre = '';
        $this->email = '';
        $this->password = '';
        $this->id_rol = '';
        $this->activo = 1;
        $this->user_id = null;
        $this->resetErrorBag();
    }

    // Guardar (Crear o Editar)
    public function save()
    {
        $this->validate();

        // CORRECCIÓN: Usamos 'activo' que es el nombre real en tu BD
        $data = [
            'nombre' => $this->nombre,
            'email' => $this->email,
            'id_rol' => $this->id_rol,
            'activo' => $this->activo 
        ];

        // 1. Si escribieron password, lo encriptamos y agregamos al array
        if (!empty($this->password)) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->user_id) {
            // --- EDICIÓN ---
            $user = User::find($this->user_id);
            
            // CORRECCIÓN LÓGICA:
            // Si el password ESTÁ VACÍO, lo quitamos del array para no sobreescribirlo
            if (empty($this->password)) {
                unset($data['password']);
            }
            
            $user->update($data);
            $mensaje = 'Usuario actualizado correctamente.';

        } else {
            // --- CREACIÓN ---
            User::create($data);
            $mensaje = 'Usuario creado correctamente.';
        }

        $this->dispatch('close-modal');
        session()->flash('success', $mensaje);
        $this->resetInput();
    }

    // Cargar datos para editar
    public function edit($id)
    {
        $user = User::find($id);
        
        // Pequeña validación extra por seguridad
        if (!$user) return;
        
        $this->user_id = $user->id;
        $this->nombre = $user->nombre;
        $this->email = $user->email;
        $this->id_rol = $user->id_rol;
        $this->activo = $user->activo;
        $this->password = ''; 
    }

    // Eliminar
    public function delete($id)
    {
        if ($id == Auth::id()) {
            session()->flash('error', 'No puedes eliminar tu propia cuenta.');
            return;
        }

        User::find($id)->delete();
        session()->flash('success', 'Usuario eliminado correctamente.');
    }
}