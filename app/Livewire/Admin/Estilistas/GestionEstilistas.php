<?php

namespace App\Livewire\Admin\Estilistas;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Estilista;

class GestionEstilistas extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Propiedades
    public $search = '';
    public $isOpen = false;

    // Campos del Formulario
    public $estilista_id;
    public $nombre;
    public $especialidad;
    public $telefono;
    public $activo = true;

    // Reglas (Mismos campos, validación robusta)
    protected $rules = [
        'nombre' => 'required|string|max:150',
        'especialidad' => 'nullable|string|max:150',
        'telefono' => 'nullable|string|max:50',
        'activo' => 'boolean'
    ];

    #[Layout('layouts.admin')]
    #[Title('Gestión de Estilistas')]
    public function render()
    {
        $estilistas = Estilista::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('activo', 'desc') // Mostrar activos primero
            ->orderBy('nombre', 'asc')
            ->paginate(10);

        return view('livewire.admin.estilistas.gestion-estilistas', compact('estilistas'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    public function edit($id)
    {
        $estilista = Estilista::findOrFail($id);
        
        $this->estilista_id = $id;
        $this->nombre = $estilista->nombre;
        $this->especialidad = $estilista->especialidad;
        $this->telefono = $estilista->telefono;
        $this->activo = $estilista->activo ? 1 : 0; // Convertir a 1 o 0
    
        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        Estilista::updateOrCreate(['id' => $this->estilista_id], [
            'nombre' => $this->nombre,
            'especialidad' => $this->especialidad,
            'telefono' => $this->telefono,
            'activo' => $this->activo,
        ]);

        session()->flash('message', 
            $this->estilista_id ? 'Estilista actualizado correctamente.' : 'Estilista registrado exitosamente.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Estilista::find($id)->delete();
        session()->flash('message', 'Estilista eliminado correctamente.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }
    
    private function resetInputFields()
    {
        $this->estilista_id = null;
        $this->nombre = '';
        $this->especialidad = '';
        $this->telefono = '';
        $this->activo = true;
    }
}