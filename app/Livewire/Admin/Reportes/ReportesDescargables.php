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

    public function descargarRentabilidadCSV()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // 1. OBTENER DATOS (Usamos la lógica existente)
        $datos = $this->calcularDatosRentabilidad($start, $end);

        $nombreArchivo = 'Rentabilidad_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.csv';

        return response()->streamDownload(function() use ($datos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM para UTF-8

            // --- SECCIÓN 1: RESUMEN GENERAL ---
            fputcsv($file, ['RESUMEN GENERAL DE RENTABILIDAD'], ',');
            fputcsv($file, ['Categoría', 'Ingresos Brutos', 'Costos/Insumos', 'Ganancia Neta'], ',');
            fputcsv($file, [
                'Servicios',
                number_format($datos['totalServicios'], 2, '.', ''),
                number_format($datos['costoInsumosPeriodo'], 2, '.', ''),
                number_format($datos['gananciaNetaServicios'], 2, '.', '')
            ], ',');
            fputcsv($file, [
                'Productos',
                number_format($datos['totalVentaProductos'], 2, '.', ''),
                number_format($datos['costoProductosVendidos'], 2, '.', ''),
                number_format($datos['gananciaNetaProductos'], 2, '.', '')
            ], ',');
            fputcsv($file, [], ','); // Fila vacía de separación

            // --- SECCIÓN 2: DETALLE DE SERVICIOS (Ranking) ---
            fputcsv($file, ['DETALLE DE SERVICIOS REALIZADOS'], ',');
            fputcsv($file, ['Nombre del Servicio', 'Veces Realizado', 'Total Generado (S/)'], ',');
            foreach ($datos['rankingServicios'] as $serv) {
                fputcsv($file, [
                    $serv->nombre,
                    $serv->veces_realizado,
                    number_format($serv->total_generado, 2, '.', '')
                ], ',');
            }
            fputcsv($file, [], ','); // Fila vacía

            // --- SECCIÓN 3: DETALLE DE PRODUCTOS (Margen de Ganancia) ---
            fputcsv($file, ['DETALLE DE PRODUCTOS VENDIDOS'], ',');
            fputcsv($file, ['Nombre del Producto', 'Cant. Vendida', 'Venta Total', 'Ganancia Neta'], ',');
            foreach ($datos['topProductosRentables'] as $prod) {
                fputcsv($file, [
                    $prod->nombre,
                    $prod->cantidad_vendida,
                    number_format($prod->total_venta, 2, '.', ''),
                    number_format($prod->ganancia_neta, 2, '.', '')
                ], ',');
            }

            fclose($file);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

    public function descargarVentasCSV()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // Reutilizamos tu función que ya trae los datos (incluyendo detalles)
        $datos = $this->calcularDatosVentas($start, $end);
        $ventas = $datos['ventas'];

        $nombreArchivo = 'Ventas_Detalladas_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.csv';

        return response()->streamDownload(function() use ($ventas) {
            $file = fopen('php://output', 'w');

            // BOM para UTF-8 (Tildes y eñes perfectas)
            fputs($file, "\xEF\xBB\xBF");

            // 1. Encabezados mucho más detallados
            fputcsv($file, [
                'ID Venta',
                'Fecha',
                'Cliente',
                'Tipo',
                'Producto / Servicio',
                'Estilista (Vendedor)',
                'Cantidad',
                'Subtotal Ítem (S/)',
                'Total del Ticket (S/)'
            ], ',');

            // 2. Recorremos las ventas
            foreach ($ventas as $venta) {
                $idVenta = '#' . str_pad($venta->id, 6, '0', STR_PAD_LEFT);
                $fecha = Carbon::parse($venta->fecha)->format('d/m/Y H:i');
                $cliente = $venta->cliente;
                $totalTicket = number_format($venta->total, 2, '.', '');

                // Si la venta tiene detalles (lo normal)
                if (count($venta->detalles) > 0) {
                    foreach ($venta->detalles as $det) {
                        fputcsv($file, [
                            $idVenta,
                            $fecha,
                            $cliente,
                            strtoupper($det->tipo_item), // SERVICIO o PRODUCTO
                            $det->nombre,
                            $det->estilista,
                            $det->cantidad,
                            number_format($det->subtotal, 2, '.', ''), // Subtotal de ese ítem
                            $totalTicket // El total global del ticket
                        ], ',');
                    }
                } else {
                    // Si por algún motivo hay una venta vacía sin detalles
                    fputcsv($file, [
                        $idVenta, $fecha, $cliente, '-', 'Sin detalles', '-', '0', '0.00', $totalTicket
                    ], ',');
                }
            }

            fclose($file);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

    public function descargarClientesCSV()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // Obtenemos los datos calculados
        $datos = $this->calcularDatosClientes($start, $end);

        $nombreArchivo = 'Clientes_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.csv';

        return response()->streamDownload(function() use ($datos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM para caracteres especiales (UTF-8)

            // --- SECCIÓN 1: ESTADÍSTICAS GENERALES ---
            fputcsv($file, ['ESTADÍSTICAS GENERALES DEL PERIODO'], ',');
            fputcsv($file, ['Total Clientes Atendidos', 'Total de Visitas (Tickets)', 'Promedio de Visitas por Cliente'], ',');
            fputcsv($file, [
                $datos['totalClientes'],
                $datos['totalVisitas'],
                number_format($datos['promedioVisitas'], 2, '.', '')
            ], ',');
            fputcsv($file, [], ','); // Fila vacía de separación

            // --- SECCIÓN 2: CANALES DE ADQUISICIÓN ---
            fputcsv($file, ['CANALES DE ADQUISICIÓN (NUEVOS CLIENTES)'], ',');
            fputcsv($file, ['Canal / Procedencia', 'Cantidad de Clientes Nuevos'], ',');
            foreach ($datos['procedencia'] as $proc) {
                fputcsv($file, [
                    $proc->procedencia,
                    $proc->total
                ], ',');
            }
            fputcsv($file, [], ','); // Fila vacía de separación

            // --- SECCIÓN 3: TOP 20 CLIENTES MÁS FRECUENTES ---
            fputcsv($file, ['TOP 20 CLIENTES MÁS FRECUENTES (VIP)'], ',');
            fputcsv($file, [
                'Nombre del Cliente',
                'Teléfono',
                'Edad',
                'Procedencia Original',
                'Visitas en el Periodo',
                'Total Gastado (S/)',
                'Última Visita'
            ], ',');

            foreach ($datos['topClientes'] as $cliente) {
                fputcsv($file, [
                    $cliente->nombre,
                    $cliente->telefono ?? '-', // Si no hay teléfono, pone un guion
                    $cliente->edad ? $cliente->edad . ' años' : '-',
                    $cliente->procedencia ?? 'No registrado',
                    $cliente->visitas,
                    number_format($cliente->total_gastado, 2, '.', ''),
                    Carbon::parse($cliente->ultima_visita)->format('d/m/Y')
                ], ',');
            }

            fclose($file);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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

    public function descargarCajaCSV()
    {
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // Obtenemos los datos calculados de cajas y movimientos (egresos)
        $datos = $this->calcularDatosCaja($start, $end);

        $nombreArchivo = 'Caja_Mensual_' . $start->format('d-m-Y') . '_' . $end->format('d-m-Y') . '.csv';

        return response()->streamDownload(function() use ($datos) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // BOM para UTF-8

            // --- SECCIÓN 1: RESUMEN DE TOTALES ---
            fputcsv($file, ['RESUMEN CONSOLIDADO DE CAJA'], ',');
            fputcsv($file, ['Concepto', 'Monto Total (S/)'], ',');
            fputcsv($file, ['Total Aperturas', number_format($datos['totalAperturas'], 2, '.', '')], ',');
            fputcsv($file, ['Total Cierres (Sistema)', number_format($datos['totalCierres'], 2, '.', '')], ',');
            fputcsv($file, ['Total Real (Físico)', number_format($datos['totalReal'], 2, '.', '')], ',');
            fputcsv($file, ['Diferencia Total (Sobra/Falta)', number_format($datos['totalDiferencias'], 2, '.', '')], ',');
            fputcsv($file, ['Total Egresos/Gastos', number_format($datos['totalEgresos'], 2, '.', '')], ',');
            fputcsv($file, [], ','); // Fila vacía

            // --- SECCIÓN 2: LISTADO DE CAJAS (AUDITORÍA) ---
            fputcsv($file, ['DETALLE DE APERTURAS Y CIERRES DE CAJA'], ',');
            fputcsv($file, [
                'Fecha Apertura',
                'Usuario Apertura',
                'Monto Apertura',
                'Fecha Cierre',
                'Usuario Cierre',
                'Monto Sistema',
                'Monto Real',
                'Diferencia',
                'Estado'
            ], ',');

            foreach ($datos['cajas'] as $caja) {
                fputcsv($file, [
                    Carbon::parse($caja->fecha_apertura)->format('d/m/Y H:i'),
                    $caja->usuario_apertura,
                    number_format($caja->monto_apertura, 2, '.', ''),
                    $caja->fecha_cierre ? Carbon::parse($caja->fecha_cierre)->format('d/m/Y H:i') : 'No cerrada',
                    $caja->usuario_cierre ?? '-',
                    number_format($caja->monto_cierre, 2, '.', ''),
                    number_format($caja->monto_real, 2, '.', ''),
                    number_format($caja->diferencia, 2, '.', ''),
                    strtoupper($caja->estado)
                ], ',');
            }
            fputcsv($file, [], ','); // Fila vacía

            // --- SECCIÓN 3: DETALLE DE EGRESOS ---
            fputcsv($file, ['DETALLE DE EGRESOS Y GASTOS EN EL PERIODO'], ',');
            fputcsv($file, ['Fecha/Hora', 'Usuario', 'Descripción/Motivo', 'Monto (S/)'], ',');
            foreach ($datos['movimientos'] as $mov) {
                fputcsv($file, [
                    Carbon::parse($mov->created_at)->format('d/m/Y H:i'),
                    $mov->usuario,
                    $mov->descripcion,
                    number_format($mov->monto, 2, '.', '')
                ], ',');
            }

            fclose($file);
        }, $nombreArchivo, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
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
        // 1. Ranking Top 5 Servicios (Para la tabla)
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

        // 2. Total Real de TODOS los Servicios (Para la caja verde)
        // Ya no dependemos del ->sum('total_generado') del ranking, porque eso solo suma 5.
        $totalServicios = DB::table('detalles_venta')
            ->join('ventas', 'detalles_venta.id_venta', '=', 'ventas.id')
            ->whereBetween('ventas.fecha', [$start, $end])
            ->where('ventas.estado', 'pagada')
            ->where('detalles_venta.tipo_item', 'servicio')
            ->sum('detalles_venta.subtotal') ?? 0;

        // 3. Costo Insumos (Corregido, sin filtrar por tipo de producto)
        $costoInsumosPeriodo = DB::table('movimientos_inventario')
            ->join('productos', 'movimientos_inventario.id_producto', '=', 'productos.id')
            ->whereBetween('movimientos_inventario.fecha', [$start, $end])
            ->where('movimientos_inventario.tipo', 'salida_insumo')
            // ELIMINADA LA RESTRICCIÓN: ->whereIn('productos.tipo', ['insumo', 'mixto'])
            ->select(
                DB::raw('ABS(SUM(movimientos_inventario.cantidad * productos.costo_compra)) as costo_total')
            )
            ->value('costo_total') ?? 0;

        // 4. Ganancia Neta Servicios (Ahora sí cuadrará con la web)
        $gananciaNetaServicios = $totalServicios - $costoInsumosPeriodo;

        // ==========================================
        // Productos (Esto estaba correcto, lo mantenemos igual)
        // ==========================================
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

        // Procedencia (Corregido: Solo canales de captación de Clientes Nuevos)
        // 1. Identificamos a los clientes cuya PRIMERA compra histórica fue en este periodo
        $clientesNuevosIds = DB::table('ventas')
            ->select('id_cliente', DB::raw('MIN(fecha) as primera_compra'))
            ->whereNotNull('id_cliente')
            ->where('estado', 'pagada')
            ->groupBy('id_cliente')
            ->havingBetween('primera_compra', [$start, $end])
            ->pluck('id_cliente');

        // 2. Agrupamos solo a esos clientes nuevos por su canal de procedencia
        $procedencia = DB::table('clientes')
            ->whereIn('id', $clientesNuevosIds)
            ->where('procedencia', '!=', 'Cliente Antiguo') // Filtramos etiquetas legacy
            ->whereNotNull('procedencia')
            ->select(
                'procedencia',
                DB::raw('COUNT(id) as total')
            )
            ->groupBy('procedencia')
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
