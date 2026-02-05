<?php

namespace App\Livewire\Admin\Ventas;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use App\Models\Venta;
use App\Models\Producto;
use App\Models\Turno;
use App\Services\SunatService;
use App\Models\Comprobante;
use Illuminate\Support\Facades\DB;

class HistorialVentas extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $fecha_inicio;
    public $fecha_fin;

    public function mount()
    {
        // Por defecto mostramos ventas de hoy
        $this->fecha_inicio = date('Y-m-d');
        $this->fecha_fin = date('Y-m-d');
    }

    // ==========================================
    // NUEVO MÉTODO: EMISIÓN DE CPE (SUNAT)
    // ==========================================
    public function emitirComprobante($ventaId)
    {
        // 1. Verificar si ya existe comprobante
        if (Comprobante::where('id_venta', $ventaId)->exists()) {
            $this->dispatch('alert', ['type' => 'warning', 'message' => 'Esta venta ya tiene un comprobante emitido.']);
            return;
        }

        $venta = Venta::with(['cliente', 'detalles'])->find($ventaId);

        // 2. Instanciar el Servicio (Manualmente o por Inyección)
        $sunatService = new SunatService();
        
        // 3. Llamar a la función de generar
        $resultado = $sunatService->generarComprobante($venta);

        // --- AGREGA ESTO TEMPORALMENTE ---
        //dd($resultado); // <--- ESTO DETENDRÁ TODO Y MOSTRARÁ EL MENSAJE
        // ---------------------------------

        // 4. Notificar al usuario
        
        if ($resultado['success']) {
            session()->flash('message', '¡ÉXITO SUNAT! Comprobante emitido y aceptado. ' . $resultado['message']);
        } else {
            // Usamos session flash error o un dispatch de alerta
            $this->dispatch('alert', ['type' => 'error', 'message' => 'ERROR SUNAT: ' . $resultado['message']]);
        }
            
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        $ventas = Venta::with(['cliente', 'pagos.metodoPago'])
            ->whereBetween('fecha', [$this->fecha_inicio . ' 00:00:00', $this->fecha_fin . ' 23:59:59'])
            ->where(function($q) {
                $q->where('id', 'like', '%' . $this->search . '%')
                  ->orWhereHas('cliente', function($c) {
                      $c->where('nombre', 'like', '%' . $this->search . '%');
                  });
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.ventas.historial-ventas', compact('ventas'))
            ->with('titulo', 'Historial de Transacciones');
    }

    // ==========================================
    // ANULACIÓN DE VENTA (REVERSIÓN)
    // ==========================================
    public function anularVenta($id)
    {
        DB::transaction(function () use ($id) {
            $venta = Venta::with('detalles')->findOrFail($id);

            if ($venta->estado == 'anulada') return; // Ya estaba anulada

            // 1. Cambiar estado
            $venta->estado = 'anulada';
            $venta->save();

            // 2. Liberar Turno (si venía de uno)
            if ($venta->id_turno) {
                // Opción A: Lo dejamos cerrado pero marcado.
                // Opción B: Lo volvemos a abrir para corregirlo. 
                // Usaremos Opción A por seguridad, el turno ya pasó.
                $turno = Turno::find($venta->id_turno);
                $turno->observaciones .= " (Venta #$id Anulada)";
                $turno->save();
            }

            // 3. DEVOLVER STOCK (Magia inversa)
            foreach ($venta->detalles as $detalle) {
                if ($detalle->tipo_item == 'producto' && $detalle->id_producto) {
                    $prod = Producto::find($detalle->id_producto);
                    if ($prod) {
                        // Devolvemos a Stock Venta (stock_actual)
                        $prod->increment('stock_actual', $detalle->cantidad);
                    }
                }
            }
        });

        session()->flash('message', 'Venta #' . $id . ' anulada. Stock restaurado y dinero descontado.');
    }

    public function anularComprobante($ventaId)
    {
        $venta = Venta::with('comprobante', 'detalles')->find($ventaId);

        if (!$venta->comprobante) {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Esta venta no tiene comprobante para anular.']);
            return;
        }

        // Llamar al servicio
        $sunatService = new SunatService();
        $resultado = $sunatService->generarNotaCredito($venta);

        if ($resultado['success']) {
            // 1. Restaurar el Stock
            $this->restaurarStock($venta);

            // 2. Cambiar estado (CORREGIDO: 'anulada' con 'a')
            $venta->estado = 'anulada';
            $venta->save();
            
            session()->flash('message', 'Nota de Crédito emitida y Stock restaurado correctamente.');
        } else {
            $this->dispatch('alert', ['type' => 'error', 'message' => 'Error SUNAT: ' . $resultado['message']]);
        }
    }

    // ==========================================
    // FUNCIÓN PRIVADA PARA DEVOLVER STOCK (CORREGIDA)
    // ==========================================
    private function restaurarStock(Venta $venta)
    {
        foreach ($venta->detalles as $detalle) {
            // 1. Validamos que sea 'producto' (Igual que en tu anularVenta)
            // Así evitamos errores si intentamos sumar stock a un Servicio
            if ($detalle->tipo_item == 'producto' && $detalle->id_producto) {
                
                $producto = Producto::find($detalle->id_producto);

                if ($producto) {
                    // 2. CORREGIDO: Usamos 'stock_actual' en lugar de 'stock'
                    $producto->increment('stock_actual', $detalle->cantidad);
                }
            }
        }
    }
}