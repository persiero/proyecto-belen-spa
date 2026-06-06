<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\MovimientoCaja;
use App\Models\Pago;
use App\Models\ConfigNegocio;
use App\Models\MetodoPago; // <-- Necesario para inicializar todos los métodos
use Barryvdh\DomPDF\Facade\Pdf;

class ReporteCajaController extends Controller
{
    public function imprimir($id)
    {
        $caja = Caja::with('usuarioApertura')->findOrFail($id);

        // 1. Obtener Pagos del turno (Ignorando ventas anuladas, igual que en la web)
        $fechaFin = $caja->fecha_cierre ?? now();

        $pagos = Pago::whereBetween('created_at', [$caja->fecha_apertura, $fechaFin])
            ->whereHas('venta', function($q) {
                $q->where('estado', '!=', 'anulada'); // <-- CLAVE: Ignorar anuladas
            })
            ->with('metodoPago')
            ->get();

        // 2. Agrupar métodos de pago (Lógica idéntica a GestionCaja.php)
        $resumenMetodos = [];
        $metodosDb = MetodoPago::where('activo', true)->get();

        foreach($metodosDb as $m) {
            $resumenMetodos[$m->nombre] = 0;
        }

        foreach ($pagos as $pago) {
            $nombreMetodo = $pago->metodoPago->nombre;
            if (!isset($resumenMetodos[$nombreMetodo])) {
                $resumenMetodos[$nombreMetodo] = 0;
            }
            $resumenMetodos[$nombreMetodo] += $pago->monto;
        }

        // 3. Gastos
        $gastos = MovimientoCaja::where('id_caja', $caja->id)
            ->where('tipo', 'egreso')
            ->get();

        // 4. Configuración del Negocio (Para el Logo y Datos)
        $config = ConfigNegocio::first();

        // 5. Generar PDF
        $pdf = Pdf::loadView('admin.pdf.cierre-caja', compact('caja', 'resumenMetodos', 'gastos', 'config'));

        // Formato Ticket 80mm x largo automático (900 es un buen límite vertical)
        $pdf->setPaper([0, 0, 226.77, 900], 'portrait');

        return $pdf->stream('Cierre-Caja-' . $caja->id . '.pdf');
    }
}
