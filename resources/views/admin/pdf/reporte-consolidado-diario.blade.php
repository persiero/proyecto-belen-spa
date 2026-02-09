<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Consolidado Diario de Caja</title>
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
        .resumen-item.cajas { background: #e7f3ff; border-color: #b3d9ff; }
        .resumen-item.aperturas { background: #fff3cd; border-color: #ffc107; }
        .resumen-item.real { background: #d1ecf1; border-color: #bee5eb; }
        .resumen-item.diferencias { background: #f8d7da; border-color: #f5c6cb; }
        .resumen-label { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .resumen-value { font-size: 16px; font-weight: bold; color: #212124; }
        
        .section-title { background: #212124; color: white; padding: 6px 8px; font-size: 11px; font-weight: bold; margin: 15px 0 8px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f5f5f5; padding: 6px; text-align: left; font-size: 9px; border-bottom: 2px solid #ddd; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        
        .monto-positivo { color: #28a745; font-weight: bold; }
        .monto-negativo { color: #dc3545; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; padding: 8px 0; border-top: 1px solid #ddd; }
        
        .total-row { background: #212124; color: white; font-weight: bold; }
        
        .info-box { margin-top: 20px; padding: 10px; background: #e7f3ff; border-left: 3px solid #007bff; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>REPORTE CONSOLIDADO DIARIO DE CAJA</h1>
        <p>Belén Spa - Resumen Total por Día</p>
    </div>

    {{-- PERIODO --}}
    <div class="periodo">
        <strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    {{-- RESUMEN GENERAL --}}
    <div class="resumen">
        <div class="resumen-item cajas">
            <div class="resumen-label">Total Cajas</div>
            <div class="resumen-value" style="color: #0056b3;">{{ $totalGeneral['cantidad_cajas'] }}</div>
        </div>
        <div class="resumen-item aperturas">
            <div class="resumen-label">Total Aperturas</div>
            <div class="resumen-value" style="color: #856404;">S/ {{ number_format($totalGeneral['total_aperturas'], 2) }}</div>
        </div>
        <div class="resumen-item real">
            <div class="resumen-label">Total Real</div>
            <div class="resumen-value" style="color: #0c5460;">S/ {{ number_format($totalGeneral['total_real'], 2) }}</div>
        </div>
        <div class="resumen-item diferencias">
            <div class="resumen-label">Diferencias</div>
            <div class="resumen-value" style="color: {{ $totalGeneral['total_diferencias'] == 0 ? '#28a745' : '#dc3545' }};">
                S/ {{ number_format($totalGeneral['total_diferencias'], 2) }}
            </div>
        </div>
    </div>

    {{-- DETALLE POR DÍA --}}
    <div class="section-title">CONSOLIDADO POR DÍA</div>

    <table>
        <thead>
            <tr>
                <th style="width: 15%;">Fecha</th>
                <th class="text-center" style="width: 10%;">Cajas</th>
                <th class="text-center" style="width: 10%;">Cerradas</th>
                <th class="text-right" style="width: 15%;">Total Aperturas</th>
                <th class="text-right" style="width: 15%;">Total Cierres</th>
                <th class="text-right" style="width: 15%;">Total Real</th>
                <th class="text-right" style="width: 12%;">Diferencias</th>
            </tr>
        </thead>
        <tbody>
            @forelse($consolidadoPorDia as $dia)
                <tr>
                    <td><strong>{{ \Carbon\Carbon::parse($dia->fecha)->format('d/m/Y') }}</strong></td>
                    <td class="text-center">{{ $dia->cantidad_cajas }}</td>
                    <td class="text-center">{{ $dia->cajas_cerradas }} / {{ $dia->cantidad_cajas }}</td>
                    <td class="text-right">S/ {{ number_format($dia->total_aperturas, 2) }}</td>
                    <td class="text-right">S/ {{ number_format($dia->total_cierres ?? 0, 2) }}</td>
                    <td class="text-right" style="font-weight: bold; color: #0c5460;">
                        S/ {{ number_format($dia->total_real ?? 0, 2) }}
                    </td>
                    <td class="text-right {{ $dia->total_diferencias == 0 ? 'monto-positivo' : 'monto-negativo' }}">
                        S/ {{ number_format($dia->total_diferencias ?? 0, 2) }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px; color: #999;">
                        No hay registros de caja en este periodo
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if($consolidadoPorDia->count() > 0)
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">TOTALES GENERALES:</td>
                    <td class="text-right">S/ {{ number_format($totalGeneral['total_aperturas'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalGeneral['total_cierres'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalGeneral['total_real'], 2) }}</td>
                    <td class="text-right">S/ {{ number_format($totalGeneral['total_diferencias'], 2) }}</td>
                </tr>
            </tfoot>
        @endif
    </table>

    {{-- INFORMACIÓN ADICIONAL --}}
    @if($consolidadoPorDia->count() > 0)
        <div class="info-box">
            <p style="font-size: 9px; margin-bottom: 5px;"><strong>Información del Reporte:</strong></p>
            <ul style="font-size: 8px; margin-left: 15px; color: #666;">
                <li>Este reporte consolida todas las cajas abiertas por día</li>
                <li>Incluye las aperturas y cierres de todas las cajeras</li>
                <li>El "Total Real" representa el dinero efectivo contado al cierre</li>
                <li>Las diferencias pueden indicar sobrantes (+) o faltantes (-)</li>
                <li>Total de días con movimiento: <strong>{{ $consolidadoPorDia->count() }}</strong></li>
                <li>Promedio de cajas por día: <strong>{{ number_format($totalGeneral['cantidad_cajas'] / $consolidadoPorDia->count(), 1) }}</strong></li>
            </ul>
        </div>
    @endif

    {{-- FOOTER --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Belén Spa System
    </div>
</body>
</html>
