<?php

namespace App\Livewire\Admin\Reportes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesDescargables extends Component
{
    public $fechaInicio;
    public $fechaFin;

    public function mount()
    {
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->format('Y-m-d');
    }

    public function descargarRentabilidadPDF()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        $datos = $this->calcularDatosRentabilidad($start, $end);

        $pdf = Pdf::loadView('admin.pdf.reporte-rentabilidad', $datos);
        $pdf->setPaper('a4', 'portrait');
        
        $nombreArchivo = 'Rentabilidad_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.pdf';
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }

    public function descargarVentasPDF()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        $datos = $this->calcularDatosVentas($start, $end);

        $pdf = Pdf::loadView('admin.pdf.reporte-ventas', $datos);
        $pdf->setPaper('a4', 'portrait');
        
        $nombreArchivo = 'Ventas_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.pdf';
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }

    public function descargarClientesPDF()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        $datos = $this->calcularDatosClientes($start, $end);

        $pdf = Pdf::loadView('admin.pdf.reporte-clientes', $datos);
        $pdf->setPaper('a4', 'portrait');
        
        $nombreArchivo = 'Clientes_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.pdf';
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }

    public function descargarCajaPDF()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        $datos = $this->calcularDatosCaja($start, $end);

        $pdf = Pdf::loadView('admin.pdf.reporte-caja', $datos);
        $pdf->setPaper('a4', 'landscape');
        
        $nombreArchivo = 'Caja_Mensual_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.pdf';
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }

    public function descargarCajaDiariaPDF()
    {
        $hoy = Carbon::today();
        $datos = $this->calcularDatosCajaDiaria($hoy);

        $pdf = Pdf::loadView('admin.pdf.reporte-caja-diaria', $datos);
        $pdf->setPaper('a4', 'portrait');
        
        $nombreArchivo = 'Caja_Diaria_' . $hoy->format('d-m-Y') . '.pdf';
        
        return response()->streamDownload(function() use ($pdf) {
            echo $pdf->output();
        }, $nombreArchivo);
    }

    private function calcularDatosRentabilidad($start, $end)
    {
        // Total Servicios
        $rankingServicios = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->join('servicios', 'detalles_venta.id_servicio', '=', 'servicios.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'servicio')
            ->select(
                'servicios.nombre',
                DB::raw('COUNT(*) as veces_realizado'),
                DB::raw('SUM(detalles_venta.subtotal) as total_generado')
            )
            ->groupBy('servicios.id', 'servicios.nombre')
            ->orderByDesc('total_generado')
            ->take(5)
            ->get();

        $totalServicios = $rankingServicios->sum('total_generado');

        // Costo Insumos
        $costoInsumosPeriodo = DB::table('movimientos_inventario')
            ->join('productos', 'movimientos_inventario.id_producto', '=', 'productos.id')
            ->whereBetween('movimientos_inventario.fecha', [$start, $end])
            ->where('movimientos_inventario.tipo', 'salida_insumo')
            ->whereIn('productos.tipo', ['insumo', 'mixto'])
            ->select(
                DB::raw('ABS(SUM(movimientos_inventario.cantidad * productos.costo_compra)) as costo_total')
            )
            ->value('costo_total') ?? 0;

        $gananciaNetaServicios = $totalServicios - $costoInsumosPeriodo;

        // Productos
        $topProductosRentables = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->join('productos', 'detalles_venta.id_producto', '=', 'productos.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'producto')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalles_venta.cantidad) as cantidad_vendida'),
                DB::raw('SUM(detalles_venta.subtotal) as total_venta'),
                DB::raw('SUM(detalles_venta.subtotal - (productos.costo_compra * detalles_venta.cantidad)) as ganancia_neta')
            )
            ->groupBy('productos.nombre', 'productos.id')
            ->orderByDesc('ganancia_neta')
            ->take(5)
            ->get();

        $totalVentaProductos = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'producto')
            ->sum('detalles_venta.subtotal');

        $costoProductosVendidos = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->join('productos', 'detalles_venta.id_producto', '=', 'productos.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'producto')
            ->select(
                DB::raw('SUM(productos.costo_compra * detalles_venta.cantidad) as costo_total')
            )
            ->value('costo_total') ?? 0;

        $gananciaNetaProductos = $totalVentaProductos - $costoProductosVendidos;

        return [
            'fechaInicio' => $start->format('d/m/Y'),
            'fechaFin' => $end->format('d/m/Y'),
            'totalServicios' => $totalServicios,
            'costoInsumosPeriodo' => $costoInsumosPeriodo,
            'gananciaNetaServicios' => $gananciaNetaServicios,
            'rankingServicios' => $rankingServicios,
            'totalVentaProductos' => $totalVentaProductos,
            'costoProductosVendidos' => $costoProductosVendidos,
            'gananciaNetaProductos' => $gananciaNetaProductos,
            'topProductosRentables' => $topProductosRentables,
        ];
    }

    private function calcularDatosVentas($start, $end)
    {
        // Obtener todas las ventas del periodo con sus detalles
        $ventas = DB::table('ventas')
            ->leftJoin('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->select(
                'ventas.id',
                'ventas.fecha',
                'ventas.total',
                DB::raw('COALESCE(clientes.nombre, "Público General") as cliente')
            )
            ->orderBy('ventas.fecha', 'desc')
            ->orderBy('ventas.created_at', 'desc')
            ->get();

        // Para cada venta, obtener sus detalles
        foreach ($ventas as $venta) {
            $detalles = DB::table('detalles_venta')
                ->leftJoin('servicios', function($join) {
                    $join->on('detalles_venta.id_servicio', '=', 'servicios.id')
                         ->where('detalles_venta.tipo_item', '=', 'servicio');
                })
                ->leftJoin('productos', function($join) {
                    $join->on('detalles_venta.id_producto', '=', 'productos.id')
                         ->where('detalles_venta.tipo_item', '=', 'producto');
                })
                ->leftJoin('estilistas', 'detalles_venta.id_estilista', '=', 'estilistas.id')
                ->where('detalles_venta.id_venta', $venta->id)
                ->select(
                    'detalles_venta.tipo_item',
                    'detalles_venta.cantidad',
                    'detalles_venta.subtotal',
                    DB::raw('COALESCE(servicios.nombre, productos.nombre) as nombre'),
                    DB::raw('COALESCE(estilistas.nombre, "-") as estilista')
                )
                ->get();
            
            $venta->detalles = $detalles;
        }

        // Métodos de pago
        $metodosPago = DB::table('pagos')
            ->join('ventas', 'pagos.id_venta', '=', 'ventas.id')
            ->join('metodos_pago', 'pagos.id_metodo_pago', '=', 'metodos_pago.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->select(
                'metodos_pago.nombre',
                DB::raw('SUM(pagos.monto) as total')
            )
            ->groupBy('metodos_pago.nombre')
            ->get();

        // Totales
        $totalGeneral = $ventas->sum('total');
        $cantidadVentas = $ventas->count();
        $ticketPromedio = $cantidadVentas > 0 ? ($totalGeneral / $cantidadVentas) : 0;

        return [
            'fechaInicio' => $start->format('d/m/Y'),
            'fechaFin' => $end->format('d/m/Y'),
            'ventas' => $ventas,
            'metodosPago' => $metodosPago,
            'totalGeneral' => $totalGeneral,
            'cantidadVentas' => $cantidadVentas,
            'ticketPromedio' => $ticketPromedio,
        ];
    }

    private function calcularDatosClientes($start, $end)
    {
        // Top 20 Clientes Frecuentes
        $topClientes = DB::table('ventas')
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->whereNotNull('ventas.id_cliente')
            ->select(
                'clientes.nombre',
                'clientes.telefono',
                'clientes.procedencia',
                DB::raw('TIMESTAMPDIFF(YEAR, clientes.fecha_nacimiento, CURDATE()) as edad'),
                DB::raw('COUNT(ventas.id) as visitas'),
                DB::raw('SUM(ventas.total) as total_gastado'),
                DB::raw('MAX(ventas.fecha) as ultima_visita')
            )
            ->groupBy('clientes.id', 'clientes.nombre', 'clientes.telefono', 'clientes.procedencia', 'clientes.fecha_nacimiento')
            ->orderByDesc('visitas')
            ->take(20)
            ->get();

        // Estadísticas generales
        $totalClientes = DB::table('ventas')
            ->whereBetween('fecha', [$start, $end])
            ->where('estado', 'pagada')
            ->whereNotNull('id_cliente')
            ->distinct('id_cliente')
            ->count('id_cliente');

        $totalVisitas = DB::table('ventas')
            ->whereBetween('fecha', [$start, $end])
            ->where('estado', 'pagada')
            ->whereNotNull('id_cliente')
            ->count();

        $promedioVisitas = $totalClientes > 0 ? ($totalVisitas / $totalClientes) : 0;

        // Procedencia
        $procedencia = DB::table('clientes')
            ->join('ventas', 'clientes.id', '=', 'ventas.id_cliente')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->whereNotNull('clientes.procedencia')
            ->select(
                'clientes.procedencia',
                DB::raw('COUNT(DISTINCT clientes.id) as total')
            )
            ->groupBy('clientes.procedencia')
            ->orderByDesc('total')
            ->get();

        return [
            'fechaInicio' => $start->format('d/m/Y'),
            'fechaFin' => $end->format('d/m/Y'),
            'topClientes' => $topClientes,
            'totalClientes' => $totalClientes,
            'totalVisitas' => $totalVisitas,
            'promedioVisitas' => $promedioVisitas,
            'procedencia' => $procedencia,
        ];
    }

    private function calcularDatosCaja($start, $end)
    {
        // Obtener todas las cajas del periodo
        $cajas = DB::table('caja')
            ->leftJoin('usuarios as u_apertura', 'caja.id_usuario_apertura', '=', 'u_apertura.id')
            ->leftJoin('usuarios as u_cierre', 'caja.id_usuario_cierre', '=', 'u_cierre.id')
            ->whereBetween('caja.fecha_apertura', [$start, $end])
            ->select(
                'caja.id',
                'caja.fecha_apertura',
                'caja.fecha_cierre',
                'caja.monto_apertura',
                'caja.monto_cierre',
                'caja.monto_real',
                'caja.diferencia',
                'caja.estado',
                'u_apertura.nombre as usuario_apertura',
                'u_cierre.nombre as usuario_cierre'
            )
            ->orderBy('caja.fecha_apertura', 'desc')
            ->get();

        // Obtener movimientos de caja del periodo (solo egresos)
        $movimientos = DB::table('movimientos_caja')
            ->leftJoin('usuarios', 'movimientos_caja.id_usuario', '=', 'usuarios.id')
            ->whereBetween('movimientos_caja.created_at', [$start, $end])
            ->where('movimientos_caja.tipo', 'egreso')
            ->select(
                'movimientos_caja.created_at',
                'movimientos_caja.monto',
                'movimientos_caja.descripcion',
                'usuarios.nombre as usuario'
            )
            ->orderBy('movimientos_caja.created_at', 'desc')
            ->get();

        // Calcular totales
        $totalAperturas = $cajas->sum('monto_apertura');
        $totalCierres = $cajas->sum('monto_cierre');
        $totalReal = $cajas->sum('monto_real');
        $totalDiferencias = $cajas->sum('diferencia');
        $totalEgresos = $movimientos->sum('monto');

        return [
            'fechaInicio' => $start->format('d/m/Y'),
            'fechaFin' => $end->format('d/m/Y'),
            'cajas' => $cajas,
            'movimientos' => $movimientos,
            'totalAperturas' => $totalAperturas,
            'totalCierres' => $totalCierres,
            'totalReal' => $totalReal,
            'totalDiferencias' => $totalDiferencias,
            'totalEgresos' => $totalEgresos,
        ];
    }

    private function calcularDatosCajaDiaria($fecha)
    {
        $start = $fecha->copy()->startOfDay();
        $end = $fecha->copy()->endOfDay();

        // Obtener cajas del día
        $cajas = DB::table('caja')
            ->leftJoin('usuarios as u_apertura', 'caja.id_usuario_apertura', '=', 'u_apertura.id')
            ->whereBetween('caja.fecha_apertura', [$start, $end])
            ->select(
                'caja.id',
                'caja.fecha_apertura',
                'caja.fecha_cierre',
                'caja.monto_apertura',
                'caja.monto_cierre',
                'caja.monto_real',
                'caja.diferencia',
                'caja.estado',
                'u_apertura.nombre as usuario_apertura'
            )
            ->orderBy('caja.fecha_apertura', 'asc')
            ->get();

        // Obtener movimientos de caja del día (solo egresos con descripción)
        $movimientos = DB::table('movimientos_caja')
            ->leftJoin('usuarios', 'movimientos_caja.id_usuario', '=', 'usuarios.id')
            ->whereBetween('movimientos_caja.created_at', [$start, $end])
            ->where('movimientos_caja.tipo', 'egreso')
            ->select(
                'movimientos_caja.created_at',
                'movimientos_caja.monto',
                'movimientos_caja.descripcion',
                'usuarios.nombre as usuario'
            )
            ->orderBy('movimientos_caja.created_at', 'asc')
            ->get();

        // Totales del día
        $totalAperturas = $cajas->sum('monto_apertura');
        $totalCierres = $cajas->sum('monto_cierre');
        $totalReal = $cajas->sum('monto_real');
        $totalDiferencias = $cajas->sum('diferencia');
        $totalEgresos = $movimientos->sum('monto');

        return [
            'fecha' => $fecha->format('d/m/Y'),
            'cajas' => $cajas,
            'movimientos' => $movimientos,
            'totalAperturas' => $totalAperturas,
            'totalCierres' => $totalCierres,
            'totalReal' => $totalReal,
            'totalDiferencias' => $totalDiferencias,
            'totalEgresos' => $totalEgresos,
            'cantidadCajas' => $cajas->count(),
        ];
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.reportes.reportes-descargables')
            ->with('titulo', 'Reportes Descargables');
    }
}
