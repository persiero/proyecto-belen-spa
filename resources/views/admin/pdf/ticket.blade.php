<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace; /* Fuente tipo ticket */
            font-size: 12px;
            margin: 0;
            padding: 5px;
            width: 80mm; /* Ancho estándar de ticket */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }
        .uppercase { text-transform: uppercase; }
        .divider { border-top: 1px dashed #000; margin: 5px 0; }
        
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        
        .header { margin-bottom: 10px; }
        .footer { margin-top: 10px; font-size: 10px; }
        
        /* Ocultar botón en impresión */
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    {{-- Botón para reimprimir si falla el auto-print --}}
    <div class="no-print" style="margin-bottom: 10px; text-align: center;">
        <button onclick="window.print()" style="padding: 10px 20px; cursor: pointer;">🖨️ IMPRIMIR</button>
    </div>

    <div class="header text-center">
        <h3 style="margin: 0;">{{ $config->nombre_comercial ?? 'SALÓN DE BELLEZA' }}</h3>
        <div>{{ $config->direccion ?? 'Av. Principal 123' }}</div>
        <div>RUC: {{ $config->ruc ?? '00000000000' }}</div>
        <div>Tel: {{ $config->telefono ?? '-' }}</div>
    </div>

    <div class="divider"></div>

    <div>
        <strong>TICKET: {{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</strong><br>
        FECHA: {{ $venta->fecha->format('d/m/Y H:i') }}<br>
        CLIENTE: {{ $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'PÚBLICO GENERAL' }}
        @if($venta->cliente && $venta->cliente->numero_documento)
            <br>DOC: {{ $venta->cliente->numero_documento }}
        @endif
    </div>

    <div class="divider"></div>

    <table>
        <thead>
            <tr style="border-bottom: 1px solid #000;">
                <th style="text-align: left;">CANT</th>
                <th style="text-align: left;">DESCRIPCIÓN</th>
                <th style="text-align: right;">IMPORTE</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $item)
            <tr>
                <td style="width: 10%;">{{ $item->cantidad }}</td>
                <td>
                    {{ $item->nombre_item }}
                    {{-- Mostrar Estilista si aplica (RF-TK-08) --}}
                    @if($item->tipo_item == 'servicio' && $item->servicio) 
                         {{-- Aquí tendrías que cruzar con turno_servicios si quieres el estilista exacto del item, 
                              o asumir el del turno. Por simplicidad mostramos nombre item --}}
                    @endif
                </td>
                <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="divider"></div>

    <table style="font-size: 14px;">
        <tr>
            <td class="text-right fw-bold">TOTAL A PAGAR:</td>
            <td class="text-right fw-bold">S/ {{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    <div class="divider"></div>

    <div>
        <strong>MÉTODOS DE PAGO:</strong><br>
        @foreach($venta->pagos as $pago)
            - {{ ucfirst($pago->metodoPago->nombre) }}: S/ {{ number_format($pago->monto, 2) }}<br>
        @endforeach
    </div>

    <div class="footer text-center">
        <p>¡Gracias por su preferencia!</p>
    </div>

</body>
</html>