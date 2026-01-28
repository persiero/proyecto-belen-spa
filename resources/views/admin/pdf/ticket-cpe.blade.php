<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket {{ $cpe->serie }}-{{ $cpe->correlativo }}</title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; font-size: 12px; margin: 0; padding: 5px; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabla th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; }
        .tabla td { font-size: 11px; vertical-align: top; }
        .logo { max-width: 150px; margin-bottom: 5px; }
    </style>
</head>
<body>
    <div class="text-center">
        {{-- SI TIENES LOGO, DESCOMENTA ESTO --}}
        {{-- <img src="{{ public_path('storage/logo.png') }}" class="logo"> <br> --}}
        
        <span class="bold" style="font-size: 14px">{{ $negocio->nombre_comercial }}</span><br>
        RUC: {{ $negocio->ruc }}<br>
        {{ $negocio->direccion }}<br>
        {{ $negocio->telefono }}<br>
    </div>

    <div class="line"></div>

    <div class="text-center">
        <span class="bold">
            {{ $cpe->tipo_comprobante == '01' ? 'FACTURA ELECTRÓNICA' : 'BOLETA ELECTRÓNICA' }}
        </span><br>
        {{ $cpe->serie }} - {{ str_pad($cpe->correlativo, 8, '0', STR_PAD_LEFT) }}
    </div>

    <div style="margin-top: 5px;">
        Fecha: {{ $cpe->fecha_emision->format('d/m/Y H:i:s') }}<br>
        Cliente: {{ $cpe->receptor_razon_social ?: 'PÚBLICO GENERAL' }}<br>
        {{ $cpe->receptor_tipo_doc == '6' ? 'RUC: ' : 'DNI: ' }} {{ $cpe->receptor_numero_doc ?: '-' }}
    </div>

    <div class="line"></div>

    <table class="tabla">
        <thead>
            <tr>
                <th style="width: 10%">Cant.</th>
                <th style="width: 60%">Desc.</th>
                <th style="width: 30%" class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($venta->detalles as $item)
            <tr>
                <td>{{ $item->cantidad }}</td>
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
            <td class="text-right bold" style="font-size: 14px">TOTAL A PAGAR:</td>
            <td class="text-right bold" style="font-size: 14px">S/ {{ number_format($cpe->total, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center">
        SON: {{-- AQUÍ PODRÍAS USAR UNA LIBRERÍA NUMEROS A LETRAS --}}
        {{ $cpe->total }} SOLES
    </div>

    <div class="text-center" style="margin-top: 10px">
        <img src="data:image/svg+xml;base64,{{ $qr }}" width="100"><br>
        <span style="font-size: 9px">Representación impresa del Comprobante Electrónico</span><br>
        <span style="font-size: 9px">Código Hash: {{ $cpe->hash_cpe }}</span><br>
        <span style="font-size: 9px">Consulte en: portal.sunat.gob.pe</span>
    </div>
</body>
</html>