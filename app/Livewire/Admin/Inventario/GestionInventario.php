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

        // Productos para selects (Limitado para rendimiento)
        $productos = Producto::where('activo', true)
            ->orderBy('nombre')
            ->take(100) // Limite seguro
            ->get();

        return view('livewire.admin.inventario.gestion-inventario', 
            compact('movimientos', 'productos', 'listaStock'));
    }

    public function cambiarTab($nombreTab) {
        $this->tab = $nombreTab;
        $this->resetValidation();
        $this->reset(['producto_id', 'cantidad', 'motivo', 'prod_transferencia_id', 'cant_transferencia', 'producto_seleccionado']);
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