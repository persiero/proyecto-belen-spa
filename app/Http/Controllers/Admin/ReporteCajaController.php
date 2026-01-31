<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Pago;    
use App\Models\ConfigNegocio;
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteCajaController extends Controller
{
    public function imprimir($id)
    {
        $caja = Caja::with('usuarioApertura')->findOrFail($id);
        
        // 1. Obtener Pagos del turno (Agrupados por método)
        // Buscamos pagos realizados entre apertura y cierre (o ahora si sigue abierta)
        $fechaFin = $caja->fecha_cierre ?? now();
        
        $pagos = Pago::whereBetween('created_at', [$caja->fecha_apertura, $fechaFin])
            ->get();

        $resumenMetodos = $pagos->groupBy('metodoPago.nombre')
            ->map(function ($row) {
                return $row->sum('monto');
            });

        // 2. Gastos
        $gastos = MovimientoCaja::where('id_caja', $caja->id)
            ->where('tipo', 'egreso')
            ->get();

        // 3. Configuración del Negocio (Para el Logo y Datos)
        $config = ConfigNegocio::first();

        // 4. Generar PDF
        $pdf = Pdf::loadView('admin.pdf.cierre-caja', compact('caja', 'resumenMetodos', 'gastos', 'config'));
        
        // Formato Ticket 80mm x largo automático (900 es un buen límite vertical)
        $pdf->setPaper([0, 0, 226.77, 900], 'portrait');

        return $pdf->stream('Cierre-Caja-' . $caja->id . '.pdf');
    }
}
