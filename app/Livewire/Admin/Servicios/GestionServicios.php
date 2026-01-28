<?php

namespace App\Livewire\Admin\Servicios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Servicio;
use App\Models\CategoriaServicio;
use App\Models\UnidadSunat;
use App\Models\AfectacionIgv;
use Livewire\Attributes\Layout;

class GestionServicios extends Component
{
    use WithPagination;

    // Configuración de paginación para Bootstrap
    protected $paginationTheme = 'bootstrap';

    // Variables de Búsqueda y Modal
    public $search = '';
    public $isOpen = false;
    public $confirmingDeletion = false;

    // Campos del Formulario (Modelos)
    public $servicio_id;
    public $nombre;
    public $precio;
    public $duracion_minutos;
    public $id_categoria;
    public $id_unidad;
    public $id_afectacion;
    public $activo = true;

    // Reglas de Validación
    protected $rules = [
        'nombre' => 'required|string|max:150',
        'precio' => 'required|numeric|min:0',
        'duracion_minutos' => 'nullable|integer|min:0',
        'id_categoria' => 'nullable|exists:categorias_servicio,id',
        'id_unidad' => 'required|exists:unidades_sunat,id',
        'id_afectacion' => 'required|exists:afectaciones_igv,id',
        'activo' => 'boolean'
    ];

    #[Layout('layouts.admin')]
    public function render()
    {
        // Traemos los servicios filtrados
        $servicios = Servicio::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Cargamos los catálogos para los <select> del modal
        $categorias = CategoriaServicio::where('activo', true)->get();
        $unidades = UnidadSunat::all();
        $afectaciones = AfectacionIgv::where('gravado', true)->get(); // Priorizamos las gravadas

        return view('livewire.admin.servicios.gestion-servicios', compact('servicios', 'categorias', 'unidades', 'afectaciones'))
            ->with('titulo', 'Gestión de Servicios');
    }

    // Método para abrir el modal de CREAR
    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

    // Método para abrir el modal de EDITAR
    public function edit($id)
    {
        $servicio = Servicio::findOrFail($id);
        
        $this->servicio_id = $id;
        $this->nombre = $servicio->nombre;
        $this->precio = $servicio->precio;
        $this->duracion_minutos = $servicio->duracion_minutos;
        $this->id_categoria = $servicio->id_categoria;
        $this->id_unidad = $servicio->id_unidad;
        $this->id_afectacion = $servicio->id_afectacion;
        $this->activo = $servicio->activo;
    
        $this->openModal();
    }

    // Método Guardar (Sirve para Crear y Actualizar)
    public function store()
    {
        $this->validate();

        Servicio::updateOrCreate(['id' => $this->servicio_id], [
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'duracion_minutos' => $this->duracion_minutos,
            'id_categoria' => $this->id_categoria ?: null, // Si está vacío guarda NULL
            'id_unidad' => $this->id_unidad,
            'id_afectacion' => $this->id_afectacion,
            'activo' => $this->activo,
        ]);

        session()->flash('message', 
            $this->servicio_id ? 'Servicio actualizado correctamente.' : 'Servicio creado correctamente.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    // Método Eliminar
    public function delete($id)
    {
        Servicio::find($id)->delete();
        session()->flash('message', 'Servicio eliminado correctamente.');
    }

    // Funciones Auxiliares
    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }
    
    private function resetInputFields()
    {
        $this->servicio_id = null;
        $this->nombre = '';
        $this->precio = '';
        $this->duracion_minutos = '';
        $this->id_categoria = '';
        // Valores por defecto útiles para Sunat
        $this->id_unidad = 1; // ZZ (Servicio) - Ajusta según tu ID real en la BD
        $this->id_afectacion = 1; // 10 (Gravado) - Ajusta según tu ID real
        $this->activo = true;
    }
}