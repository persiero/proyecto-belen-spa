<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Cierre #{{ str_pad($caja->id, 5, '0', STR_PAD_LEFT) }}</title>
    <style>
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
        .double-line { border-bottom: 3px double #000; margin: 5px 0; }
        
        .tabla { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .tabla th { text-align: left; font-size: 10px; border-bottom: 1px solid #000; }
        .tabla td { font-size: 11px; vertical-align: top; padding: 2px 0; }
        
        .logo { max-width: 120px; height: auto; margin-bottom: 5px; filter: grayscale(100%); }
    </style>
</head>
<body>

    <div class="text-center">
        {{-- LOGO --}}
        @php $pathLogo = public_path('adminlte/assets/img/Logo1.jpg'); @endphp {{-- Verifica si es .jpg o .png --}}
        @if(file_exists($pathLogo))
            <img src="{{ $pathLogo }}" class="logo"><br>
        @endif

        <span class="bold" style="font-size: 14px">{{ $config->nombre_comercial ?? 'BELEN SPA' }}</span><br>
        <span style="font-size: 10px">REPORTE DE CIERRE DE CAJA</span>
    </div>

    <div class="line"></div>

    <div>
        <strong>ID CIERRE:</strong> #{{ str_pad($caja->id, 5, '0', STR_PAD_LEFT) }}<br>
        <strong>CAJERO:</strong> {{ strtoupper($caja->usuarioApertura->name ?? 'SISTEMA') }}<br>
        <strong>APERTURA:</strong> {{ $caja->fecha_apertura->format('d/m/Y H:i') }}<br>
        <strong>CIERRE:</strong> {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'EN CURSO' }}
    </div>

    <div class="double-line"></div>
    <div class="text-center bold">RESUMEN DE INGRESOS (VENTAS)</div>
    <div class="line"></div>

    <table class="tabla">
        <tbody>
            @php $totalVentas = 0; @endphp
            @foreach($resumenMetodos as $metodo => $total)
            @php $totalVentas += $total; @endphp
            <tr>
                <td style="text-transform: capitalize;">{{ $metodo }}</td>
                <td class="text-right">S/ {{ number_format($total, 2) }}</td>
            </tr>
            @endforeach
            <tr style="border-top: 1px solid #000;">
                <td class="bold">TOTAL VENTAS</td>
                <td class="text-right bold">S/ {{ number_format($totalVentas, 2) }}</td>
            </tr>
        </tbody>
    </table>

    <br>

    {{-- DETALLE DE SALIDAS (GASTOS) --}}
    @if($gastos->count() > 0)
        <div class="text-center bold">SALIDAS DE EFECTIVO</div>
        <div class="line"></div>
        <table class="tabla">
            <tbody>
                @foreach($gastos as $gasto)
                <tr>
                    <td>
                        {{ \Illuminate\Support\Str::limit($gasto->descripcion, 20) }}
                        <div style="font-size: 9px; color: #555;">{{ $gasto->created_at->format('H:i') }}</div>
                    </td>
                    <td class="text-right text-top">S/ {{ number_format($gasto->monto, 2) }}</td>
                </tr>
                @endforeach
                <tr style="border-top: 1px solid #000;">
                    <td class="bold">TOTAL GASTOS</td>
                    <td class="text-right bold">S/ {{ number_format($gastos->sum('monto'), 2) }}</td>
                </tr>
            </tbody>
        </table>
        <br>
    @endif

    {{-- CUADRE DE CAJA (SOLO EFECTIVO) --}}
    <div class="double-line"></div>
    <div class="text-center bold" style="font-size: 13px;">ARQUEO DE EFECTIVO</div>
    <div class="line"></div>

    <table style="width: 100%;">
        <tr>
            <td>(+) Saldo Inicial:</td>
            <td class="text-right">S/ {{ number_format($caja->monto_apertura, 2) }}</td>
        </tr>
        <tr>
            {{-- Solo sumamos lo que entró en EFECTIVO --}}
            <td>(+) Ventas Efectivo:</td>
            <td class="text-right">S/ {{ number_format($resumenMetodos['efectivo'] ?? 0, 2) }}</td>
        </tr>
        <tr>
            <td>(-) Gastos/Retiros:</td>
            <td class="text-right">S/ {{ number_format($gastos->sum('monto'), 2) }}</td>
        </tr>
        <tr>
            <td colspan="2"><div class="line"></div></td>
        </tr>
        <tr style="font-size: 13px;">
            <td class="bold">EFECTIVO ESPERADO:</td>
            <td class="text-right bold">S/ {{ number_format($caja->monto_cierre, 2) }}</td> {{-- Asumiendo que guardaste el esperado aquí --}}
        </tr>
        <tr>
            <td class="bold">EFECTIVO EN CAJÓN:</td>
            <td class="text-right bold">S/ {{ number_format($caja->monto_real, 2) }}</td>
        </tr>
        <tr>
            <td colspan="2"><div class="line"></div></td>
        </tr>
        
        {{-- DIFERENCIA --}}
        <tr style="font-size: 14px;">
            <td class="bold">DIFERENCIA:</td>
            <td class="text-right bold">
                @if($caja->diferencia == 0)
                    OK
                @elseif($caja->diferencia > 0)
                    + S/ {{ number_format($caja->diferencia, 2) }}
                @else
                    S/ {{ number_format($caja->diferencia, 2) }}
                @endif
            </td>
        </tr>
    </table>

    <div class="line"></div>
    <br><br>
    <div class="text-center">
        __________________________<br>
        Firma Cajero/Responsable
    </div>

    <div class="text-center" style="margin-top: 15px; font-size: 10px;">
        Impreso el {{ now()->format('d/m/Y H:i:s') }}
    </div>

</body>
</html>