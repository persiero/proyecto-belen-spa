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
use Illuminate\Support\Str;
use App\Models\Comprobante;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportesPrincipal extends Component
{
    public $fechaInicio;
    public $fechaFin;

    public $mostrarModalNuevos = false;
    public $detallesClientesNuevos = [];

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

    // ==========================================
    // MODAL DE DETALLE DE CLIENTES NUEVOS
    // ==========================================
    public function abrirModalNuevos()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // 1. Obtenemos los IDs y la fecha de su primera compra
        $ventasNuevos = DB::table('ventas')
            ->select('id_cliente', DB::raw('MIN(fecha) as primera_compra'), DB::raw('SUM(total) as total_gastado'))
            ->whereNotNull('id_cliente')
            ->where('estado', 'pagada')
            ->groupBy('id_cliente')
            ->havingBetween('primera_compra', [$start, $end])
            ->get();

        $idsNuevos = $ventasNuevos->pluck('id_cliente');

        // 2. Traemos los datos de esos clientes (excluyendo 'Cliente Antiguo')
        $clientes = Cliente::whereIn('id', $idsNuevos)
            ->where('procedencia', '!=', 'Cliente Antiguo')
            ->orderBy('id', 'desc')
            ->get();

        // 3. Fusionamos los datos para tener la fecha de atención y lo que gastaron
        $this->detallesClientesNuevos = $clientes->map(function($cliente) use ($ventasNuevos) {
            $datosVenta = $ventasNuevos->firstWhere('id_cliente', $cliente->id);
            $cliente->fecha_primera_atencion = $datosVenta->primera_compra;
            $cliente->total_gastado = $datosVenta->total_gastado;
            return $cliente;
        });

        $this->mostrarModalNuevos = true;
    }

    public function cerrarModalNuevos()
    {
        $this->mostrarModalNuevos = false;
        $this->detallesClientesNuevos = [];
    }

    // ==========================================
    // EXPORTAR PDF DE CLIENTES NUEVOS
    // ==========================================
    public function exportarClientesNuevosPDF()
    {
        // Re-consultamos los datos brevemente para asegurarnos de que la info sea fresca y no sature la memoria de Livewire
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        $ventasNuevos = DB::table('ventas')
            ->select('id_cliente', DB::raw('MIN(fecha) as primera_compra'), DB::raw('SUM(total) as total_gastado'))
            ->whereNotNull('id_cliente')
            ->where('estado', 'pagada')
            ->groupBy('id_cliente')
            ->havingBetween('primera_compra', [$start, $end])
            ->get();

        $idsNuevos = $ventasNuevos->pluck('id_cliente');

        $clientes = Cliente::whereIn('id', $idsNuevos)
            ->where('procedencia', '!=', 'Cliente Antiguo')
            ->orderBy('id', 'desc')
            ->get()
            ->map(function($cliente) use ($ventasNuevos) {
                $datosVenta = $ventasNuevos->firstWhere('id_cliente', $cliente->id);
                $cliente->fecha_primera_atencion = $datosVenta->primera_compra;
                $cliente->total_gastado = $datosVenta->total_gastado;
                return $cliente;
            });

        // Generamos el PDF usando una vista Blade
        $pdf = Pdf::loadView('livewire.admin.reportes.pdf-clientes-nuevos', [
            'clientes' => $clientes,
            'fechaInicio' => $this->fechaInicio,
            'fechaFin' => $this->fechaFin,
        ]);

        // Formato para que se vea bien en horizontal (opcional, si hay muchas columnas)
        // $pdf->setPaper('A4', 'landscape');

        // Descargamos el archivo
        return response()->streamDownload(function () use ($pdf) {
            echo $pdf->stream();
        }, 'Reporte_Clientes_Nuevos_' . Carbon::now()->format('dmY_Hi') . '.pdf');
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
        $procedencia = DB::table('ventas')
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
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


        // CLIENTES NUEVOS EN EL PERIODO
        $clientesNuevos = DB::table('ventas')
            ->select('id_cliente', DB::raw('MIN(fecha) as primera_compra'))
            ->whereNotNull('id_cliente')
            ->where('estado', 'pagada')
            ->groupBy('id_cliente')
            ->havingBetween('primera_compra', [$start, $end])
            ->pluck('id_cliente');

        $totalClientesNuevos = $clientesNuevos->count();

        $procedenciaNuevos = DB::table('clientes')
            ->whereIn('id', $clientesNuevos)
            ->whereNotNull('procedencia')
            ->where('procedencia', '!=', 'Cliente Antiguo') // 🔥 excluir
            ->select('procedencia', DB::raw('COUNT(*) as total'))
            ->groupBy('procedencia')
            ->orderByDesc('total')
            ->get();

        $totalClientesPeriodo = DB::table('ventas')
            ->whereBetween('fecha', [$start, $end])
            ->where('estado', 'pagada')
            ->whereNotNull('id_cliente')
            ->distinct()
            ->count('id_cliente');

        $totalClientesNuevos = DB::table('clientes')
            ->whereIn('id', $clientesNuevos)
            ->where('procedencia', '!=', 'Cliente Antiguo')
            ->count();

        $totalRecurrentes = $totalClientesPeriodo - $totalClientesNuevos;

        $tasaCaptacion = $totalClientesPeriodo > 0
            ? ($totalClientesNuevos / $totalClientesPeriodo) * 100
            : 0;

        // 5. MARKETING: Edades (Usando 'fecha_nacimiento')
        // Calculamos la edad usando SQL puro para mayor rendimiento
        $edadesRaw = DB::table('ventas')
            ->join('clientes', 'ventas.id_cliente', '=', 'clientes.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->whereNotNull('clientes.fecha_nacimiento')
            ->select(
                DB::raw('TIMESTAMPDIFF(YEAR, clientes.fecha_nacimiento, CURDATE()) as edad'),
                DB::raw('clientes.id')
            )
            ->groupBy('clientes.id', 'clientes.fecha_nacimiento')
            ->get();

        $edadPromedio = $edadesRaw->avg('edad') ?? 0;

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

        $rangoDominante = collect($rangosEdad)->sortDesc()->keys()->first();
        $totalEdad = array_sum($rangosEdad);
        $porcentajeDominante = $totalEdad > 0
            ? ($rangosEdad[$rangoDominante] / $totalEdad) * 100
            : 0;

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

        // 8.1 CLIENTES: Próximos Cumpleaños (1 mes incluyendo hoy)
        $proximosCumpleanos = Cliente::whereNotNull('fecha_nacimiento')
            ->select(
                'nombre',
                'fecha_nacimiento',
                DB::raw('TIMESTAMPDIFF(YEAR, fecha_nacimiento, CURDATE()) as edad_actual'),
                DB::raw("
                    CASE
                        WHEN DATE_FORMAT(fecha_nacimiento, '%m-%d') >= DATE_FORMAT(CURDATE(), '%m-%d')
                        THEN DATE_FORMAT(CONCAT(YEAR(CURDATE()), '-', DATE_FORMAT(fecha_nacimiento, '%m-%d')), '%Y-%m-%d')
                        ELSE DATE_FORMAT(CONCAT(YEAR(CURDATE()) + 1, '-', DATE_FORMAT(fecha_nacimiento, '%m-%d')), '%Y-%m-%d')
                    END as proximo_cumple
                ")
            )
            ->havingRaw('DATEDIFF(proximo_cumple, CURDATE()) <= 30')
            ->orderByRaw('proximo_cumple')
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
        $totalServicios = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'servicio')
            ->sum('detalles_venta.subtotal') ?? 0;

        $gananciaNetaServicios = $totalServicios - $costoInsumosPeriodo;

        // 12. VENTAS: Comprobantes emitidos con totales (SOLO ACEPTADOS, SIN NOTAS DE CRÉDITO)
        $comprobantesPeriodo = DB::table('comprobantes')
            ->join('ventas', 'comprobantes.id_venta', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            // FILTRO 1: Solo contar los que SUNAT aceptó
            ->where('comprobantes.estado_sunat', 'aceptado')
            // FILTRO 2: Solo contar Facturas (1) y Boletas (2), excluyendo Notas de Crédito (3)
            ->whereIn('comprobantes.id_tipo_comprobante', [1, 2])
            ->select(
                'comprobantes.serie',
                'comprobantes.total' // Usamos el total del comprobante, es más preciso
            )
            ->get();

        // Facturas
        $totalFacturas = $comprobantesPeriodo
            ->filter(fn($c) => Str::startsWith($c->serie, 'F'));

        $cantidadFacturas = $totalFacturas->count();
        $montoFacturas = $totalFacturas->sum('total');

        // Boletas
        $totalBoletas = $comprobantesPeriodo
            ->filter(fn($c) => Str::startsWith($c->serie, 'B'));

        $cantidadBoletas = $totalBoletas->count();
        $montoBoletas = $totalBoletas->sum('total');

        // Totales generales
        $totalComprobantes = $cantidadFacturas + $cantidadBoletas;
        $montoTotalComprobantes = $montoFacturas + $montoBoletas;

        return compact(
            'totalIngresos', 'cantidadTickets', 'ticketPromedio',
            'ventasDiarias', 'metodosPago', 'procedencia',
            'rangosEdad', 'edadPromedio', 'topProductosRentables', 'rankingEstilistas',
            'topClientesFrecuentes', 'proximosCumpleanos', 'rankingServicios',
            'costoInsumosPeriodo', 'totalServicios', 'gananciaNetaServicios',
            'totalVentaProductos', 'costoProductosVendidos', 'gananciaNetaProductos',
            'totalClientesPeriodo', 'totalClientesNuevos', 'totalRecurrentes',
            'tasaCaptacion', 'procedenciaNuevos', 'cantidadFacturas',
            'montoFacturas', 'cantidadBoletas', 'montoBoletas',
            'totalComprobantes', 'montoTotalComprobantes'
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

            'procedenciaLabels' => $data['procedenciaNuevos']->pluck('procedencia'),
            'procedenciaValues' => $data['procedenciaNuevos']->pluck('total'),

            'edadLabels' => array_keys($data['rangosEdad']),
            'edadValues' => array_values($data['rangosEdad']),
        ];

        $this->dispatch('refresh-charts', data: $payload);
    }
}
