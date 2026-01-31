<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Ticket #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</title>
    <style>
        /* Estilos idénticos al ticket CPE para uniformidad */
        body { 
            font-family: 'Courier New', Courier, monospace; 
            font-size: 11px; 
            margin: 0; 
            padding: 5px; 
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
        .line { border-bottom: 1px dashed #000; margin: 5px 0; }
        
        .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabla th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; }
        .tabla td { font-size: 11px; vertical-align: top; }
        
        .logo { max-width: 120px; height: auto; margin-bottom: 5px; filter: grayscale(100%); }
    </style>
</head>
<body>

    <div class="text-center">
        {{-- LOGO SEGURO PARA PDF --}}
        @php
            // Ajusta aquí si tu imagen es .jpg o .png
            $pathLogo = public_path('adminlte/assets/img/Logo1.jpg'); 
        @endphp
        
        @if(file_exists($pathLogo))
            <img src="{{ $pathLogo }}" class="logo"><br>
        @endif

        <span class="bold" style="font-size: 14px">{{ $config->nombre_comercial ?? 'BELEN SPA' }}</span><br>
        RUC: {{ $config->ruc ?? '-' }}<br>
        {{ $config->direccion ?? '-' }}<br>
        {{ $config->telefono ?? '-' }}<br>
    </div>

    <div class="line"></div>

    <div class="text-center">
        <span class="bold" style="font-size: 14px;">TICKET DE VENTA</span><br>
        NRO: {{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
    </div>

    <div style="margin-top: 5px;">
        FECHA: {{ $venta->fecha->format('d/m/Y h:i A') }}<br>
        CLIENTE: {{ $venta->cliente ? $venta->cliente->nombre . ' ' . $venta->cliente->apellido : 'PÚBLICO GENERAL' }}
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
            <td class="text-right bold" style="font-size: 13px">TOTAL A PAGAR:</td>
            <td class="text-right bold" style="font-size: 13px">S/ {{ number_format($venta->total, 2) }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div style="font-size: 10px;">
        <strong>PAGADO CON:</strong><br>
        @foreach($venta->pagos as $pago)
            - {{ ucfirst($pago->metodoPago->nombre) }}: S/ {{ number_format($pago->monto, 2) }}<br>
        @endforeach
    </div>

    <div class="text-center" style="margin-top: 15px; font-size: 10px;">
        ¡Gracias por su visita!<br>
        Vuelva pronto.
    </div>

</body>
</html>