<?php

namespace App\Livewire\Admin\Reportes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Venta;
use App\Models\DetalleVenta;
use App\Models\Cliente;
use App\Models\Producto;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesPrincipal extends Component
{
    public $fechaInicio;
    public $fechaFin;
    
    public function mount()
    {
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedFechaInicio() { $this->actualizarGraficos(); }
    public function updatedFechaFin() { $this->actualizarGraficos(); }

    #[Layout('layouts.admin')]
    #[Title('Reportes y Analítica')]
    public function render()
    {
        // Ejecutamos los cálculos (La lógica está extraída para reutilizarla en el evento de actualización)
        $data = $this->calcularDatos();

        return view('livewire.admin.reportes.reportes-principal', $data);
    }

    // --- LÓGICA CENTRAL DE CÁLCULO ---
    private function calcularDatos()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // 1. KPI GENERAlES
        $ventasPeriodo = Venta::whereBetween('fecha', [$start, $end])->where('estado', 'pagada');
        $totalIngresos = $ventasPeriodo->sum('total');
        $cantidadTickets = $ventasPeriodo->count();
        $ticketPromedio = $cantidadTickets > 0 ? ($totalIngresos / $cantidadTickets) : 0;

        // 2. OPERATIVAS: Evolución Diaria
        $ventasDiarias = Venta::selectRaw('DATE(fecha) as fecha, SUM(total) as total')
            ->whereBetween('fecha', [$start, $end])->where('estado', 'pagada')
            ->groupBy('fecha')->orderBy('fecha')->get();

        // 3. FINANZAS: Métodos de Pago
        $metodosPago = DB::table('pagos')
            ->join('metodos_pago', 'pagos.id_metodo_pago', '=', 'metodos_pago.id')
            ->whereBetween('pagos.fecha', [$start, $end])
            ->select('metodos_pago.nombre', DB::raw('SUM(pagos.monto) as total'))
            ->groupBy('metodos_pago.nombre')->get();

        // 4. MARKETING: Procedencia (Usando tu campo 'procedencia')
        // Filtramos solo clientes que han comprado en este periodo o general (según prefieras)
        // Aquí analizo la base de datos completa de clientes para ver el perfil general
        $procedencia = Cliente::select('procedencia', DB::raw('count(*) as total'))
            ->whereNotNull('procedencia')
            ->groupBy('procedencia')
            ->orderByDesc('total')
            ->get();

        // 5. MARKETING: Edades (Usando 'fecha_nacimiento')
        // Calculamos la edad usando SQL puro para mayor rendimiento
        $edadesRaw = Cliente::select(DB::raw('TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad'))
            ->whereNotNull('fecha_nacimiento')
            ->get();
        
        // Agrupamos en rangos usando PHP (Más fácil de mantener)
        $rangosEdad = [
            '18-25' => 0, '26-35' => 0, '36-50' => 0, '50+' => 0
        ];
        foreach ($edadesRaw as $row) {
            if ($row->edad >= 18 && $row->edad <= 25) $rangosEdad['18-25']++;
            elseif ($row->edad >= 26 && $row->edad <= 35) $rangosEdad['26-35']++;
            elseif ($row->edad >= 36 && $row->edad <= 50) $rangosEdad['36-50']++;
            elseif ($row->edad > 50) $rangosEdad['50+']++;
        }

        // 6. INVENTARIO & MARGEN (Usando 'costo_compra')
        // Rentabilidad = (Precio Venta - Costo Compra) * Cantidad Vendida
        $topProductosRentables = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->join('productos', 'detalles_venta.id_producto', '=', 'productos.id') // Join para sacar el costo
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'producto')
            ->select(
                'productos.nombre',
                DB::raw('SUM(detalles_venta.cantidad) as cantidad_vendida'),
                DB::raw('SUM(detalles_venta.subtotal) as total_venta'),
                // Cálculo del Margen: (Subtotal Venta) - (Costo * Cantidad)
                DB::raw('SUM(detalles_venta.subtotal - (productos.costo_compra * detalles_venta.cantidad)) as ganancia_neta')
            )
            ->groupBy('productos.nombre', 'productos.id')
            ->orderByDesc('ganancia_neta')
            ->take(5)
            ->get();

        // 7. EQUIPO: Ranking Estilistas (Quién vendió más)
        $rankingEstilistas = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->leftJoin('estilistas', 'detalles_venta.id_estilista', '=', 'estilistas.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->select(
                DB::raw("COALESCE(estilistas.nombre, 'Sin Asignar / Venta Antigua') as nombre"), 
                DB::raw('SUM(detalles_venta.subtotal) as total_vendido')
            )
            ->groupBy('nombre')
            ->orderByDesc('total_vendido')
            ->get();

        // 8. CLIENTES: Top 10 Clientes Frecuentes (NUEVO)
        $topClientesFrecuentes = DB::table('ventas')
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->whereNotNull('ventas.id_cliente')
            ->select(
                'clientes.nombre',
                DB::raw('TIMESTAMPDIFF(YEAR, clientes.fecha_nacimiento, CURDATE()) as edad'),
                DB::raw('COUNT(ventas.id) as visitas'),
                DB::raw('SUM(ventas.total) as total_gastado')
            )
            ->groupBy('clientes.id', 'clientes.nombre', 'clientes.fecha_nacimiento')
            ->orderByDesc('visitas')
            ->take(10)
            ->get();

        // 9. RENTABILIDAD: Ranking de Servicios (NUEVO)
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

        // 10. RENTABILIDAD: Costo de Insumos Consumidos (NUEVO)
        // Suma de movimientos de salida tipo 'salida_insumo'
        $costoInsumosPeriodo = DB::table('movimientos_inventario')
            ->join('productos', 'movimientos_inventario.id_producto', '=', 'productos.id')
            ->whereBetween('movimientos_inventario.fecha', [$start, $end])
            ->where('movimientos_inventario.tipo', 'salida_insumo')
            ->whereIn('productos.tipo', ['insumo', 'mixto'])
            ->select(
                DB::raw('ABS(SUM(movimientos_inventario.cantidad * productos.costo_compra)) as costo_total')
            )
            ->value('costo_total') ?? 0;

        // 11. RENTABILIDAD: Totales de Productos Vendidos (NUEVO)
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

        // Totales para cálculo de ganancia neta de servicios
        $totalServicios = $rankingServicios->sum('total_generado');
        $gananciaNetaServicios = $totalServicios - $costoInsumosPeriodo;

        return compact(
            'totalIngresos', 'cantidadTickets', 'ticketPromedio', 
            'ventasDiarias', 'metodosPago', 'procedencia', 
            'rangosEdad', 'topProductosRentables', 'rankingEstilistas',
            'topClientesFrecuentes', 'rankingServicios', 
            'costoInsumosPeriodo', 'totalServicios', 'gananciaNetaServicios',
            'totalVentaProductos', 'costoProductosVendidos', 'gananciaNetaProductos'
        );
    }

    private function actualizarGraficos() 
    {
        $data = $this->calcularDatos();

        // Preparamos TODOS los datos para enviarlos a JS de un solo golpe
        $payload = [
            'ventasLabels' => $data['ventasDiarias']->pluck('fecha')->map(fn($d) => Carbon::parse($d)->format('d/m')),
            'ventasValues' => $data['ventasDiarias']->pluck('total'),
            
            'pagosLabels' => $data['metodosPago']->pluck('nombre'),
            'pagosValues' => $data['metodosPago']->pluck('total'),

            'procedenciaLabels' => $data['procedencia']->pluck('procedencia'),
            'procedenciaValues' => $data['procedencia']->pluck('total'),

            'edadLabels' => array_keys($data['rangosEdad']),
            'edadValues' => array_values($data['rangosEdad']),
        ];

        $this->dispatch('refresh-charts', data: $payload);
    }
}