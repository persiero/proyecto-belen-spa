<?php

namespace App\Livewire\Admin\Pos;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\Estilista;
use App\Models\Turno;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\MetodoPago;
use App\Models\Pago;
use App\Models\Caja; // Asumiendo que existe modelo Caja
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GestionPos extends Component
{
    // Carrito de Compras
    public $cart = []; // Array de items
    public $total = 0.00;

    // Datos de la Venta
    public $cliente_id;
    public $turno_id = null; // Si viene de un turno
    public $observaciones;

    // Buscadores
    public $searchProducto = '';
    public $buscar_cliente = '';
    public $clientes_encontrados = [];
    public $cliente_seleccionado_nombre = null;

    // Estilista temporal por producto (para ventas directas)
    public $estilista_temp = [];

    // Modal de Pago
    public $isPaymentModalOpen = false;
    public $metodo_pago_id = 1; // Efectivo por defecto
    public $monto_recibido = 0;
    public $vuelto = 0;
    public $referencia_pago = null; // <--- NUEVA VARIABLE

    // ... propiedades ...
    public $isSuccessModalOpen = false;
    public $ultimaVenta = null; // Para mostrar en el ticket final


    public function mount($turno_id = null)
    {
        // Regla de Negocio: Verificar Caja Abierta
        // Buscamos si el usuario actual tiene una sesión 'abierta'
        $tengoCajaAbierta = Caja::where('id_usuario_apertura', Auth::id())
            ->where('estado', 'abierta')
            ->exists();

        // Si NO tiene caja abierta, lo redirigimos
        if (!$tengoCajaAbierta) {
            session()->flash('error', '⚠️ DEBES ABRIR CAJA antes de vender.');
            return redirect()->route('admin.caja');
        }

        // 2. AUTO-CARGA DEL TURNO (NUEVA LÓGICA)
        if ($turno_id) {
            // Reutilizamos tu función cargarTurno que ya programamos antes
            $this->cargarTurno($turno_id);
        }
    }

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
            $this->cliente_id = $cliente->id; // Usamos tu variable original $cliente_id
            $this->cliente_seleccionado_nombre = $cliente->nombre . ' ' . $cliente->apellido;
            $this->buscar_cliente = '';
            $this->clientes_encontrados = [];
        }
    }

    public function limpiarCliente()
    {
        $this->cliente_id = null;
        $this->cliente_seleccionado_nombre = null;
        $this->buscar_cliente = '';
        $this->clientes_encontrados = [];
    }

    #[Layout('layouts.admin')]
    #[Title('Punto de Venta (POS)')]
    public function render()
    {
        // BÚSQUEDA INTELIGENTE
        $productos = [];
        if(strlen($this->searchProducto) > 0) {
            $productos = Producto::where('activo', true)
                ->where('tipo', '!=', 'insumo')
                ->where(function($q) {
                    $q->where('nombre', 'like', '%' . $this->searchProducto . '%')
                      ->orWhere('codigo_barras', 'like', '%' . $this->searchProducto . '%');
                })
                ->take(10)
                ->get();
        }

        $turnosPendientes = Turno::where('estado', 'activo')
            ->with(['cliente', 'servicios.servicio', 'productos.producto'])
            ->get();
        $clientes = Cliente::orderBy('nombre')->take(50)->get(); // Limitar carga
        $estilistas = Estilista::where('activo', true)->orderBy('nombre')->get();
        $metodos = MetodoPago::where('activo', true)->get();

        return view('livewire.admin.pos.gestion-pos',
            compact('productos', 'turnosPendientes', 'clientes', 'estilistas', 'metodos'));
    }

    // ==========================================
    // 1. LÓGICA DE CARGA DE TURNOS
    // ==========================================
    public function cargarTurno($idTurno)
    {
        $turno = Turno::with(['servicios.servicio', 'productos.producto', 'cliente'])->find($idTurno);

        if (!$turno) return;

        $this->resetCart(); // Limpiar carrito actual
        $this->turno_id = $turno->id;
        $this->cliente_id = $turno->id_cliente;

        $hayEliminados = false; // Bandera para saber si encontramos fantasmas 👻

        // Importar servicios del turno al carrito
        foreach ($turno->servicios as $detalle) {
            // Verificamos si no existe o si está en la papelera
            $esEliminado = !$detalle->servicio || $detalle->servicio->trashed();
            if ($esEliminado) $hayEliminados = true;

            $nombreServicio = $detalle->servicio?->nombre ?? 'Servicio No Encontrado';
            if ($detalle->servicio && $detalle->servicio->trashed()) {
                $nombreServicio .= ' ⚠️ (ELIMINADO)';
            }

            $this->cart[] = [
                'tipo' => 'servicio',
                'id' => $detalle->servicio?->id ?? 0,
                'nombre' => $nombreServicio,
                'precio' => $detalle->precio_aplicado,
                'cantidad' => 1,
                'subtotal' => $detalle->precio_aplicado,
                'estilista_id' => $detalle->id_estilista,
                'stock_check' => false
            ];
        }

        // NUEVO: Importar productos del turno al carrito
        foreach ($turno->productos as $detalle) {
            // Verificamos si no existe o si está en la papelera
            $esEliminado = !$detalle->producto || $detalle->producto->trashed();
            if ($esEliminado) $hayEliminados = true;

            $nombreProd = $detalle->producto?->nombre ?? 'Producto No Encontrado';
            if ($detalle->producto && $detalle->producto->trashed()) {
                $nombreProd .= ' ⚠️ (ELIMINADO)';
            }

            $this->cart[] = [
                'tipo' => 'producto',
                'id' => $detalle->producto?->id ?? 0,
                'nombre' => $nombreProd,
                'precio' => $detalle->precio / $detalle->cantidad,
                'cantidad' => $detalle->cantidad,
                'subtotal' => $detalle->precio,
                'estilista_id' => $detalle->id_estilista,
                'stock_check' => true
            ];
        }

        $this->cliente_id = $turno->id_cliente;

        // --- AGREGAR ESTO PARA QUE SE VEA EL NOMBRE AL CARGAR ---
        if ($turno->cliente) {
            $this->cliente_seleccionado_nombre = $turno->cliente->nombre . ' ' . $turno->cliente->apellido;
        }

        $this->calculateTotal();

        // Lanzamos un mensaje diferente si detectamos productos borrados
        if ($hayEliminados) {
            session()->flash('message', 'ATENCIÓN: El turno cargado contiene artículos que fueron eliminados del catálogo. Por favor, elimínelos de la lista (X) para poder cobrar.');
        } else {
            session()->flash('message', 'Turno cargado.');
        }
    }

    // ==========================================
    // 2. LÓGICA DE PRODUCTOS (Retail)
    // ==========================================
    public function addProducto($idProducto)
    {
        $prod = Producto::find($idProducto);

        if ($prod->stock_actual <= 0) {
            session()->flash('error', 'Producto AGOTADO.');
            return;
        }

        // Obtener estilista seleccionado (si hay)
        $estilistaId = $this->estilista_temp[$idProducto] ?? null;

        // Buscar si ya está en el carrito para sumar cantidad
        $found = false;
        foreach ($this->cart as $key => $item) {
            if ($item['tipo'] == 'producto' && $item['id'] == $idProducto) {
                if ($this->cart[$key]['cantidad'] + 1 > $prod->stock_actual) {
                    session()->flash('error', 'Stock insuficiente.');
                    return;
                }
                $this->cart[$key]['cantidad']++;
                $this->cart[$key]['subtotal'] = $this->cart[$key]['cantidad'] * $this->cart[$key]['precio'];
                $found = true;
                break;
            }
        }

        if (!$found) {
            $this->cart[] = [
                'tipo' => 'producto',
                'id' => $prod->id,
                'nombre' => $prod->nombre,
                'precio' => $prod->precio_venta,
                'cantidad' => 1,
                'subtotal' => $prod->precio_venta,
                'estilista_id' => $estilistaId, // Asignar vendedor
                'stock_check' => true
            ];
        }

        $this->calculateTotal();
        // Limpiar selección de estilista después de agregar
        unset($this->estilista_temp[$idProducto]);
    }

    // Quitar item del carrito
    public function removeItem($index)
    {
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart); // Reindexar
        $this->calculateTotal();
    }

    // INCREMENTAR CANTIDAD
    public function incrementQuantity($index)
    {
        $item = $this->cart[$index];

        // Si es producto, validamos stock
        if ($item['tipo'] == 'producto') {
            $prod = Producto::find($item['id']);
            if ($item['cantidad'] + 1 > $prod->stock_actual) {
                session()->flash('error', 'No hay suficiente stock.');
                return;
            }
        }

        $this->cart[$index]['cantidad']++;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['cantidad'] * $this->cart[$index]['precio'];
        $this->calculateTotal();
    }

    // DECREMENTAR CANTIDAD
    public function decrementQuantity($index)
    {
        if ($this->cart[$index]['cantidad'] > 1) {
            $this->cart[$index]['cantidad']--;
            $this->cart[$index]['subtotal'] = $this->cart[$index]['cantidad'] * $this->cart[$index]['precio'];
            $this->calculateTotal();
        }
    }

    // ACTUALIZAR PRECIO UNITARIO
    public function updatePrice($index, $newPrice)
    {
        $this->cart[$index]['precio'] = $newPrice;
        $this->cart[$index]['subtotal'] = $this->cart[$index]['cantidad'] * $newPrice;
        $this->calculateTotal();
    }

    public function cambiarMetodoPago($id)
    {
        $this->metodo_pago_id = $id;
        $this->referencia_pago = null; // Limpiamos referencia al cambiar

        // CORRECCIÓN: Siempre asignamos el total al cambiar de método.
        // Así evitamos que el sistema piense que "falta dinero" y bloquee el botón.
        $this->monto_recibido = $this->total;

        $this->calculateVuelto();
    }

    public function calculateTotal()
    {
        $this->total = 0;
        foreach ($this->cart as $item) {
            $this->total += $item['subtotal'];
        }
        $this->monto_recibido = $this->total; // Sugerir monto exacto
        $this->calculateVuelto();
    }

    // Este "hook" se ejecuta automáticamente cada vez que escribes en el input 'monto_recibido'
    public function updatedMontoRecibido()
    {
        $this->calculateVuelto();
    }

    public function calculateVuelto()
    {
        // Forzamos que sean números (float) para evitar errores con vacíos
        $monto = (float) ($this->monto_recibido ?? 0);
        $total = (float) $this->total;

        $this->vuelto = $monto - $total;
    }

    // ==========================================
    // 3. PROCESAR PAGO
    // ==========================================
    public function openPaymentModal()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'El carrito está vacío.');
            return;
        }
        $this->calculateTotal();
        $this->isPaymentModalOpen = true;
    }

    public function procesarVenta()
    {
        if ($this->monto_recibido < $this->total) {
            session()->flash('error_pago', 'El monto recibido es menor al total.');
            return;
        }

        // NUEVA VALIDACIÓN: Si NO es efectivo, la referencia es obligatoria
        if ($this->metodo_pago_id != 1 && empty(trim($this->referencia_pago))) {
            session()->flash('error_pago', 'Debe ingresar el número de operación o referencia del pago.');
            return;
        }

        // Validación: Si el total > 700, no permitir cliente genérico
        if ($this->total > 700 && $this->esClienteGenerico()) {
            session()->flash('error_pago', 'Para ventas mayores a S/ 700.00 debe seleccionar un cliente con DNI/RUC válido (no genérico).');
            return;
        }

        // =========================================================================
        // 🚨 FRENO DE EMERGENCIA: Validar ítems eliminados (Soft Deletes)
        // =========================================================================
        foreach ($this->cart as $item) {
            if ($item['tipo'] == 'producto') {
                $existe = \App\Models\Producto::find($item['id']);
                if (!$existe) {
                    session()->flash('error_pago', "El producto '{$item['nombre']}' ya no está disponible en el catálogo. Por favor, cierre esta ventana y retírelo de la lista.");
                    return;
                }
            } elseif ($item['tipo'] == 'servicio') {
                $existe = \App\Models\Servicio::find($item['id']);
                if (!$existe) {
                    session()->flash('error_pago', "El servicio '{$item['nombre']}' ya no está disponible en el catálogo. Por favor, cierre esta ventana y retírelo de la lista.");
                    return;
                }
            }
        }
        // =========================================================================

        DB::beginTransaction();

        try {
            // 1. Crear Venta Cabecera
            $venta = Venta::create([
                'id_turno' => $this->turno_id,
                'id_cliente' => $this->cliente_id,
                'fecha' => Carbon::now(),
                'total' => $this->total,
                'estado' => 'pagada',
                // Aquí deberías calcular IGV real desglosando, por ahora simplificado:
                'op_gravadas' => $this->total / 1.18,
                'monto_igv' => $this->total - ($this->total / 1.18)
            ]);

            // 2. Guardar Detalles y Descontar Stock
            foreach ($this->cart as $item) {

                // Si es producto, descontamos stock
                if ($item['tipo'] == 'producto') {
                    $prod = Producto::find($item['id']);
                    $prod->decrement('stock_actual', $item['cantidad']);

                    // REGISTRAR EN EL KARDEX
                    \App\Models\MovimientoInventario::create([
                        'id_producto' => $prod->id,
                        'tipo' => 'salida_venta',
                        'cantidad' => -$item['cantidad'], // Negativo porque sale
                        'motivo' => 'Venta en POS',
                        'fecha' => Carbon::now(),
                        'referencia' => 'VENTA TICKET #' . $venta->id
                    ]);
                }

                DetalleVenta::create([
                    'id_venta' => $venta->id,
                    'tipo_item' => $item['tipo'],
                    'id_estilista' => $item['estilista_id'] ?? null,
                    'id_servicio' => $item['tipo'] == 'servicio' ? $item['id'] : null,
                    'id_producto' => $item['tipo'] == 'producto' ? $item['id'] : null,
                    'nombre_item' => $item['nombre'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio'], // Con IGV
                    'valor_unitario' => $item['precio'] / 1.18, // Sin IGV aprox
                    'subtotal' => $item['subtotal'],
                    'igv_total' => $item['subtotal'] - ($item['subtotal'] / 1.18),
                    // Datos SUNAT hardcodeados por ahora (deberían venir del modelo)
                    'codigo_afectacion_igv' => '10',
                    'porcentaje_igv' => 18.00,
                    'codigo_unidad' => $item['tipo'] == 'servicio' ? 'ZZ' : 'NIU',
                ]);
            }

            // 3. Registrar Pago
            Pago::create([
                'id_venta' => $venta->id,
                'id_metodo_pago' => $this->metodo_pago_id,
                'monto' => $this->total, // Asumiendo pago total por ahora
                'fecha' => Carbon::now(),
                // LÓGICA DE REFERENCIA
                // Si es efectivo (1), va null. Si no, guardamos lo que escribió.
                'referencia' => $this->metodo_pago_id == 1 ? null : $this->referencia_pago,
                'confirmado' => true
            ]);

            // 4. Cerrar Turno si existe
            if ($this->turno_id) {
                Turno::where('id', $this->turno_id)->update([
                    'estado' => 'cerrado',
                    'hora_fin' => Carbon::now()
                ]);
            }

            DB::commit();

            // GUARDAMOS LA VENTA PARA MOSTRARLA
            $this->ultimaVenta = Venta::with(['cliente', 'detalles', 'pagos.metodoPago'])->find($venta->id);

            // LIMPIEZA PARCIAL (No reseteamos todo aún para no perder el modal)
            $this->reset(['cart', 'turno_id', 'cliente_id', 'total', 'monto_recibido', 'vuelto']);
            $this->isPaymentModalOpen = false;

            // ABRIMOS MODAL DE ÉXITO
            $this->isSuccessModalOpen = true;

            // session()->flash... (Ya no es necesario el flash si mostramos modal)

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error_pago', 'Error al procesar: ' . $e->getMessage());
        }
    }

    public function resetCart()
    {
        $this->cart = [];
        $this->turno_id = null;
        $this->cliente_id = null;
        $this->total = 0;
        $this->monto_recibido = 0;
        $this->vuelto = 0;
        $this->referencia_pago = null;
        $this->limpiarCliente();
    }

    public function cerrarSuccessModal()
    {
        $this->isSuccessModalOpen = false;
        $this->ultimaVenta = null;
    }

    public function closePaymentModal() { $this->isPaymentModalOpen = false; }

    // Método helper para validar si el cliente es genérico
    public function esClienteGenerico()
    {
        if (!$this->cliente_id) return true;

        $cliente = Cliente::find($this->cliente_id);
        if (!$cliente) return true;

        return in_array($cliente->numero_documento, ['00000000', '0', '-']);
    }

    // Método computed para verificar si debe bloquearse por el límite de 700
    public function getRequiereClienteValidoProperty()
    {
        return $this->total > 700 && $this->esClienteGenerico();
    }


}
