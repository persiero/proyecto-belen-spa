<?php

namespace App\Livewire\Admin\Productos;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Producto;
use App\Models\UnidadSunat;
use App\Models\AfectacionIgv;

class GestionProductos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $isOpen = false;

    // Campos
    public $producto_id;
    public $tipo = 'venta'; // venta, insumo, mixto
    public $nombre, $descripcion, $codigo_barras;
    public $costo_compra = 0.00;
    public $precio_venta = 0.00;
    public $stock_actual = 0;
    public $stock_insumo = 0; // <--- NUEVO CAMPO
    public $stock_minimo = 5;
    public $id_afectacion, $id_unidad;
    public $activo = true;

    // Reglas dinámicas
    protected function rules()
    {
        return [
            'tipo' => 'required|in:venta,insumo,mixto',
            'nombre' => 'required|string|max:150',
            'codigo_barras' => 'nullable|string|max:50',
            'costo_compra' => 'required|numeric|min:0',
            // El precio de venta es obligatorio solo si NO es insumo puro
            'precio_venta' => $this->tipo === 'insumo' ? 'nullable|numeric' : 'required|numeric|min:0',
            'stock_actual' => 'nullable|integer|min:0', // Ahora es nullable pq puede ser 0
            'stock_insumo' => 'nullable|integer|min:0', // <--- NUEVO
            'stock_minimo' => 'required|integer|min:0',
            'id_afectacion' => 'required|exists:afectaciones_igv,id',
            'id_unidad' => 'required|exists:unidades_sunat,id',
            'activo' => 'boolean'
        ];
    }

    #[Layout('layouts.admin')]
    #[Title('Gestión de Productos')]
    public function render()
    {
        $productos = Producto::where(function($query) {
                // Agrupamos la búsqueda para que el OR no rompa futuros filtros
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo_barras', 'like', '%' . $this->search . '%');
            })
            // Si algún día quieres filtrar inactivos, solo agregarías aquí: ->where('activo', true)
            ->orderBy('id', 'desc') // Mantiene los más nuevos arriba
            ->paginate(10);

        $afectaciones = AfectacionIgv::where('gravado', true)->get(); // Prioridad gravadas
        $unidades = UnidadSunat::all();

        return view('livewire.admin.productos.gestion-productos', compact('productos', 'afectaciones', 'unidades'))
            ->with('titulo', 'Gestión de Productos');
    }

    public function create() { $this->resetInputFields(); $this->openModal(); }

    public function edit($id)
    {
        $p = Producto::findOrFail($id);
        $this->producto_id = $id;

        // --- MAGIA DINÁMICA: Ajustar el "tipo" según el stock real ---
        $tipoReal = $p->tipo;
        if ($p->stock_actual > 0 && $p->stock_insumo == 0) {
            $tipoReal = 'venta';
        } elseif ($p->stock_insumo > 0 && $p->stock_actual == 0) {
            $tipoReal = 'insumo';
        } elseif ($p->stock_actual > 0 && $p->stock_insumo > 0) {
            $tipoReal = 'mixto';
        }

        $this->tipo = $tipoReal; // Asignamos el tipo calculado
        // -------------------------------------------------------------

        $this->nombre = $p->nombre;
        $this->descripcion = $p->descripcion;
        $this->codigo_barras = $p->codigo_barras;
        $this->costo_compra = $p->costo_compra;
        $this->precio_venta = $p->precio_venta;
        $this->stock_actual = $p->stock_actual;
        $this->stock_insumo = $p->stock_insumo;
        $this->stock_minimo = $p->stock_minimo;
        $this->id_afectacion = $p->id_afectacion;
        $this->id_unidad = $p->id_unidad;
        $this->activo = $p->activo;

        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        // 1. Preparamos los datos COMUNES (que siempre se guardan, sea nuevo o edición)
        $data = [
            'tipo' => $this->tipo,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'codigo_barras' => $this->codigo_barras,
            'costo_compra' => $this->costo_compra,
            'precio_venta' => $this->tipo === 'insumo' ? 0 : $this->precio_venta,
            'stock_minimo' => $this->stock_minimo,
            'id_afectacion' => $this->id_afectacion,
            'id_unidad' => $this->id_unidad,
            'activo' => $this->activo,
        ];

        // 2. LÓGICA DE SEGURIDAD PARA STOCK
        // Solo agregamos el stock al array si es un producto NUEVO.
        // Si es edición, NO tocamos esas columnas para no reiniciar el inventario accidentalmente.
        if (!$this->producto_id) {
            $data['stock_actual'] = $this->stock_actual ?? 0;
            $data['stock_insumo'] = $this->stock_insumo ?? 0;
        }

        // 3. Guardamos (Ahora $data siempre existe)
        Producto::updateOrCreate(['id' => $this->producto_id], $data);

        session()->flash('message', $this->producto_id ? 'Producto actualizado.' : 'Producto registrado.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id)
    {
        Producto::find($id)->delete();
        session()->flash('message', 'Producto eliminado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->producto_id = null;
        $this->tipo = 'venta';
        $this->nombre = ''; $this->descripcion = ''; $this->codigo_barras = '';
        $this->costo_compra = 0; $this->precio_venta = 0;
        $this->stock_actual = 0; $this->stock_minimo = 5;
        $this->stock_insumo = 0; // <--- RESET
        $this->id_afectacion = 1; // Ajusta según tu DB (10 Gravado)
        $this->id_unidad = 2; // Ajusta según tu DB (NIU Unidad)
        $this->activo = true;
    }
}
