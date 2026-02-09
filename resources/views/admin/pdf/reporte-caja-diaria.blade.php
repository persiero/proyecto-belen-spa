<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Diario de Caja</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 3px solid #007bff; }
        .header h1 { font-size: 22px; color: #007bff; margin-bottom: 5px; }
        .header .fecha { font-size: 16px; color: #666; font-weight: bold; }
        
        .resumen-destacado { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
        }
        .resumen-destacado h2 { font-size: 14px; margin-bottom: 15px; opacity: 0.9; }
        .resumen-grid { display: table; width: 100%; }
        .resumen-col { display: table-cell; width: 25%; padding: 10px; text-align: center; }
        .resumen-label { font-size: 9px; opacity: 0.8; margin-bottom: 5px; }
        .resumen-valor { font-size: 20px; font-weight: bold; }
        
        .section-title { 
            background: #007bff; 
            color: white; 
            padding: 8px 10px; 
            font-size: 12px; 
            font-weight: bold; 
            margin: 20px 0 10px 0;
            border-radius: 4px;
        }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { 
            background: #f8f9fa; 
            padding: 10px 8px; 
            text-align: left; 
            font-size: 10px; 
            border-bottom: 2px solid #dee2e6;
            color: #495057;
            font-weight: bold;
        }
        td { padding: 10px 8px; border-bottom: 1px solid #e9ecef; font-size: 10px; }
        tr:nth-child(even) { background: #f8f9fa; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .badge { 
            display: inline-block; 
            padding: 3px 8px; 
            border-radius: 4px; 
            font-size: 9px; 
            font-weight: bold; 
        }
        .badge-abierta { background: #28a745; color: white; }
        .badge-cerrada { background: #6c757d; color: white; }
        
        .monto-positivo { color: #28a745; font-weight: bold; }
        .monto-negativo { color: #dc3545; font-weight: bold; }
        .monto-neutro { color: #6c757d; font-weight: bold; }
        
        .total-row { 
            background: #007bff !important; 
            color: white; 
            font-weight: bold; 
            font-size: 11px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #007bff;
            padding: 12px;
            margin-top: 20px;
        }
        
        .footer { 
            position: fixed; 
            bottom: 0; 
            width: 100%; 
            text-align: center; 
            font-size: 9px; 
            color: #999; 
            padding: 10px 0; 
            border-top: 1px solid #ddd; 
        }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>REPORTE DIARIO DE CAJA</h1>
        <p class="fecha">{{ $fecha }}</p>
        <p style="font-size: 10px; color: #999; margin-top: 5px;">Belén Spa</p>
    </div>

    {{-- RESUMEN DESTACADO --}}
    <div class="resumen-destacado">
        <h2>RESUMEN DEL DÍA</h2>
        <div class="resumen-grid">
            <div class="resumen-col">
                <div class="resumen-label">Cajas Abiertas</div>
                <div class="resumen-valor">{{ $cantidadCajas }}</div>
            </div>
            <div class="resumen-col">
                <div class="resumen-label">Total Aperturas</div>
                <div class="resumen-valor">S/ {{ number_format($totalAperturas, 2) }}</div>
            </div>
            <div class="resumen-col">
                <div class="resumen-label">Total Real</div>
                <div class="resumen-valor">S/ {{ number_format($totalReal, 2) }}</div>
            </div>
            <div class="resumen-col">
                <div class="resumen-label">Diferencias</div>
                <div class="resumen-valor">S/ {{ number_format($totalDiferencias, 2) }}</div>
            </div>
        </div>
    </div>

    {{-- DETALLE DE CAJAS --}}
    <div class="section-title">DETALLE DE CAJAS DEL DÍA</div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Hora Apertura</th>
                <th style="width: 20%;">Cajero(a)</th>
                <th class="text-right" style="width: 13%;">Apertura</th>
                <th class="text-right" style="width: 13%;">Cierre</th>
                <th class="text-right" style="width: 13%;">Real</th>
                <th class="text-right" style="width: 13%;">Diferencia</th>
                <th class="text-center" style="width: 13%;">Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse($cajas as $caja)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($caja->fecha_apertura)->format('H:i') }}</td>
                    <td><strong>{{ $caja->usuario_apertura ?? 'Sin asignar' }}</strong></td>
                    <td class="text-right">S/ {{ number_format($caja->monto_apertura, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($caja->monto_cierre ?? 0, 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #007bff;">
                        S/ {{ number_format($caja->monto_real ?? 0, 2) }}
                    </td>
                    <td class="text-right {{ $caja->diferencia == 0 ? 'monto-neutro' : ($caja->diferencia > 0 ? 'monto-positivo' : 'monto-negativo') }}">
                        {{ $caja->diferencia > 0 ? '+' : '' }}S/ {{ number_format($caja->diferencia ?? 0, 2) }}
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
                    <td colspan="7" class="text-center" style="padding: 30px; color: #999;">
                        No hay cajas registradas para el día de hoy
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($cajas->count() > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="2" class="text-right">TOTALES DEL DÍA:</td>
                    <td class="text-right">S/ {{ number_format($totalAperturas, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalCierres, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalReal, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalDiferencias, 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- INFORMACIÓN --}}
    @if($cajas->count() > 0)
        <div class="info-box">
            <p style="font-size: 10px; margin-bottom: 8px;"><strong>Información:</strong></p>
            <ul style="font-size: 9px; margin-left: 20px; color: #666; line-height: 1.6;">
                <li>Cajas cerradas: <strong>{{ $cajas->where('estado', 'cerrada')->count() }}</strong> de <strong>{{ $cantidadCajas }}</strong></li>
                <li>Estado de diferencias: 
                    @if($totalDiferencias == 0)
                        <strong style="color: #28a745;">PERFECTO</strong> - Sin diferencias
                    @elseif($totalDiferencias > 0)
                        <strong style="color: #ffc107;">SOBRANTE</strong> - Hay S/ {{ number_format($totalDiferencias, 2) }} de más
                    @else
                        <strong style="color: #dc3545;">FALTANTE</strong> - Faltan S/ {{ number_format(abs($totalDiferencias), 2) }}
                    @endif
                </li>
                <li>Total en efectivo del día: <strong style="color: #007bff;">S/ {{ number_format($totalReal, 2) }}</strong></li>
            </ul>
        </div>
    @endif

    {{-- MOVIMIENTOS DE CAJA (EGRESOS) --}}
    @if($movimientos->count() > 0)
        <div class="section-title" style="margin-top: 25px;">SALIDAS DE DINERO DEL DÍA</div>
        
        <table>
            <thead>
                <tr>
                    <th style="width: 12%;">Hora</th>
                    <th style="width: 18%;">Cajero(a)</th>
                    <th style="width: 50%;">Descripción</th>
                    <th class="text-right" style="width: 20%;">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach($movimientos as $mov)
                    <tr>
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
                    <td colspan="3" class="text-right">TOTAL EGRESOS:</td>
                    <td class="text-right">S/ {{ number_format($totalEgresos, 2) }}</td>
                </tr>
            </tfoot>
        </table>

        <div style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 10px; margin-top: 10px;">
            <p style="font-size: 9px; color: #856404; margin: 0;">
                <strong>Nota:</strong> Estas son las salidas de dinero registradas durante el día. 
                El cajero debe justificar cada egreso con el comprobante correspondiente.
            </p>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Belén Spa System
    </div>
</body>
</html>
