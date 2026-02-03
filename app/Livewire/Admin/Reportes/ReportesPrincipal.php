<?php

namespace App\Livewire\Admin\Reportes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ReportesPrincipal extends Component
{
    // Filtros
    public $fechaInicio;
    public $fechaFin;
    
    public function mount()
    {
        // Por defecto: El mes actual
        $this->fechaInicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fechaFin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    // Detectar cambios en las fechas
    public function updatedFechaInicio() {
        $this->actualizarGrafico();
    }

    public function updatedFechaFin() {
        $this->actualizarGrafico();
    }

    #[Layout('layouts.admin')]
    #[Title('Reportes y Analítica')]
    public function render()
    {
        // Convertir a Carbon para consultas
        $start = Carbon::parse($this->fechaInicio)->startOfDay();
        $end = Carbon::parse($this->fechaFin)->endOfDay();

        // 1. KPIs Generales
        $ventasPeriodo = Venta::whereBetween('fecha', [$start, $end])
            ->where('estado', 'pagada');
            
        $totalIngresos = $ventasPeriodo->sum('total');
        $cantidadTickets = $ventasPeriodo->count();
        $ticketPromedio = $cantidadTickets > 0 ? ($totalIngresos / $cantidadTickets) : 0;

        // 2. Gráfico de Ventas (Agrupado por día)
        // Usamos selectRaw para que sea rápido y compatible con gráficos
        $ventasPorDia = Venta::selectRaw('DATE(fecha) as fecha, SUM(total) as total')
            ->whereBetween('fecha', [$start, $end])
            ->where('estado', 'pagada')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // 3. Top 5 Servicios Más Vendidos
        $topServicios = DetalleVenta::select('nombre_item', DB::raw('SUM(cantidad) as total_cantidad'), DB::raw('SUM(subtotal) as total_dinero'))
            ->whereHas('venta', function($q) use ($start, $end) {
                $q->whereBetween('fecha', [$start, $end])->where('estado', 'pagada');
            })
            ->where('tipo_item', 'servicio') // Solo servicios
            ->groupBy('nombre_item')
            ->orderByDesc('total_cantidad')
            ->take(5)
            ->get();

        // 4. Métodos de Pago (Para gráfico de torta)
        $metodosPago = DB::table('pagos')
            ->join('metodos_pago', 'pagos.id_metodo_pago', '=', 'metodos_pago.id')
            ->whereBetween('pagos.fecha', [$start, $end])
            ->select('metodos_pago.nombre', DB::raw('SUM(pagos.monto) as total'))
            ->groupBy('metodos_pago.nombre')
            ->get();

        return view('livewire.admin.reportes.reportes-principal', compact(
            'totalIngresos',
            'cantidadTickets',
            'ticketPromedio',
            'ventasPorDia',
            'topServicios',
            'metodosPago'
        ));
    }

    // Función privada para enviar los datos nuevos al JS
    private function actualizarGrafico() 
    {
        // 1. Recalculamos la misma consulta del gráfico
        $start = \Carbon\Carbon::parse($this->fechaInicio)->startOfDay();
        $end = \Carbon\Carbon::parse($this->fechaFin)->endOfDay();

        $ventasPorDia = Venta::selectRaw('DATE(fecha) as fecha, SUM(total) as total')
            ->whereBetween('fecha', [$start, $end])
            ->where('estado', 'pagada')
            ->groupBy('fecha')
            ->orderBy('fecha')
            ->get();

        // 2. Preparamos los arrays
        $labels = $ventasPorDia->pluck('fecha')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'));
        $values = $ventasPorDia->pluck('total');

        // 3. Enviamos el evento 'refresh-chart' al navegador con los datos nuevos
        $this->dispatch('refresh-chart', labels: $labels, values: $values);
    }
}