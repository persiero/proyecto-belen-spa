<?php

namespace App\Livewire\Admin\Perfil;

use Livewire\Component;
use Livewire\WithFileUploads; // Necesario para subir fotos
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class MiPerfil extends Component
{
    use WithFileUploads;

    public $nombre, $email, $password, $foto;
    public $foto_actual; // Para mostrar la que ya tiene

    public function mount()
    {
        $user = Auth::user();
        $this->nombre = $user->nombre; // Ojo: tu columna es 'nombre'
        $this->email = $user->email;
        $this->foto_actual = $user->foto_perfil;
    }

    public function actualizar()
    {
        $user = User::find(Auth::id());

        $this->validate([
            'nombre' => 'required|string|max:255',
            'email' => 'required|email|unique:usuarios,email,' . $user->id,
            'password' => 'nullable|min:6',
            'foto' => 'nullable|image|max:2048', // Máx 2MB
        ]);

        // 1. Actualizar datos básicos
        $user->nombre = $this->nombre;
        $user->email = $this->email;

        // 2. Si escribió contraseña, la actualizamos
        if (!empty($this->password)) {
            $user->password = Hash::make($this->password);
        }

        // 3. Lógica de la Foto
        if ($this->foto) {
            // Borrar anterior si existe
            if ($user->foto_perfil && Storage::disk('public')->exists($user->foto_perfil)) {
                Storage::disk('public')->delete($user->foto_perfil);
            }

            // Guardar nueva
            $path = $this->foto->store('perfiles', 'public');
            $user->foto_perfil = $path;
        }

        $user->save();

        // Recargar datos para mostrar la nueva foto
        $this->foto_actual = $user->foto_perfil;
        $this->foto = null; // Limpiar input file
        $this->password = ''; // Limpiar password por seguridad

        session()->flash('message', 'Perfil actualizado correctamente.');

        // Emitir evento para que el Navbar se actualice (opcional, requiere recarga por ahora)
        return redirect()->route('admin.perfil'); 
    }

    // 2. USAR ATRIBUTOS PHP 8 (Más limpio y sin errores de VS Code)
    #[Layout('layouts.admin')]
    #[Title('Mi Perfil')]
    public function render()
    {
        // 3. RETURN LIMPIO
        return view('livewire.admin.perfil.mi-perfil');
    }
}