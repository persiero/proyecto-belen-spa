<?php

namespace App\Livewire\Admin\Compras;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\Compra;
use App\Models\CompraDetalle;
use App\Models\MovimientoInventario; // Opcional, si quieres trazar kardex
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GestionCompras extends Component
{
    // Cabecera
    public $id_proveedor = null; // Opcional
    public $tipo_documento = 'sin_documento';
    public $numero_documento;
    public $fecha_compra;
    public $observaciones;

    // Detalle (Carrito de compra)
    public $cart = [];
    public $total = 0.00;
    
    // Buscador
    public $searchProducto = '';

    public function mount() {
        $this->fecha_compra = Carbon::now()->format('Y-m-d');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $proveedores = Proveedor::where('activo', true)->orderBy('nombre_empresa')->get();
        
        $productos = Producto::where('nombre', 'like', '%' . $this->searchProducto . '%')
            ->where('activo', true)
            ->take(5)
            ->get();

        return view('livewire.admin.compras.gestion-compras', compact('proveedores', 'productos'))
            ->with('titulo', 'Registrar Ingreso de Mercadería');
    }

    // Agregar producto al carrito de entrada
    public function addProducto($id)
    {
        $prod = Producto::find($id);
        
        // Verificar si ya está en la lista
        foreach($this->cart as $item) {
            if($item['id'] == $id) return; // Ya está agregado
        }

        $this->cart[] = [
            'id' => $prod->id,
            'nombre' => $prod->nombre,
            'tipo' => $prod->tipo, // 'insumo', 'venta' o 'mixto'
            'cantidad' => 1,
            'costo' => $prod->costo_compra, // Sugerimos el último costo registrado
            'subtotal' => $prod->costo_compra
        ];
        
        $this->calculateTotal();
        $this->searchProducto = '';
    }

    // Recalcular montos al editar inputs en la tabla
    public function updateItem($index, $field, $value)
    {
        $this->cart[$index][$field] = $value;
        
        // Validar números
        //if(!is_numeric($this->cart[$index]['cantidad']) || $this->cart[$index]['cantidad'] < 1) 
            //$this->cart[$index]['cantidad'] = 1;
            
        //if(!is_numeric($this->cart[$index]['costo']) || $this->cart[$index]['costo'] < 0) 
            //$this->cart[$index]['costo'] = 0;

        if($field == 'cantidad' && $value < 1) $this->cart[$index]['cantidad'] = 1;
        if($field == 'costo' && $value < 0) $this->cart[$index]['costo'] = 0;

        $this->cart[$index]['subtotal'] = 
            $this->cart[$index]['cantidad'] * $this->cart[$index]['costo'];

        $this->calculateTotal();
    }

    public function removeItem($index) {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
        $this->calculateTotal();
    }

    public function calculateTotal() {
        $this->total = 0;
        foreach($this->cart as $item) {
            $this->total += $item['subtotal'];
        }
    }

    public function guardarCompra()
    {
        $this->validate([
            'fecha_compra' => 'required|date',
            'cart' => 'required|array|min:1',
            'numero_documento' => 'nullable|string|max:50'
        ]);

        DB::beginTransaction();
        try {
            // 1. Crear Cabecera Compra
            $compra = Compra::create([
                'id_proveedor' => $this->id_proveedor ?: null, // null si está vacío
                'fecha' => $this->fecha_compra,
                'tipo_documento' => $this->tipo_documento,
                'numero_documento' => $this->numero_documento,
                'costo_total' => $this->total,
                'observaciones' => $this->observaciones
            ]);

            // 2. Procesar Detalles y Actualizar Stock/Costo
            foreach($this->cart as $item) {
                // Registrar Detalle
                CompraDetalle::create([
                    'id_compra' => $compra->id,
                    'id_producto' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'costo_unitario' => $item['costo'],
                    'costo_total' => $item['subtotal']
                ]);

                // Actualizar Producto Maestro
                $prod = Producto::find($item['id']);
                
                // --- LÓGICA INTELIGENTE DE DOBLE STOCK ---
                if ($prod->tipo == 'insumo') {
                    // Si es insumo puro, va directo al almacén interno
                    $prod->increment('stock_insumo', $item['cantidad']);
                } else {
                    // Si es 'venta' o 'mixto', asumimos que entra para ser vendido (Vitrina)
                    // (Si luego quieren usarlo internamente, harán una Transferencia)
                    $prod->increment('stock_actual', $item['cantidad']);
                }
                
                // Actualizar Costo
                $prod->update(['costo_compra' => $item['costo']]);

                // . Registrar en KARDEX (Entrada)
                MovimientoInventario::create([
                    'id_producto' => $prod->id,
                    'tipo' => 'entrada',
                    'cantidad' => $item['cantidad'],
                    'referencia' => 'COMPRA #' . $compra->id,
                    'motivo' => 'Ingreso por Compra',
                    'fecha' => $this->fecha_compra
                ]);
            }

            DB::commit();
            session()->flash('message', 'Compra registrada. Stock actualizado correctamente.');
            $this->reset(['cart', 'total', 'id_proveedor', 'numero_documento', 'observaciones']);

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }
}