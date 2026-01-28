<?php

namespace App\Livewire\Admin\Proveedores;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Proveedor;

class GestionProveedores extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $isOpen = false;

    // Campos
    public $proveedor_id;
    public $nombre_empresa, $ruc_dni, $telefono, $email, $contacto, $direccion;
    public $activo = true;

    protected $rules = [
        'nombre_empresa' => 'required|string|max:150',
        'ruc_dni' => 'nullable|string|max:20', // Podrías agregar unique:proveedores,ruc_dni con ignore
        'telefono' => 'nullable|string|max:50',
        'email' => 'nullable|email',
        'contacto' => 'nullable|string|max:100',
        'direccion' => 'nullable|string|max:255',
        'activo' => 'boolean'
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        $proveedores = Proveedor::where('nombre_empresa', 'like', '%' . $this->search . '%')
            ->orWhere('ruc_dni', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.proveedores.gestion-proveedores', compact('proveedores'))
            ->with('titulo', 'Directorio de Proveedores');
    }

    public function create() { $this->resetInputFields(); $this->openModal(); }

    public function edit($id)
    {
        $p = Proveedor::findOrFail($id);
        $this->proveedor_id = $id;
        $this->nombre_empresa = $p->nombre_empresa;
        $this->ruc_dni = $p->ruc_dni;
        $this->telefono = $p->telefono;
        $this->email = $p->email;
        $this->contacto = $p->contacto;
        $this->direccion = $p->direccion;
        $this->activo = $p->activo;
        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        Proveedor::updateOrCreate(['id' => $this->proveedor_id], [
            'nombre_empresa' => $this->nombre_empresa,
            'ruc_dni' => $this->ruc_dni,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'contacto' => $this->contacto,
            'direccion' => $this->direccion,
            'activo' => $this->activo
        ]);

        session()->flash('message', $this->proveedor_id ? 'Proveedor actualizado.' : 'Proveedor registrado.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Proveedor::find($id)->delete();
        session()->flash('message', 'Proveedor eliminado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->proveedor_id = null;
        $this->nombre_empresa = ''; $this->ruc_dni = '';
        $this->telefono = ''; $this->email = '';
        $this->contacto = ''; $this->direccion = '';
        $this->activo = true;
    }
}