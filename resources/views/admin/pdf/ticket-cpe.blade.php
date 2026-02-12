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
            width: 100%; /* Asegura que el centro sea el centro del papel */
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabla th { text-align: left; font-size: 9px; border-bottom: 1px solid #000; padding-bottom: 2px;}
        .tabla td { font-size: 10px; vertical-align: top; padding-top: 2px;}
        
        /* Banner de anulado - Más visible y compatible con PDF */
        .anulado-banner {
            background-color: #ff0000;
            color: white;
            text-align: center;
            padding: 8px;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 10px;
            border: 3px solid #cc0000;
            letter-spacing: 3px;
        }
        
        .logo { max-width: 150px; height: auto; margin-bottom: 8px; }
    </style>
</head>
<body>

    {{-- BANNER DE ANULADO: Solo para Boletas/Facturas anuladas, NO para Notas de Crédito --}}
    @if($cpe->id_tipo_comprobante != 3 && ($cpe->estado_sunat == 'anulado' || (isset($venta) && $venta->estado == 'anulado')))
        <div class="anulado-banner">*** DOCUMENTO ANULADO ***</div>
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
            {{-- LÓGICA DE TÍTULO CORREGIDA: Usamos id_tipo_comprobante --}}
            @if($cpe->id_tipo_comprobante == 1) 
                FACTURA ELECTRÓNICA
            @elseif($cpe->id_tipo_comprobante == 2) 
                BOLETA DE VENTA ELECTRÓNICA
            @elseif($cpe->id_tipo_comprobante == 3) 
                NOTA DE CRÉDITO ELECTRÓNICA
            @else
                COMPROBANTE DE PAGO
            @endif
        </span><br>
        <span style="font-size: 13px;">{{ $cpe->serie }} - {{ str_pad($cpe->correlativo, 8, '0', STR_PAD_LEFT) }}</span>
    </div>

    <div style="margin-top: 8px;">
        <strong>FECHA EMISIÓN:</strong> {{ $cpe->fecha_emision->format('d/m/Y H:i:s') }}<br>
        <strong>MONEDA:</strong> {{ ($cpe->moneda == 'PEN' || $cpe->moneda == null) ? 'SOLES' : $cpe->moneda }}<br>
        
        <div class="line" style="margin: 2px 0; border-bottom: 1px dotted #ccc;"></div>

        <strong>CLIENTE:</strong> {{ $cpe->receptor_razon_social ?: 'PÚBLICO GENERAL' }}<br>
        <strong>{{ strlen($cpe->receptor_numero_doc ?? '') == 11 ? 'RUC' : 'DNI' }}:</strong> {{ $cpe->receptor_numero_doc ?: '00000000' }}<br>

        @if( ($cpe->id_tipo_comprobante == 1 || ($cpe->receptor_direccion && $cpe->receptor_direccion != '-')) )
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
            <td class="text-right bold" style="font-size: 13px">IMPORTE TOTAL:</td>
            <td class="text-right bold" style="font-size: 13px">S/ {{ number_format($cpe->total, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center" style="font-size: 10px; padding: 5px 0;">
        {{-- LEYENDA --}}
        <strong>{{ $cpe->leyenda_sunat }}</strong>
    </div>

    {{-- BLOQUE NOTA DE CRÉDITO --}}
    @if($cpe->id_tipo_comprobante == 3)
        <div style="margin-top: 8px; border: 2px solid #000; padding: 6px; background-color: #f9f9f9;">
            <div class="text-center bold" style="font-size: 11px; text-decoration: underline;">NOTA DE CRÉDITO</div>
            <div style="font-size: 10px; margin-top: 4px;">
                <strong>Motivo:</strong> {{ $cpe->descripcion_motivo_nc ?? 'Anulación de la operación' }}<br>
                <strong>Doc. Afectado:</strong> 
                @if(isset($cpe->comprobanteReferencia))
                    {{ $cpe->comprobanteReferencia->serie }}-{{ str_pad($cpe->comprobanteReferencia->correlativo, 8, '0', STR_PAD_LEFT) }}
                @else
                    N/A
                @endif
            </div>
        </div>
    @endif

    <div class="text-center" style="margin-top: 10px">
        @if(isset($qr))
            <img src="data:image/svg+xml;base64,{{ $qr }}" width="95" style="margin-bottom: 5px;"><br>
        @endif
        
        <span style="font-size: 9px">Representación impresa del Comprobante Electrónico</span><br>
        @if($cpe->hash_cpe)
            <span style="font-size: 8px">Hash: {{ $cpe->hash_cpe }}</span><br>
        @endif
        <span style="font-size: 9px">Autorizado por la SUNAT</span><br>
        <span style="font-size: 9px">Consulte en: portal.sunat.gob.pe</span>
    </div>

    {{-- INDICADOR FINAL DE ANULADO --}}
    @if($cpe->id_tipo_comprobante != 3 && ($cpe->estado_sunat == 'anulado' || (isset($venta) && $venta->estado == 'anulado')))
        <div style="margin-top: 10px; border: 2px solid #ff0000; padding: 5px; text-align: center; background-color: #ffe6e6;">
            <span style="color: #ff0000; font-weight: bold; font-size: 10px;">
                ⚠ ESTE DOCUMENTO HA SIDO ANULADO ⚠<br>
                <span style="font-size: 9px;">No tiene validez tributaria</span>
            </span>
        </div>
    @endif
</body>
</html>