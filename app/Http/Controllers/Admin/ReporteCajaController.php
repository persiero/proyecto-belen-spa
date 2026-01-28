<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Pago;    
use Illuminate\Http\Request;

class ReporteCajaController extends Controller
{
    public function imprimir(Caja $caja)
    {
        // Recalcular métricas para el reporte estático
        $pagos = Pago::where('created_at', '>=', $caja->fecha_apertura)
            ->where('created_at', '<=', $caja->fecha_cierre ?? now())
            ->with('metodoPago')
            ->get();

        $resumenMetodos = $pagos->groupBy('metodoPago.nombre')->map(fn($row) => $row->sum('monto'));

        $gastos = MovimientoCaja::where('id_caja', $caja->id)->where('tipo', 'egreso')->get();
        $ingresos = MovimientoCaja::where('id_caja', $caja->id)->where('tipo', 'ingreso')->get();

        return view('admin.pdf.cierre-caja', compact('caja', 'resumenMetodos', 'gastos', 'ingresos'));
    }
}
