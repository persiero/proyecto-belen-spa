<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Venta;
use App\Models\Turno;
use App\Models\Producto;
use App\Models\Cliente;
use Carbon\Carbon;

class Dashboard extends Component
{
    #[Layout('layouts.admin')]
    public function render()
    {
        $hoy = Carbon::today();

        // 1. Total Vendido Hoy (Dinero)
        $totalVentasHoy = Venta::whereDate('fecha', $hoy)
            ->where('estado', 'pagada')
            ->sum('total');

        // 2. Cantidad de Ventas Hoy (Tickets)
        $cantidadVentas = Venta::whereDate('fecha', $hoy)
            ->where('estado', 'pagada')
            ->count();

        // 3. Turnos Activos (Clientes atendiéndose ahora mismo)
        $turnosActivos = Turno::where('estado', 'activo')->count();

        // 4. Clientes Nuevos este Mes
        $clientesNuevos = Cliente::whereMonth('created_at', Carbon::now()->month)
            ->whereYear('created_at', Carbon::now()->year)
            ->count();

        // 5. Alerta de Stock Bajo (Productos que necesitan reposición)
        $productosBajoStock = Producto::where('activo', true)
            ->where('tipo', '!=', 'insumo') // Opcional: si quieres ver insumos quita esto
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->take(5) // Solo mostramos los 5 primeros para no saturar
            ->get();

        // 6. Últimas 5 Ventas (Para historial rápido)
        $ultimasVentas = Venta::with('cliente')
            ->where('estado', 'pagada')
            ->latest()
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', compact(
            'totalVentasHoy', 
            'cantidadVentas', 
            'turnosActivos', 
            'clientesNuevos',
            'productosBajoStock',
            'ultimasVentas'
        ))->with('titulo', 'Panel de Control');
    }
}