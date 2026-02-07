<?php

namespace App\Livewire\Admin\Turnos;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Turno;
use App\Models\TurnoServicio;
use App\Models\TurnoProducto;
use App\Models\Cliente;
use App\Models\Estilista;
use App\Models\Servicio;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GestionTurnos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $isOpen = false;
    public $search = '';

    // Datos del Turno
    public $turno_id = null;
    public $id_cliente;
    public $observaciones;

    // [PEGAR AQUÍ] BLOQUE DE BUSCADOR DE CLIENTES -----------------------
    public $buscar_cliente = ''; 
    public $clientes_encontrados = []; 
    public $cliente_seleccionado_nombre = null;

    // BUSCADOR DE PRODUCTOS (por índice de item)
    public $buscar_producto = []; // ['0' => 'termo', '1' => 'shampoo']
    public $productos_encontrados = []; // ['0' => [...], '1' => [...]]
    
    // Lista dinámica de servicios [servicio_id, estilista_id, precio]
    public $items = []; 

    // NUEVO: Lista de PRODUCTOS [producto_id, estilista_id, precio, cantidad]
    public $items_productos = [];

    // Reglas de validación
    protected $rules = [
        'id_cliente' => 'required|exists:clientes,id',
        // Servicios
        'items' => 'required|array|min:1',
        'items.*.servicio_id' => 'required|exists:servicios,id',
        'items.*.estilista_id' => 'required|exists:estilistas,id',
        'items.*.precio' => 'required|numeric|min:0',
        // Productos
        'items_productos' => 'nullable|array',
        'items_productos.*.producto_id' => 'required|exists:productos,id',
        'items_productos.*.estilista_id' => 'required|exists:estilistas,id',
        'items_productos.*.cantidad' => 'required|integer|min:1',
        'items_productos.*.precio' => 'required|numeric|min:0',
        
    ];

    public function updatedBuscarCliente()
    {
        // Limpiamos espacios en blanco al inicio/final
        $termino = trim($this->buscar_cliente);

        if(strlen($termino) < 2) {
            $this->clientes_encontrados = [];
            return;
        }

        $this->clientes_encontrados = \App\Models\Cliente::where(function($query) use ($termino) {
                $query->where('nombre', 'like', '%' . $termino . '%')
                      ->orWhere('apellido', 'like', '%' . $termino . '%')
                      ->orWhere('numero_documento', 'like', '%' . $termino . '%');
            })->limit(5)->get();
    }

    public function seleccionarCliente($id)
    {
        $cliente = \App\Models\Cliente::find($id);
        if($cliente) {
            $this->id_cliente = $cliente->id; // Usamos tu variable original $id_cliente
            $this->cliente_seleccionado_nombre = $cliente->nombre . ' ' . $cliente->apellido;
            $this->buscar_cliente = '';
            $this->clientes_encontrados = [];
        }
    }

    public function limpiarCliente()
    {
        $this->id_cliente = null;
        $this->cliente_seleccionado_nombre = null;
        $this->buscar_cliente = '';
        $this->clientes_encontrados = [];
    }

    #[Layout('layouts.admin')]
    #[Title('Recepción y Turnos')]
    public function render()
    {
        // 1. MONITOR DE ESTILISTAS
        // Traemos estilistas activos y sus atenciones de hoy que NO han terminado
        $monitorEstilistas = Estilista::where('activo', true)
            ->with(['atencionesEnCurso' => function($q) {
                // Filtramos solo las atenciones de turnos ACTIVOS
                $q->whereHas('turno', function($t) {
                    $t->where('estado', 'activo');
                });
            }]) 
            ->orderBy('nombre')
            ->get();

        // 2. HISTORIAL DE TURNOS ACTIVOS
        $turnos = Turno::with(['cliente', 'servicios.servicio', 'servicios.estilista'])
            ->where('estado', 'activo') 
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Catálogos para el Modal
        $clientes = Cliente::orderBy('nombre')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        // NUEVO: Catálogo de productos (Solo venta o mixto)
        $productos_catalogo = Producto::where('activo', true)
            ->whereIn('tipo', ['venta', 'mixto'])
            ->where('stock_actual', '>', 0) // Solo con stock
            ->orderBy('nombre')->get();
        $estilistas = Estilista::where('activo', true)->orderBy('nombre')->get(); // Para el select

        return view('livewire.admin.turnos.gestion-turnos', 
            compact('turnos', 'monitorEstilistas', 'estilistas', 'clientes', 'servicios', 'productos_catalogo'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->addItem(); // Fila vacía por defecto
        // $this->addProducto(); // No agregamos producto por defecto (es opcional)
        $this->openModal();
    }

    public function addItem()
    {
        $this->items[] = [
            'servicio_id' => '',
            'estilista_id' => '',
            'precio' => 0.00
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // Actualizar precio automáticamente al seleccionar servicio
    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) == 2 && $parts[1] == 'servicio_id') {
            $index = $parts[0];
            $servicio = Servicio::find($value);
            if ($servicio) {
                $this->items[$index]['precio'] = $servicio->precio;
            }
        }
    }

    // --- NUEVO: LÓGICA DE PRODUCTOS ---
    public function addProducto()
    {
        $index = count($this->items_productos);
        $this->items_productos[] = [
            'producto_id' => '', 
            'estilista_id' => '', // ¿Quién vendió?
            'cantidad' => 1,
            'precio_unitario' => 0.00,
            'precio' => 0.00,
            'producto_nombre' => null // Para mostrar cuando está seleccionado
        ];
        $this->buscar_producto[$index] = '';
        $this->productos_encontrados[$index] = [];
    }

    public function removeProducto($index)
    {
        unset($this->items_productos[$index]);
        unset($this->buscar_producto[$index]);
        unset($this->productos_encontrados[$index]);
        $this->items_productos = array_values($this->items_productos);
        $this->buscar_producto = array_values($this->buscar_producto);
        $this->productos_encontrados = array_values($this->productos_encontrados);
    }

    // Buscador de productos por índice
    public function updatedBuscarProducto($value, $index)
    {
        $termino = trim($value);
        if(strlen($termino) < 2) {
            $this->productos_encontrados[$index] = [];
            return;
        }
        $this->productos_encontrados[$index] = Producto::where('activo', true)
            ->whereIn('tipo', ['venta', 'mixto'])
            ->where('stock_actual', '>', 0)
            ->where(function($q) use ($termino) {
                $q->where('nombre', 'like', '%' . $termino . '%')
                  ->orWhere('codigo_barras', 'like', '%' . $termino . '%');
            })
            ->limit(5)->get();
    }

    public function seleccionarProducto($index, $productoId)
    {
        $prod = Producto::find($productoId);
        if($prod) {
            $this->items_productos[$index]['producto_id'] = $prod->id;
            $this->items_productos[$index]['producto_nombre'] = $prod->nombre;
            $this->items_productos[$index]['precio_unitario'] = $prod->precio_venta;
            $cantidad = $this->items_productos[$index]['cantidad'] ?? 1;
            $this->items_productos[$index]['precio'] = $prod->precio_venta * $cantidad;
            $this->buscar_producto[$index] = '';
            $this->productos_encontrados[$index] = [];
        }
    }

    public function limpiarProducto($index)
    {
        $this->items_productos[$index]['producto_id'] = '';
        $this->items_productos[$index]['producto_nombre'] = null;
        $this->items_productos[$index]['precio_unitario'] = 0;
        $this->items_productos[$index]['precio'] = 0;
        $this->buscar_producto[$index] = '';
        $this->productos_encontrados[$index] = [];
    }

    // Al seleccionar producto o cambiar cantidad, actualizamos precio total
    public function updatedItemsProductos($value, $key)
    {
        $parts = explode('.', $key);
        $index = $parts[0];
        $field = $parts[1] ?? null;
        
        // Si cambia la cantidad, recalculamos el precio total
        if ($field == 'cantidad' && isset($this->items_productos[$index]['precio_unitario'])) {
            $precioUnitario = $this->items_productos[$index]['precio_unitario'];
            $this->items_productos[$index]['precio'] = $precioUnitario * $value;
        }
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $this->turno_id = $id;

        $turno = Turno::with('servicios', 'cliente')->find($id);
        $this->id_cliente = $turno->id_cliente;
        // AGREGAR ESTO: Cargar el nombre visual para que el buscador aparezca verde
        if($turno->cliente) {
            $this->cliente_seleccionado_nombre = $turno->cliente->nombre . ' ' . $turno->cliente->apellido;
        }
        $this->observaciones = $turno->observaciones;

        // Cargar Servicios
        foreach($turno->servicios as $detalle) {
            $this->items[] = [
                'servicio_id' => $detalle->id_servicio,
                'estilista_id' => $detalle->id_estilista,
                'precio' => $detalle->precio_aplicado
            ];
        }

        //Cargar Productos (si hay)
        foreach($turno->productos as $prod) {
            $precioUnitario = $prod->cantidad > 0 ? $prod->precio / $prod->cantidad : 0;
            $idx = count($this->items_productos);
            $this->items_productos[] = [
                'producto_id' => $prod->id_producto,
                'estilista_id' => $prod->id_estilista,
                'cantidad' => $prod->cantidad,
                'precio_unitario' => $precioUnitario,
                'precio' => $prod->precio,
                'producto_nombre' => $prod->producto->nombre ?? null
            ];
            $this->buscar_producto[$idx] = '';
            $this->productos_encontrados[$idx] = [];
        }

        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->turno_id) {
                // EDITAR
                $turno = Turno::find($this->turno_id);
                $turno->update([
                    'id_cliente' => $this->id_cliente,
                    'observaciones' => $this->observaciones
                ]);
                
                // Borrón y cuenta nueva de detalles (Estrategia segura)
                TurnoServicio::where('id_turno', $this->turno_id)->delete();
                TurnoProducto::where('id_turno', $this->turno_id)->delete(); // NUEVO

                $mensaje = 'Atención actualizada correctamente.';
            } else {
                // CREAR
                $turno = Turno::create([
                    'id_cliente' => $this->id_cliente,
                    'hora_inicio' => Carbon::now(),
                    'estado' => 'activo',
                    'observaciones' => $this->observaciones
                ]);
                $mensaje = 'Nueva atención iniciada.';
            }

            // GUARDAR SERVICIOS
            foreach ($this->items as $item) {
                TurnoServicio::create([
                    'id_turno' => $turno->id,
                    'id_servicio' => $item['servicio_id'],
                    'id_estilista' => $item['estilista_id'],
                    'precio_aplicado' => $item['precio']
                ]);
            }

            // NUEVO: GUARDAR PRODUCTOS
            if (!empty($this->items_productos)) {
                foreach ($this->items_productos as $prod) {
                    TurnoProducto::create([
                        'id_turno' => $turno->id,
                        'id_producto' => $prod['producto_id'],
                        'id_estilista' => $prod['estilista_id'],
                        'cantidad' => $prod['cantidad'],
                        'precio' => $prod['precio']
                    ]);
                    // Nota: Aquí NO descontamos stock aún. El stock se descuenta al PAGAR en caja/pos.
                    // Esto es solo una reserva/intención de venta.
                }
            }

            DB::commit();
            session()->flash('message', $mensaje);
            $this->closeModal();
            $this->resetInputFields();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('message', 'Error: ' . $e->getMessage());
        }
    }

    public function cancelar($id)
    {
        $turno = Turno::find($id);
        $turno->update(['estado' => 'cancelado', 'hora_fin' => Carbon::now()]);
        session()->flash('message', 'Turno cancelado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->turno_id = null;
        $this->id_cliente = '';
        $this->observaciones = '';
        $this->items = [];
        $this->items_productos = []; // NUEVO
        $this->buscar_producto = [];
        $this->productos_encontrados = [];
        $this->limpiarCliente();
    }
}