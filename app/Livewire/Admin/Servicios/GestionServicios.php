<?php

namespace App\Livewire\Admin\Servicios;

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Servicio;
use App\Models\CategoriaServicio;
use App\Models\UnidadSunat;
use App\Models\AfectacionIgv;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class GestionServicios extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    // Variables
    public $search = '';
    public $isOpen = false;
    
    // Campos
    public $servicio_id, $nombre, $precio, $duracion_minutos;
    public $id_categoria, $id_unidad, $id_afectacion;
    public $activo = true;

    // Reglas (Categoría ahora es nullable)
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
    #[Title('Gestión de Servicios')]
    public function render()
    {
        $servicios = Servicio::where('nombre', 'like', '%' . $this->search . '%')
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Cargamos catálogos (Cache simple o consulta directa)
        $categorias = CategoriaServicio::where('activo', true)->get();
        $unidades = UnidadSunat::all();
        $afectaciones = AfectacionIgv::where('gravado', true)->get();

        return view('livewire.admin.servicios.gestion-servicios', compact('servicios', 'categorias', 'unidades', 'afectaciones'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->openModal();
    }

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

    public function store()
    {
        $this->validate();

        Servicio::updateOrCreate(['id' => $this->servicio_id], [
            'nombre' => $this->nombre,
            'precio' => $this->precio,
            'duracion_minutos' => $this->duracion_minutos,
            // Importante: Si viene vacío, guardamos NULL
            'id_categoria' => $this->id_categoria == "" ? null : $this->id_categoria,
            'id_unidad' => $this->id_unidad,
            'id_afectacion' => $this->id_afectacion,
            'activo' => $this->activo,
        ]);

        session()->flash('message', 
            $this->servicio_id ? 'Servicio actualizado correctamente.' : 'Servicio creado exitosamente.'
        );

        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Servicio::find($id)->delete();
        session()->flash('message', 'Servicio eliminado correctamente.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }
    
    private function resetInputFields()
    {
        $this->servicio_id = null;
        $this->nombre = '';
        $this->precio = '';
        $this->duracion_minutos = '';
        $this->id_categoria = null; // Por defecto nulo
        // Valores por defecto seguros (Asumiendo que existen en tu BD)
        $this->id_unidad = UnidadSunat::first()->id ?? 1; 
        $this->id_afectacion = AfectacionIgv::where('codigo', '10')->first()->id ?? 1;
        $this->activo = true;
    }
}