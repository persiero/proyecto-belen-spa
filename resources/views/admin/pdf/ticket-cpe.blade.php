<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>CPE {{ $cpe->serie }}-{{ $cpe->correlativo }}</title>
    <style>
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11px; 
            margin: 0; 
            padding: 5px; 
            position: relative; 
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabla th { text-align: left; font-size: 9px; border-bottom: 1px solid #000; padding-bottom: 2px;}
        .tabla td { font-size: 10px; vertical-align: top; padding-top: 2px;}
        
        /* Marca de agua ANULADO */
        .anulado-watermark {
            position: absolute;
            top: 35%; left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 50px;
            color: rgba(255, 0, 0, 0.15);
            font-weight: bold;
            border: 4px solid rgba(255, 0, 0, 0.15);
            padding: 10px 40px;
            z-index: -1;
        }
        
        .logo { max-width: 150px; height: auto; margin-bottom: 8px; }
    </style>
</head>
<body>

    {{-- MARCA DE AGUA SI ESTÁ ANULADO --}}
    @if($cpe->estado == 'anulado' || (isset($venta) && $venta->estado == 'anulado'))
        <div class="anulado-watermark">ANULADO</div>
    @endif

    <div class="text-center">                
        <span class="bold" style="font-size: 13px">{{ $negocio->nombre_comercial }}</span><br>
        RUC: {{ $negocio->ruc }}<br>
        <span style="font-size: 10px;">{{ $negocio->direccion }}</span><br>
        <span style="font-size: 10px;">{{ $negocio->telefono }}</span><br>
    </div>

    <div class="line"></div>

    <div class="text-center">
        <span class="bold" style="font-size: 12px;">
            @if(str_starts_with($cpe->serie, 'F')) FACTURA ELECTRÓNICA
            @elseif(str_starts_with($cpe->serie, 'B')) BOLETA DE VENTA ELECTRÓNICA
            @else NOTA DE CRÉDITO
            @endif
        </span><br>
        <span style="font-size: 13px;">{{ $cpe->serie }} - {{ str_pad($cpe->correlativo, 8, '0', STR_PAD_LEFT) }}</span>
    </div>

    <div style="margin-top: 8px;">
        <strong>FECHA:</strong> {{ $cpe->fecha_emision->format('d/m/Y H:i:s') }}<br>
        <strong>CLIENTE:</strong> {{ $cpe->receptor_razon_social ?: 'PÚBLICO GENERAL' }}<br>
        
        @if($cpe->receptor_numero_doc)
            <strong>{{ strlen($cpe->receptor_numero_doc) == 11 ? 'RUC' : 'DNI' }}:</strong> {{ $cpe->receptor_numero_doc }}<br>
        @endif

        {{-- MOSTRAR DIRECCIÓN (Lógica: Si es Factura o si el campo tiene datos) --}}
        @if( (str_starts_with($cpe->serie, 'F') || $cpe->receptor_direccion) && $cpe->receptor_direccion != '-' )
            <strong>DIR:</strong> {{ Str::limit($cpe->receptor_direccion, 40) }}
        @endif
    </div>

    <div class="line"></div>

    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 10%">CANT</th>
                <th style="width: 65%">DESCRIPCIÓN</th>
                <th style="width: 25%" class="text-right">TOTAL</th>
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
        SON: {{ $cpe->total }} SOLES
    </div>

    {{-- BLOQUE SI ES NOTA DE CRÉDITO --}}
    @if($cpe->tipo_comprobante == '07')
        <div class="text-center" style="margin-top: 5px; border: 1px dashed #000; padding: 2px;">
            <strong>MOTIVO DE NOTA DE CRÉDITO</strong><br>
            Anulación de la operación
        </div>
    @endif

    <div class="text-center" style="margin-top: 10px">
        @if(isset($qr))
            <img src="data:image/svg+xml;base64,{{ $qr }}" width="90" style="margin-bottom: 5px;"><br>
        @endif
        
        <span style="font-size: 9px">Representación impresa del Comprobante Electrónico</span><br>
        @if($cpe->hash_cpe)
            <span style="font-size: 8px">Hash: {{ $cpe->hash_cpe }}</span><br>
        @endif
        <span style="font-size: 9px">Consulte en: portal.sunat.gob.pe</span>
    </div>
</body>
</html>