<!DOCTYPE html>
<html>
<head>
    <title>Cierre de Caja #{{ $caja->id }}</title>
    <style>
        body { font-family: monospace; font-size: 12px; width: 300px; margin: 0 auto; }
        .header { text-align: center; border-bottom: 1px dashed #000; padding-bottom: 10px; margin-bottom: 10px; }
        .row { display: flex; justify-content: space-between; margin-bottom: 5px; }
        .total { font-weight: bold; font-size: 14px; border-top: 1px dashed #000; padding-top: 5px; }
        .footer { text-align: center; margin-top: 20px; font-size: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <strong>BELEN SPA</strong><br>
        REPORTE DE CIERRE DE CAJA<br>
        ID: #{{ str_pad($caja->id, 5, '0', STR_PAD_LEFT) }}<br>
        Apertura: {{ $caja->fecha_apertura->format('d/m/Y H:i') }}<br>
        Cierre: {{ $caja->fecha_cierre ? $caja->fecha_cierre->format('d/m/Y H:i') : 'EN CURSO' }}
    </div>

    <strong>RESUMEN DE VENTAS</strong>
    @foreach($resumenMetodos as $metodo => $total)
    <div class="row">
        <span>{{ ucfirst($metodo) }}</span>
        <span>S/ {{ number_format($total, 2) }}</span>
    </div>
    @endforeach

    <br><strong>MOVIMIENTOS DE CAJA</strong>
    <div class="row">
        <span>Saldo Inicial</span>
        <span>S/ {{ number_format($caja->monto_apertura, 2) }}</span>
    </div>
    <div class="row">
        <span>(-) Total Gastos</span>
        <span>S/ {{ number_format($gastos->sum('monto'), 2) }}</span>
    </div>

    <div class="total row">
        <span>EFECTIVO ESPERADO:</span>
        <span>S/ {{ number_format($caja->monto_cierre, 2) }}</span>
    </div>
    <div class="row">
        <span>EFECTIVO REAL:</span>
        <span>S/ {{ number_format($caja->monto_real, 2) }}</span>
    </div>
    <div class="row" style="margin-top: 5px;">
        <span>DIFERENCIA:</span>
        <span>S/ {{ number_format($caja->diferencia, 2) }}</span>
    </div>

    <div class="footer">
        Cajero: {{ $caja->usuarioApertura->name ?? 'Sistema' }}<br>
        {{ now() }}
    </div>
</body>
</html>