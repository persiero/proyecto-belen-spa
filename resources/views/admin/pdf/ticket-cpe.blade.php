<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $cpe->serie }}-{{ $cpe->correlativo }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 5px; position: relative; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabla th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; }
        .tabla td { font-size: 11px; vertical-align: top; }
        
        /* MARCA DE AGUA PARA ANULADOS */
        .anulado-watermark {
            position: absolute;
            top: 30%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 40px;
            color: rgba(255, 0, 0, 0.2); /* Rojo muy transparente */
            font-weight: bold;
            border: 5px solid rgba(255, 0, 0, 0.2);
            padding: 10px;
            z-index: -1;
            pointer-events: none;
        }
    </style>
</head>
<body>

    {{-- MARCA DE AGUA SI ESTÁ ANULADO --}}
    @if($cpe->estado == 'anulado' || (isset($venta) && $venta->estado == 'anulado'))
        <div class="anulado-watermark">ANULADO</div>
    @endif

    <div class="text-center">
        {{-- <img src="{{ public_path('storage/logo.png') }}" class="logo" style="max-width: 150px;"> <br> --}}
        
        <span class="bold" style="font-size: 14px">{{ $negocio->nombre_comercial }}</span><br>
        RUC: {{ $negocio->ruc }}<br>
        {{ $negocio->direccion }}<br>
        {{ $negocio->telefono }}<br>
    </div>

    <div class="line"></div>

    <div class="text-center">
        <span class="bold" style="font-size: 13px;">
            {{-- LÓGICA INFALIBLE BASADA EN LA SERIE --}}
            @if(str_starts_with($cpe->serie, 'F'))
                FACTURA ELECTRÓNICA
            @elseif(str_starts_with($cpe->serie, 'B'))
                BOLETA DE VENTA ELECTRÓNICA
            @else
                TICKET DE VENTA
            @endif
        </span><br>
        <span style="font-size: 14px;">{{ $cpe->serie }} - {{ str_pad($cpe->correlativo, 8, '0', STR_PAD_LEFT) }}</span>
    </div>

    <div style="margin-top: 5px;">
        Fecha Emisión: {{ $cpe->fecha_emision->format('d/m/Y H:i:s') }}<br>
        
        {{-- CLIENTE --}}
        Cliente: {{ $cpe->receptor_razon_social ?: 'PÚBLICO GENERAL' }}<br>
        
        {{-- DOCUMENTO DEL CLIENTE --}}
        @if($cpe->receptor_numero_doc)
            {{ strlen($cpe->receptor_numero_doc) == 11 ? 'RUC:' : 'DNI:' }} {{ $cpe->receptor_numero_doc }}
        @endif
    </div>

    <div class="line"></div>

    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 10%">Cant.</th>
                <th style="width: 65%">Desc.</th>
                <th style="width: 25%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $item)
            <tr>
                <td style="text-align: center;">{{ $item->cantidad }}</td>
                <td>{{ $item->nombre_item }}</td>
                <td class="text-right">{{ number_format($item->subtotal, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="line"></div>

    <table style="width: 100%">
        <tr>
            <td class="text-right">Op. Gravada:</td>
            <td class="text-right" style="width: 30%">{{ number_format($cpe->op_gravadas, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right">I.G.V. (18%):</td>
            <td class="text-right">{{ number_format($cpe->monto_igv, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right bold" style="font-size: 13px">TOTAL A PAGAR:</td>
            <td class="text-right bold" style="font-size: 13px">S/ {{ number_format($cpe->total, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center" style="font-size: 10px;">
        {{-- LÓGICA BÁSICA PARA TEXTO DE MONEDA (O usa tu librería si tienes) --}}
        SON: {{ $cpe->total }} SOLES
    </div>

    {{-- SI ES NOTA DE CRÉDITO (Opcional, si llegas a implementar NC) --}}
    @if($cpe->tipo_comprobante == '07')
        <div class="text-center" style="margin-top: 5px;">
            <strong>NOTA DE CRÉDITO</strong><br>
            Motivo: Anulación de la operación
        </div>
    @endif

    <div class="text-center" style="margin-top: 10px">
        @if(isset($qr))
            <img src="data:image/svg+xml;base64,{{ $qr }}" width="100" style="margin-bottom: 5px;"><br>
        @endif
        
        <span style="font-size: 9px">Representación impresa del Comprobante Electrónico</span><br>
        
        @if($cpe->hash_cpe)
            <span style="font-size: 9px">Hash: {{ $cpe->hash_cpe }}</span><br>
        @endif
        
        <span style="font-size: 9px">Consulte en: portal.sunat.gob.pe</span>
        <br>
        <span style="font-size: 9px; font-style: italic;">¡Gracias por su preferencia!</span>
    </div>
</body>
</html>