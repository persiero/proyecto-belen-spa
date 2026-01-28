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
        $negocio = ConfigNegocio::first();

        // 1. Generar Texto para el QR (Estándar SUNAT)
        // RUC | TIPO DOC | SERIE | CORRELATIVO | IGV | TOTAL | FECHA | TIPO DOC ADQUIRENTE | NUM DOC ADQUIRENTE
        $textoQr = implode('|', [
            $negocio->ruc,
            $comprobante->tipo_comprobante == '01' ? '01' : '03', // 01 Factura, 03 Boleta
            $comprobante->serie,
            $comprobante->correlativo,
            $comprobante->mto_igv ?? 0.00,
            $comprobante->total,
            $comprobante->fecha_emision->format('Y-m-d'),
            $comprobante->receptor_tipo_doc ?? '-',
            $comprobante->receptor_numero_doc ?? '-',
        ]);

        // 2. Generar QR en Base64 para incrustarlo en el PDF
        $qrImage = base64_encode(QrCode::format('svg')->size(120)->generate($textoQr));

        // 3. Renderizar vista PDF
        $pdf = Pdf::loadView('admin.pdf.ticket-cpe', [
            'cpe' => $comprobante,
            'venta' => $comprobante->venta,
            'negocio' => $negocio,
            'qr' => $qrImage
        ]);

        // Configuración para Ticket 80mm
        $pdf->setPaper([0, 0, 226.77, 600], 'portrait'); // ~80mm ancho, altura dinámica (ajustable)

        return $pdf->stream('CPE-' . $comprobante->serie . '-' . $comprobante->correlativo . '.pdf');
    }

    public function descargarXml($id)
    {
        $comprobante = \App\Models\Comprobante::findOrFail($id);
        
        // Reconstruimos la ruta del archivo
        $path = 'comprobantes/xml/' . $comprobante->nombre_xml . '.xml';

        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404, 'El archivo XML no se encuentra.');
        }

        return \Illuminate\Support\Facades\Storage::download($path);
    }

    public function descargarCdr($id)
    {
        $comprobante = \App\Models\Comprobante::findOrFail($id);
        
        // El CDR siempre empieza con "R-" y termina en .zip
        $nombreCdr = 'R-' . $comprobante->nombre_xml . '.zip';
        $path = 'comprobantes/cdr/' . $nombreCdr;

        if (!\Illuminate\Support\Facades\Storage::exists($path)) {
            abort(404, 'El archivo CDR (Constancia de Recepción) no se encuentra.');
        }

        return \Illuminate\Support\Facades\Storage::download($path);
    }
}
