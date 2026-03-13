<?php

namespace App\Livewire\Admin\Inventario;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Producto;
use App\Models\MovimientoInventario;
use App\Models\Transferencia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GestionInventario extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $tab = 'stock';
    public $search = '';

    // -- Formulario Ajuste --
    public $producto_id;
    public $producto_seleccionado = null;
    public $tipo_movimiento = 'salida_insumo';
    public $cantidad = 1;
    public $motivo = '';

    // -- Formulario Transferencia --
    public $prod_transferencia_id;
    public $origen = 'venta';
    public $destino = 'insumo';
    public $cant_transferencia = 1;
    public $motivo_transferencia = '';

    // -- Variables para el Buscador Predictivo (Ajustes) --
    public $buscar_producto_ajuste = '';
    public $productos_encontrados_ajuste = [];

    // -- Variables para el Buscador Predictivo (Transferencias) --
    public $buscar_producto_transferencia = '';
    public $productos_encontrados_transferencia = [];

    public function mount()
    {
        // Detectar si viene con parámetro tab en la URL
        if (request()->has('tab')) {
            $this->tab = request()->get('tab');
        }
    }

    // ===============================================
    // LÓGICA PARA BUSCADOR DE AJUSTES
    // ===============================================
    public function updatedBuscarProductoAjuste()
    {
        $termino = trim($this->buscar_producto_ajuste);

        if(strlen($termino) < 2) {
            $this->productos_encontrados_ajuste = [];
            return;
        }

        // Buscar por nombre o código de barras
        $this->productos_encontrados_ajuste = Producto::where('activo', true)
            ->where(function($query) use ($termino) {
                $query->where('nombre', 'like', '%' . $termino . '%')
                      ->orWhere('codigo_barras', 'like', '%' . $termino . '%');
            })
            ->limit(10) // Mostrar solo 10 resultados para no saturar la vista
            ->get();
    }

    public function seleccionarProductoAjuste($id)
    {
        $this->producto_seleccionado = Producto::find($id);
        $this->producto_id = $this->producto_seleccionado->id;

        // Limpiamos el buscador y mostramos el nombre del elegido
        $this->buscar_producto_ajuste = $this->producto_seleccionado->nombre;
        $this->productos_encontrados_ajuste = [];
    }

    public function limpiarProductoAjuste()
    {
        $this->producto_seleccionado = null;
        $this->producto_id = null;
        $this->buscar_producto_ajuste = '';
        $this->productos_encontrados_ajuste = [];
    }

    // ===============================================
    // LÓGICA PARA BUSCADOR DE TRANSFERENCIAS
    // ===============================================
    public function updatedBuscarProductoTransferencia()
    {
        $termino = trim($this->buscar_producto_transferencia);

        if(strlen($termino) < 2) {
            $this->productos_encontrados_transferencia = [];
            return;
        }

        $this->productos_encontrados_transferencia = Producto::where('activo', true)
            ->where(function($query) use ($termino) {
                $query->where('nombre', 'like', '%' . $termino . '%')
                      ->orWhere('codigo_barras', 'like', '%' . $termino . '%');
            })
            ->limit(10)
            ->get();
    }

    public function seleccionarProductoTransferencia($id)
    {
        $this->producto_seleccionado = Producto::find($id);
        $this->prod_transferencia_id = $this->producto_seleccionado->id;

        // Reiniciamos la cantidad a 1 por defecto
        $this->cant_transferencia = 1;

        $this->buscar_producto_transferencia = $this->producto_seleccionado->nombre;
        $this->productos_encontrados_transferencia = [];
    }

    public function limpiarProductoTransferencia()
    {
        $this->producto_seleccionado = null;
        $this->prod_transferencia_id = null;
        $this->buscar_producto_transferencia = '';
        $this->productos_encontrados_transferencia = [];
        $this->cant_transferencia = 1;
    }

    #[Layout('layouts.admin')]
    #[Title('Centro de Control de Stock')]
    public function render()
    {
        // 1. Pestaña STOCK
        $listaStock = [];
        if($this->tab == 'stock') {
            $listaStock = Producto::where('activo', true)
                ->where(function($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('codigo_barras', 'like', '%' . $this->search . '%');
                })
                ->orderBy('nombre')
                ->paginate(15);
        }

        // 2. Pestaña KARDEX
        $movimientos = [];
        if($this->tab == 'kardex') {
            $movimientos = MovimientoInventario::with('producto')
                ->whereHas('producto', function($q) {
                    $q->where('nombre', 'like', '%' . $this->search . '%');
                })
                ->orderBy('fecha', 'desc')
                ->orderBy('id', 'desc')
                ->paginate(15);
        }

        return view('livewire.admin.inventario.gestion-inventario',
            compact('movimientos', 'listaStock'));
    }

    public function cambiarTab($nombreTab) {
        $this->tab = $nombreTab;
        $this->resetValidation();
        // Agregamos las nuevas variables al reset
        $this->reset([
            'producto_id', 'cantidad', 'motivo', 'producto_seleccionado',
            'prod_transferencia_id', 'cant_transferencia', 'motivo_transferencia',
            'buscar_producto_ajuste', 'productos_encontrados_ajuste',
            'buscar_producto_transferencia', 'productos_encontrados_transferencia'
        ]);
        $this->search = '';
    }

    // Reactividad: Actualizar info al seleccionar producto (Ajuste)
    public function updatedProductoId($value) {
        $this->producto_seleccionado = Producto::find($value);
    }

    // Reactividad: Actualizar info al seleccionar producto (Transferencia)
    public function updatedProdTransferenciaId($value) {
        $this->producto_seleccionado = Producto::find($value);
        $this->cant_transferencia = 1;
    }

    public function guardarAjuste()
    {
        $this->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
            'motivo' => 'required|string|max:255',
            'tipo_movimiento' => 'required|in:ajuste_entrada,ajuste_salida,salida_insumo'
        ]);

        $prod = Producto::find($this->producto_id);

        $bolsillo = 'stock_actual';
        $operacion = '+';
        $tipoDb = 'ajuste';

        switch ($this->tipo_movimiento) {
            case 'ajuste_entrada': $operacion = '+'; break;
            case 'ajuste_salida':  $operacion = '-'; break;
            case 'salida_insumo':
                $bolsillo = 'stock_insumo';
                $operacion = '-';
                $tipoDb = 'salida_insumo';
                break;
        }

        // Validación Stock
        if ($operacion == '-') {
            $stockDisponible = ($bolsillo == 'stock_actual') ? $prod->stock_actual : $prod->stock_insumo;
            if ($stockDisponible < $this->cantidad) {
                $this->addError('cantidad', "Stock insuficiente (Disponible: $stockDisponible).");
                return;
            }
        }

        DB::transaction(function () use ($prod, $tipoDb, $operacion, $bolsillo) {
            if ($operacion == '+') {
                $prod->increment($bolsillo, $this->cantidad);
            } else {
                $prod->decrement($bolsillo, $this->cantidad);
            }

            MovimientoInventario::create([
                'id_producto' => $this->producto_id,
                'tipo' => $tipoDb,
                'cantidad' => ($operacion == '-') ? -$this->cantidad : $this->cantidad,
                'motivo' => $this->motivo,
                'fecha' => Carbon::now(),
                'referencia' => 'AJUSTE MANUAL'
            ]);
        });

        session()->flash('message', 'Movimiento registrado correctamente.');
        $this->reset(['producto_id', 'cantidad', 'motivo', 'producto_seleccionado']);
    }

    public function guardarTransferencia()
    {
        $this->validate([
            'prod_transferencia_id' => 'required|exists:productos,id',
            'cant_transferencia' => 'required|integer|min:1',
            'origen' => 'required|in:venta,insumo',
            'destino' => 'required|in:venta,insumo|different:origen',
            'motivo_transferencia' => 'nullable|string|max:255'
        ]);

        $prod = Producto::find($this->prod_transferencia_id);

        if ($this->origen == 'venta' && $prod->stock_actual < $this->cant_transferencia) {
            $this->addError('cant_transferencia', 'Stock insuficiente en Venta (Tienes: ' . $prod->stock_actual . ').');
            return;
        }
        if ($this->origen == 'insumo' && $prod->stock_insumo < $this->cant_transferencia) {
            $this->addError('cant_transferencia', 'Stock insuficiente en Insumo (Tienes: ' . $prod->stock_insumo . ').');
            return;
        }

        DB::transaction(function () use ($prod) {
            if ($this->origen == 'venta') {
                $prod->decrement('stock_actual', $this->cant_transferencia);
                $prod->increment('stock_insumo', $this->cant_transferencia);
                $desc = "Traslado a Insumo";
            } else {
                $prod->decrement('stock_insumo', $this->cant_transferencia);
                $prod->increment('stock_actual', $this->cant_transferencia);
                $desc = "Retorno a Venta";
            }

            Transferencia::create([
                'id_producto' => $this->prod_transferencia_id,
                'origen' => $this->origen,
                'destino' => $this->destino,
                'cantidad' => $this->cant_transferencia,
                'fecha' => Carbon::now(),
                'observaciones' => $this->motivo_transferencia
            ]);

            MovimientoInventario::create([
                'id_producto' => $this->prod_transferencia_id,
                'tipo' => 'ajuste',
                'cantidad' => 0, // No altera el total global, solo mueve bolsillos
                'motivo' => $desc . ($this->motivo_transferencia ? ': '.$this->motivo_transferencia : ''),
                'fecha' => Carbon::now(),
                'referencia' => 'TRANSFERENCIA'
            ]);
        });

        session()->flash('message', 'Transferencia exitosa.');
        $this->reset(['prod_transferencia_id', 'cant_transferencia', 'motivo_transferencia', 'producto_seleccionado']);
        $this->tab = 'stock';
    }
}
