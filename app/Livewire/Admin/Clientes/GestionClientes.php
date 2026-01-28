<?php

namespace App\Livewire\Admin\Clientes;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Cliente;
use Illuminate\Validation\Rule;

class GestionClientes extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $isOpen = false;

    // Campos
    public $cliente_id;
    public $nombre, $apellido, $telefono, $email, $direccion;
    public $tipo_documento, $numero_documento;

    // Reglas (Dinámicas)
    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:150',
            'apellido' => 'nullable|string|max:150',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'tipo_documento' => 'nullable|in:DNI,RUC,CE,PAS,OTRO',
            // El documento es único, excepto para el cliente que estamos editando
            'numero_documento' => [
                'nullable', 
                'string', 
                'max:20', 
                Rule::unique('clientes', 'numero_documento')->ignore($this->cliente_id)
            ],
            'direccion' => 'nullable|string|max:255',
        ];
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        // Buscamos por Nombre, Apellido o Documento
        $clientes = Cliente::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('numero_documento', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.clientes.gestion-clientes', compact('clientes'))
            ->with('titulo', 'Gestión de Clientes');
    }

    public function create() { $this->resetInputFields(); $this->openModal(); }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->cliente_id = $id;
        $this->nombre = $cliente->nombre;
        $this->apellido = $cliente->apellido;
        $this->tipo_documento = $cliente->tipo_documento;
        $this->numero_documento = $cliente->numero_documento;
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email;
        $this->direccion = $cliente->direccion;
        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        Cliente::updateOrCreate(['id' => $this->cliente_id], [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'tipo_documento' => $this->tipo_documento ?: null,
            'numero_documento' => $this->numero_documento ?: null,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
        ]);

        session()->flash('message', $this->cliente_id ? 'Cliente actualizado.' : 'Cliente registrado.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Cliente::find($id)->delete();
        session()->flash('message', 'Cliente eliminado correctamente.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->cliente_id = null;
        $this->nombre = ''; $this->apellido = '';
        $this->tipo_documento = ''; $this->numero_documento = '';
        $this->telefono = ''; $this->email = ''; $this->direccion = '';
    }
}