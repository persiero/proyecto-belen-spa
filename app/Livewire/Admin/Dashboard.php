<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Venta;
use App\Models\Turno;
use App\Models\Producto;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\MovimientoCaja;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class Dashboard extends Component
{
    #[Layout('layouts.admin')]
    public function render()
    {
        $hoy = Carbon::today();
        $hace7Dias = Carbon::now()->subDays(7);

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

        // 4. Clientes Atendidos Hoy (CAMBIO: antes era nuevos del mes)
        $clientesAtendidosHoy = Turno::whereDate('created_at', $hoy)
            ->distinct('id_cliente')
            ->count('id_cliente');

        // 5. Alerta de Stock Bajo (Productos que necesitan reposición)
        $productosBajoStock = Producto::where('activo', true)
            ->where('tipo', '!=', 'insumo')
            ->whereColumn('stock_actual', '<=', 'stock_minimo')
            ->take(5)
            ->get();

        // 6. Últimas 10 Ventas (Para historial rápido)
        $ultimasVentas = Venta::with(['cliente', 'detalles.servicio', 'detalles.producto'])
            ->whereDate('fecha', $hoy)
            ->where('estado', 'pagada')
            ->latest()
            ->take(10)
            ->get();

        // 7. Movimientos de Caja Hoy
        // Ingresos = Ventas del día + Ingresos adicionales (movimientos_caja)
        $ingresosAdicionalesCaja = MovimientoCaja::whereDate('created_at', $hoy)
            ->where('tipo', 'ingreso')
            ->sum('monto');
        
        $ingresosCajaHoy = $totalVentasHoy + $ingresosAdicionalesCaja;
        
        $egresosCajaHoy = MovimientoCaja::whereDate('created_at', $hoy)
            ->where('tipo', 'egreso')
            ->sum('monto');
        
        $saldoCajaHoy = $ingresosCajaHoy - $egresosCajaHoy;

        // 8. Top Estilistas Hoy (por cantidad de servicios)
        $topEstilistasHoy = DB::table('turno_servicios')
            ->join('turnos', 'turno_servicios.id_turno', '=', 'turnos.id')
            ->join('estilistas', 'turno_servicios.id_estilista', '=', 'estilistas.id')
            ->whereDate('turnos.created_at', $hoy)
            ->whereNull('turnos.deleted_at')
            ->select('estilistas.nombre', DB::raw('COUNT(*) as total_servicios'))
            ->groupBy('estilistas.id', 'estilistas.nombre')
            ->orderByDesc('total_servicios')
            ->take(5)
            ->get();

        // 9. Top Servicio (últimos 7 días)
        $topServicio = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->join('servicios', 'detalles_venta.id_servicio', '=', 'servicios.id')
            ->where('detalles_venta.tipo_item', 'servicio')
            ->where('ventas.estado', 'pagada')
            ->where('ventas.created_at', '>=', $hace7Dias)
            ->whereNull('ventas.deleted_at')
            ->select(
                'servicios.nombre',
                DB::raw('SUM(detalles_venta.cantidad) as total_veces'),
                DB::raw('SUM(detalles_venta.subtotal) as total_ingresos')
            )
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('total_veces')
            ->first();

        // 10. Top Producto (últimos 7 días)
        $topProducto = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->join('productos', 'detalles_venta.id_producto', '=', 'productos.id')
            ->where('detalles_venta.tipo_item', 'producto')
            ->where('ventas.estado', 'pagada')
            ->where('ventas.created_at', '>=', $hace7Dias)
            ->whereNull('ventas.deleted_at')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalles_venta.cantidad) as total_ventas'),
                DB::raw('SUM(detalles_venta.subtotal) as total_ingresos')
            )
            ->groupBy('productos.id', 'productos.nombre')
            ->orderByDesc('total_ventas')
            ->first();

        // 11. Cumpleaños del Día
        $cumpleanosHoy = Cliente::whereNotNull('fecha_nacimiento')
            ->whereRaw('DAY(fecha_nacimiento) = DAY(CURDATE())')
            ->whereRaw('MONTH(fecha_nacimiento) = MONTH(CURDATE())')
            ->select('nombre', 'fecha_nacimiento', DB::raw('TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad'))
            ->get();

        // 12. Salidas de Insumos (últimos 7 días)
        $salidasInsumos = DB::table('movimientos_inventario')
            ->join('productos', 'movimientos_inventario.id_producto', '=', 'productos.id')
            ->where('movimientos_inventario.tipo', 'salida_insumo')
            ->whereIn('productos.tipo', ['insumo', 'mixto'])
            ->where('movimientos_inventario.fecha', '>=', Carbon::now()->subDays(7))
            ->select(
                'productos.nombre',
                'movimientos_inventario.cantidad',
                'movimientos_inventario.fecha',
                'movimientos_inventario.motivo'
            )
            ->orderByDesc('movimientos_inventario.fecha')
            ->take(5)
            ->get();

        return view('livewire.admin.dashboard', compact(
            'totalVentasHoy', 
            'cantidadVentas', 
            'turnosActivos', 
            'clientesAtendidosHoy',
            'productosBajoStock',
            'ultimasVentas',
            'ingresosCajaHoy',
            'egresosCajaHoy',
            'saldoCajaHoy',
            'topEstilistasHoy',
            'topServicio',
            'topProducto',
            'cumpleanosHoy',
            'salidasInsumos'
        ))->with('titulo', 'Panel de Control');
    }
}