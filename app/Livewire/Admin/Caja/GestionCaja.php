<?php

namespace App\Livewire\Admin\Caja;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Caja;
use App\Models\MovimientoCaja; // <--- 1. IMPORTANTE: Agregado
use App\Models\Pago;
use App\Models\MetodoPago;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class GestionCaja extends Component
{
    public $cajaAbierta = null;
    
    // Formulario Apertura
    public $monto_inicial = 0.00;

    // Formulario de Gastos (NUEVO)
    public $gasto_monto;
    public $gasto_descripcion;

    // Resumen de Cierre (Calculado)
    public $resumenMetodos = [];
    public $totalVentas = 0.00;
    public $totalEfectivoEnCaja = 0.00; 
    public $totalGastos = 0.00; // <--- NUEVO: Para mostrar el total retirado

    public $dinero_fisico; // Lo que el usuario escribe
    public $diferencia = 0; // Lo que calculamos

    public $movimientos = [];

    #[Layout('layouts.admin')]
    public function render()
    {
        // 1. Verificar si el usuario tiene caja abierta
        $this->cajaAbierta = Caja::where('id_usuario_apertura', Auth::id())
            ->where('estado', 'abierta')
            ->latest()
            ->first();

        // 2. Si está abierta, calcular totales y cargar movimientos
        if ($this->cajaAbierta) {
            
            // Calculamos los totales
            $this->calcularArqueo();

            // Cargar movimientos (DENTRO DEL IF)
            $this->movimientos = MovimientoCaja::where('id_caja', $this->cajaAbierta->id)
                ->with('usuario')
                ->orderBy('created_at', 'desc')
                ->get();
                
        } else {
            // Si no hay caja abierta, la lista de movimientos está vacía
            $this->movimientos = [];
        }

        return view('livewire.admin.caja.gestion-caja')
            ->with('titulo', 'Control de Caja');
    }

    public function calcularArqueo()
    {
        // A. VENTAS (Tu lógica original)
        $pagos = Pago::where('created_at', '>=', $this->cajaAbierta->fecha_apertura)
            ->whereHas('venta', function($q) {
                $q->where('estado', '!=', 'anulada');
            })
            ->with('metodoPago')
            ->get();

        $this->resumenMetodos = [];
        $this->totalVentas = 0;

        $metodosDb = MetodoPago::where('activo', true)->get();
        foreach($metodosDb as $m) {
            $this->resumenMetodos[$m->nombre] = 0;
        }

        foreach ($pagos as $pago) {
            $nombreMetodo = $pago->metodoPago->nombre;
            if (!isset($this->resumenMetodos[$nombreMetodo])) {
                $this->resumenMetodos[$nombreMetodo] = 0;
            }
            $this->resumenMetodos[$nombreMetodo] += $pago->monto;
            $this->totalVentas += $pago->monto;
        }

        // B. GASTOS (NUEVA LÓGICA)
        // Sumamos todo lo que haya salido de ESTA caja abierta
        $this->totalGastos = MovimientoCaja::where('id_caja', $this->cajaAbierta->id)
                                           ->where('tipo', 'egreso')
                                           ->sum('monto');

        // C. CÁLCULO FINAL DEL EFECTIVO
        // Saldo Inicial + Ventas en Efectivo - Gastos
        $ventasEfectivo = $this->resumenMetodos['efectivo'] ?? 0;
        
        $this->totalEfectivoEnCaja = ($this->cajaAbierta->monto_apertura + $ventasEfectivo) - $this->totalGastos;
    }

    // --- NUEVA FUNCIÓN: REGISTRAR GASTO ---
    public function registrarGasto()
    {
        $this->validate([
            'gasto_monto' => 'required|numeric|min:0.10',
            'gasto_descripcion' => 'required|string|min:3'
        ]);

        MovimientoCaja::create([
            'id_caja' => $this->cajaAbierta->id,
            'tipo' => 'egreso',
            'monto' => $this->gasto_monto,
            'descripcion' => $this->gasto_descripcion,
            'id_usuario' => Auth::id(),
        ]);

        // Limpiar
        $this->reset(['gasto_monto', 'gasto_descripcion']);
        
        // Cerrar modal (evento de navegador)
        $this->dispatch('close-modal'); 
        
        // Recalcular
        $this->calcularArqueo();

        session()->flash('message', 'Salida de dinero registrada correctamente.');
    }

    public function abrirCaja()
    {
        $this->validate(['monto_inicial' => 'required|numeric|min:0']);

        Caja::create([
            'fecha_apertura' => Carbon::now(),
            'monto_apertura' => $this->monto_inicial,
            'estado' => 'abierta',
            'id_usuario_apertura' => Auth::id()
        ]);

        session()->flash('message', 'Caja abierta correctamente. Ya puedes vender.');
        $this->monto_inicial = 0;
    }

    public function cerrarCaja()
    {
        // Validamos que haya ingresado el conteo
        $this->validate([
            'dinero_fisico' => 'required|numeric|min:0'
        ]);

        if (!$this->cajaAbierta) return;

        // Calculamos la diferencia final
        $diferenciaFinal = $this->dinero_fisico - $this->totalEfectivoEnCaja;

        $this->cajaAbierta->update([
            'fecha_cierre' => Carbon::now(),
            'monto_cierre' => $this->totalEfectivoEnCaja, // El teórico (Sistema)
            'monto_real'   => $this->dinero_fisico,       // El real (Mano)
            'diferencia'   => $diferenciaFinal,           // El cuadre
            'estado'       => 'cerrada',
            'id_usuario_cierre' => Auth::id()
        ]);

        $this->cajaAbierta = null;

        // Limpiamos variables
        $this->reset(['dinero_fisico', 'diferencia']);

        // Cerramos modal (si usas script)
        $this->dispatch('close-modal-cierre'); 

        session()->flash('message', 'Caja cerrada. Diferencia registrada: S/ ' . number_format($diferenciaFinal, 2));
    }

    // Esta función calcula la diferencia en tiempo real mientras escribes
    public function updatedDineroFisico()
    {
        // Validamos que sea número
        if(is_numeric($this->dinero_fisico)){
            // Diferencia = Lo que tienes en mano - Lo que dice el sistema
            $this->diferencia = floatval($this->dinero_fisico) - $this->totalEfectivoEnCaja;
        }
    }
}