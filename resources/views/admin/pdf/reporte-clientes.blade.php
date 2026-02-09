<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #333; }
        
        .header { text-align: center; margin-bottom: 15px; padding-bottom: 8px; border-bottom: 2px solid #212124; }
        .header h1 { font-size: 18px; color: #212124; margin-bottom: 3px; }
        .header p { font-size: 9px; color: #666; }
        
        .periodo { text-align: center; background: #f5f5f5; padding: 6px; margin-bottom: 15px; }
        .periodo strong { color: #212124; }
        
        .resumen { display: table; width: 100%; margin-bottom: 15px; }
        .resumen-item { display: table-cell; width: 33.33%; padding: 8px; text-align: center; border: 1px solid #ddd; background: #f9f9f9; }
        .resumen-label { font-size: 8px; color: #666; text-transform: uppercase; margin-bottom: 3px; }
        .resumen-value { font-size: 14px; font-weight: bold; color: #212124; }
        
        .section-title { background: #212124; color: white; padding: 6px 8px; font-size: 11px; font-weight: bold; margin: 15px 0 8px 0; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f5f5f5; padding: 6px; text-align: left; font-size: 9px; border-bottom: 2px solid #ddd; }
        td { padding: 6px; border-bottom: 1px solid #eee; font-size: 9px; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #28a745; font-weight: bold; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        .ranking { display: inline-block; width: 20px; height: 20px; background: #ffc107; color: #fff; text-align: center; line-height: 20px; border-radius: 50%; font-weight: bold; font-size: 9px; }
        .ranking.top3 { background: #28a745; }
        
        .procedencia-table { margin-top: 10px; }
        .procedencia-table td { padding: 4px 6px; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; padding: 8px 0; border-top: 1px solid #ddd; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>REPORTE DE CLIENTES FRECUENTES</h1>
        <p>Belén Spa - Análisis de Clientes</p>
    </div>

    {{-- PERIODO --}}
    <div class="periodo">
        <strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    {{-- RESUMEN --}}
    <div class="resumen">
        <div class="resumen-item">
            <div class="resumen-label">Total Clientes</div>
            <div class="resumen-value">{{ $totalClientes }}</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-label">Total Visitas</div>
            <div class="resumen-value">{{ $totalVisitas }}</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-label">Promedio Visitas</div>
            <div class="resumen-value">{{ number_format($promedioVisitas, 1) }}</div>
        </div>
    </div>

    {{-- TOP 20 CLIENTES --}}
    <div class="section-title">TOP 20 CLIENTES MÁS FRECUENTES</div>

    <table>
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 25%;">Cliente</th>
                <th style="width: 15%;">Teléfono</th>
                <th class="text-center" style="width: 8%;">Edad</th>
                <th class="text-center" style="width: 10%;">Visitas</th>
                <th class="text-right" style="width: 12%;">Total Gastado</th>
                <th class="text-center" style="width: 12%;">Última Visita</th>
                <th style="width: 13%;">Procedencia</th>
            </tr>
        </thead>
        <tbody>
            @forelse($topClientes as $index => $cliente)
                <tr>
                    <td class="text-center">
                        <span class="ranking {{ $index < 3 ? 'top3' : '' }}">{{ $index + 1 }}</span>
                    </td>
                    <td><strong>{{ $cliente->nombre }}</strong></td>
                    <td>{{ $cliente->telefono ?? '-' }}</td>
                    <td class="text-center">
                        @if($cliente->edad)
                            <span class="badge badge-info">{{ $cliente->edad }} años</span>
                        @else
                            <span style="color: #999;">-</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-warning">{{ $cliente->visitas }}</span>
                    </td>
                    <td class="text-right text-success">S/ {{ number_format($cliente->total_gastado, 2) }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($cliente->ultima_visita)->format('d/m/Y') }}</td>
                    <td>{{ $cliente->procedencia ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 20px; color: #999;">
                        No hay datos de clientes en este periodo
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- PROCEDENCIA DE CLIENTES --}}
    @if($procedencia->count() > 0)
        <div class="section-title">PROCEDENCIA DE CLIENTES</div>
        <table class="procedencia-table">
            <thead>
                <tr>
                    <th>Procedencia</th>
                    <th class="text-center">Cantidad de Clientes</th>
                    <th class="text-right">Porcentaje</th>
                </tr>
            </thead>
            <tbody>
                @php $totalProcedencia = $procedencia->sum('total'); @endphp
                @foreach($procedencia as $proc)
                    @php $porcentaje = $totalProcedencia > 0 ? ($proc->total / $totalProcedencia) * 100 : 0; @endphp
                    <tr>
                        <td><strong>{{ $proc->procedencia }}</strong></td>
                        <td class="text-center">{{ $proc->total }}</td>
                        <td class="text-right">{{ number_format($porcentaje, 1) }}%</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- NOTAS --}}
    <div style="margin-top: 20px; padding: 10px; background: #f9f9f9; border-left: 3px solid #ffc107;">
        <p style="font-size: 9px; margin-bottom: 5px;"><strong>Notas:</strong></p>
        <ul style="font-size: 8px; margin-left: 15px; color: #666;">
            <li>Este reporte muestra los 20 clientes con mayor cantidad de visitas en el periodo seleccionado.</li>
            <li>Los clientes en el Top 3 están destacados con color verde.</li>
            <li>Use esta información para programas de fidelización y campañas de marketing.</li>
        </ul>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Belén Spa System
    </div>
</body>
</html>
