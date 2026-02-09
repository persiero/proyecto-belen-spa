<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Caja</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #212124; }
        .header h1 { font-size: 18px; color: #212124; margin-bottom: 3px; }
        .header p { font-size: 9px; color: #666; }
        
        .periodo { text-align: center; background: #f5f5f5; padding: 6px; margin-bottom: 15px; }
        .periodo strong { color: #212124; }
        
        .resumen { display: table; width: 100%; margin-bottom: 15px; }
        .resumen-item { display: table-cell; width: 25%; padding: 10px; text-align: center; border: 1px solid #ddd; }
        .resumen-item.apertura { background: #fff3cd; border-color: #ffc107; }
        .resumen-item.cierre { background: #d4edda; border-color: #c3e6cb; }
        .resumen-item.real { background: #d1ecf1; border-color: #bee5eb; }
        .resumen-item.diferencia { background: #f8d7da; border-color: #f5c6cb; }
        .resumen-label { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .resumen-value { font-size: 16px; font-weight: bold; color: #212124; }
        
        .section-title { background: #212124; color: white; padding: 6px 8px; font-size: 11px; font-weight: bold; margin: 15px 0 8px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f5f5f5; padding: 6px; text-align: left; font-size: 9px; border-bottom: 2px solid #ddd; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-abierta { background: #28a745; color: white; }
        .badge-cerrada { background: #6c757d; color: white; }
        
        .monto-positivo { color: #28a745; font-weight: bold; }
        .monto-negativo { color: #dc3545; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; padding: 8px 0; border-top: 1px solid #ddd; }
        
        .total-row { background: #f5f5f5; font-weight: bold; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>REPORTE MENSUAL DE CAJA</h1>
        <p>Belén Spa - Control Completo del Periodo</p>
    </div>

    {{-- PERIODO --}}
    <div class="periodo">
        <strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    {{-- RESUMEN --}}
    <div class="resumen">
        <div class="resumen-item apertura">
            <div class="resumen-label">Total Aperturas</div>
            <div class="resumen-value" style="color: #856404;">S/ {{ number_format($totalAperturas, 2) }}</div>
        </div>
        <div class="resumen-item cierre">
            <div class="resumen-label">Total Cierres</div>
            <div class="resumen-value" style="color: #155724;">S/ {{ number_format($totalCierres, 2) }}</div>
        </div>
        <div class="resumen-item real">
            <div class="resumen-label">Total Real</div>
            <div class="resumen-value" style="color: #0c5460;">S/ {{ number_format($totalReal, 2) }}</div>
        </div>
        <div class="resumen-item diferencia">
            <div class="resumen-label">Diferencias</div>
            <div class="resumen-value" style="color: {{ $totalDiferencias == 0 ? '#28a745' : '#dc3545' }};">
                S/ {{ number_format($totalDiferencias, 2) }}
            </div>
        </div>
    </div>

    {{-- DETALLE DE CAJAS --}}
    <div class="section-title">DETALLE DE APERTURAS Y CIERRES</div>

    <table>
        <thead>
            <tr>
                <th style="width: 12%;">Apertura</th>
                <th style="width: 12%;">Cierre</th>
                <th style="width: 15%;">Cajero(a)</th>
                <th class="text-right" style="width: 12%;">Monto Apertura</th>
                <th class="text-right" style="width: 12%;">Monto Cierre</th>
                <th class="text-right" style="width: 12%;">Monto Real</th>
                <th class="text-right" style="width: 10%;">Diferencia</th>
                <th class="text-center" style="width: 8%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cajas as $caja)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($caja->fecha_apertura)->format('d/m/Y H:i') }}</td>
                    <td>{{ $caja->fecha_cierre ? \Carbon\Carbon::parse($caja->fecha_cierre)->format('d/m/Y H:i') : '-' }}</td>
                    <td>{{ $caja->usuario_apertura ?? '-' }}</td>
                    <td class="text-right">S/ {{ number_format($caja->monto_apertura, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($caja->monto_cierre ?? 0, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($caja->monto_real ?? 0, 2) }}</td>
                    <td class="text-right {{ $caja->diferencia == 0 ? 'monto-positivo' : 'monto-negativo' }}">
                        S/ {{ number_format($caja->diferencia ?? 0, 2) }}
                    </td>
                    <td class="text-center">
                        @if($caja->estado == 'abierta')
                            <span class="badge badge-abierta">ABIERTA</span>
                        @else
                            <span class="badge badge-cerrada">CERRADA</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #999;">
                        No hay registros de caja en este periodo
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($cajas->count() > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTALES:</td>
                    <td class="text-right">S/ {{ number_format($totalAperturas, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalCierres, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalReal, 2) }}</td>
                    <td class="text-right {{ $totalDiferencias == 0 ? 'monto-positivo' : 'monto-negativo' }}">
                        S/ {{ number_format($totalDiferencias, 2) }}
                    </td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- ANÁLISIS --}}
    @if($cajas->count() > 0)
        <div style="margin-top: 20px; padding: 10px; background: #f9f9f9; border-left: 3px solid #17a2b8;">
            <p style="font-size: 9px; margin-bottom: 5px;"><strong>Análisis del Periodo:</strong></p>
            <ul style="font-size: 8px; margin-left: 15px; color: #666;">
                <li>Total de cajas registradas: <strong>{{ $cajas->count() }}</strong></li>
                <li>Cajas abiertas: <strong>{{ $cajas->where('estado', 'abierta')->count() }}</strong></li>
                <li>Cajas cerradas: <strong>{{ $cajas->where('estado', 'cerrada')->count() }}</strong></li>
                <li>Estado de diferencias: 
                    @if($totalDiferencias == 0)
                        <strong style="color: #28a745;">PERFECTO</strong> - Sin diferencias
                    @elseif($totalDiferencias > 0)
                        <strong style="color: #dc3545;">SOBRANTE</strong> - Hay S/ {{ number_format($totalDiferencias, 2) }} de más
                    @else
                        <strong style="color: #dc3545;">FALTANTE</strong> - Faltan S/ {{ number_format(abs($totalDiferencias), 2) }}
                    @endif
                </li>
            </ul>
        </div>
    @endif

    {{-- SALIDAS DE DINERO DEL PERIODO --}}
    @if($movimientos->count() > 0)
        <div style="page-break-before: always;"></div>
        
        <div class="section-title" style="margin-top: 0;">SALIDAS DE DINERO DEL PERIODO</div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 10%;">Fecha</th>
                    <th style="width: 7%;">Hora</th>
                    <th style="width: 15%;">Cajero(a)</th>
                    <th style="width: 48%;">Descripción</th>
                    <th class="text-right" style="width: 20%;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $mov)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mov->created_at)->format('d/m/Y') }}</td>
                        <td>{{ \Carbon\Carbon::parse($mov->created_at)->format('H:i') }}</td>
                        <td><strong>{{ $mov->usuario ?? 'Sin asignar' }}</strong></td>
                        <td>{{ $mov->descripcion ?? 'Sin descripción' }}</td>
                        <td class="text-right" style="color: #dc3545; font-weight: bold;">
                            - S/ {{ number_format($mov->monto, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="4" class="text-right">TOTAL EGRESOS DEL PERIODO:</td>
                    <td class="text-right">S/ {{ number_format($totalEgresos, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">
            <p style="font-size: 9px; color: #856404; margin: 0;">
                <strong>Nota:</strong> Estas son todas las salidas de dinero registradas en el periodo seleccionado. 
                Cada egreso debe estar respaldado con su comprobante correspondiente.
            </p>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Belén Spa System
    </div>
</body>
</html>
