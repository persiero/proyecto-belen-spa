<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Comprobante, ConfigNegocio};
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Barryvdh\DomPDF\Facade\Pdf;

class ComprobanteController extends Controller
{
    public function imprimirTicket($idComprobante)
    {
        $comprobante = Comprobante::with(['venta.detalles', 'venta.cliente'])->findOrFail($idComprobante);
        
        if ($comprobante->ruta_pdf) {
            $path = 'comprobantes/pdf/' . $comprobante->ruta_pdf;
            
            if (\Illuminate\Support\Facades\Storage::exists($path)) {
                return response()->file(\Illuminate\Support\Facades\Storage::path($path));
            }
        }

        $negocio = ConfigNegocio::first();

        $textoQr = implode('|', [
            $negocio->ruc,
            $comprobante->tipo_comprobante == '01' ? '01' : '03',
            $comprobante->serie,
            $comprobante->correlativo,
            $comprobante->mto_igv ?? 0.00,
            $comprobante->total,
            $comprobante->fecha_emision->format('Y-m-d'),
            $comprobante->receptor_tipo_doc ?? '-',
            $comprobante->receptor_numero_doc ?? '-',
        ]);

        $qrImage = base64_encode(QrCode::format('svg')->size(120)->generate($textoQr));

        $pdf = Pdf::loadView('admin.pdf.ticket-cpe', [
            'cpe' => $comprobante,
            'venta' => $comprobante->venta,
            'negocio' => $negocio,
            'qr' => $qrImage
        ]);

        $pdf->setPaper([0, 0, 226.77, 600], 'portrait');

        return $pdf->stream('CPE-' . $comprobante->serie . '-' . $comprobante->correlativo . '.pdf');
    }

    public function descargarXml($id)
    {
        $comprobante = \App\Models\Comprobante::findOrFail($id);
        
        $path = 'comprobantes/xml/' . $comprobante->nombre_xml . '.xml';

        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404, 'El archivo XML no se encuentra.');
        }

        return \Illuminate\Support\Facades\Storage::download($path, $comprobante->nombre_xml . '.xml');
    }

    public function descargarCdr($id)
    {
        $comprobante = \App\Models\Comprobante::findOrFail($id);
        
        $nombreCdr = $comprobante->cdr_xml ?: ('R-' . $comprobante->nombre_xml . '.zip');
        $path = 'comprobantes/cdr/' . $nombreCdr;

        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404, 'El archivo CDR (Constancia de Recepción) no se encuentra.');
        }

        return \Illuminate\Support\Facades\Storage::download($path, $nombreCdr);
    }
}
