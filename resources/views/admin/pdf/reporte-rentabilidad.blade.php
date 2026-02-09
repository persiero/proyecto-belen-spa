<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Rentabilidad</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #333; }
        
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #212124; }
        .header h1 { font-size: 20px; color: #212124; margin-bottom: 5px; }
        .header p { font-size: 10px; color: #666; }
        
        .periodo { text-align: center; background: #f5f5f5; padding: 8px; margin-bottom: 20px; border-radius: 4px; }
        .periodo strong { color: #212124; }
        
        .section { margin-bottom: 20px; }
        .section-title { background: #212124; color: white; padding: 8px 10px; font-size: 12px; font-weight: bold; margin-bottom: 10px; }
        
        .cards { display: table; width: 100%; margin-bottom: 15px; }
        .card { display: table-cell; width: 33.33%; padding: 10px; text-align: center; border: 1px solid #ddd; }
        .card.success { background: #d4edda; border-color: #c3e6cb; }
        .card.danger { background: #f8d7da; border-color: #f5c6cb; }
        .card.primary { background: #d1ecf1; border-color: #bee5eb; }
        .card-label { font-size: 9px; color: #666; text-transform: uppercase; margin-bottom: 5px; }
        .card-value { font-size: 16px; font-weight: bold; color: #212124; }
        .card-desc { font-size: 8px; color: #999; margin-top: 3px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        th { background: #f5f5f5; padding: 8px; text-align: left; font-size: 10px; border-bottom: 2px solid #ddd; }
        td { padding: 8px; border-bottom: 1px solid #eee; font-size: 10px; }
        tr:hover { background: #f9f9f9; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-success { color: #28a745; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 9px; color: #999; padding: 10px 0; border-top: 1px solid #ddd; }
        
        .badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 9px; font-weight: bold; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>REPORTE DE RENTABILIDAD</h1>
        <p>Belén Spa - Análisis de Ganancias</p>
    </div>

    {{-- PERIODO --}}
    <div class="periodo">
        <strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    {{-- SECCIÓN: RENTABILIDAD DE SERVICIOS --}}
    <div class="section">
        <div class="section-title">RENTABILIDAD DE SERVICIOS</div>
        
        <div class="cards">
            <div class="card success">
                <div class="card-label">Venta de Servicios</div>
                <div class="card-value">S/ {{ number_format($totalServicios, 2) }}</div>
                <div class="card-desc">Ingresos brutos</div>
            </div>
            <div class="card danger">
                <div class="card-label">Costo de Insumos</div>
                <div class="card-value">S/ {{ number_format($costoInsumosPeriodo, 2) }}</div>
                <div class="card-desc">Consumidos</div>
            </div>
            <div class="card primary">
                <div class="card-label">Ganancia Neta</div>
                <div class="card-value">S/ {{ number_format($gananciaNetaServicios, 2) }}</div>
                <div class="card-desc">Servicios - Insumos</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Top 5 Servicios</th>
                    <th class="text-center">Veces Realizado</th>
                    <th class="text-right">Total Generado</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rankingServicios as $servicio)
                    <tr>
                        <td>{{ $servicio->nombre }}</td>
                        <td class="text-center">
                            <span class="badge badge-info">{{ $servicio->veces_realizado }}</span>
                        </td>
                        <td class="text-right text-success">S/ {{ number_format($servicio->total_generado, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- SECCIÓN: RENTABILIDAD DE PRODUCTOS --}}
    <div class="section">
        <div class="section-title">RENTABILIDAD DE PRODUCTOS</div>
        
        <div class="cards">
            <div class="card success">
                <div class="card-label">Venta de Productos</div>
                <div class="card-value">S/ {{ number_format($totalVentaProductos, 2) }}</div>
                <div class="card-desc">Ingresos brutos</div>
            </div>
            <div class="card danger">
                <div class="card-label">Costo de Productos</div>
                <div class="card-value">S/ {{ number_format($costoProductosVendidos, 2) }}</div>
                <div class="card-desc">Costo de lo vendido</div>
            </div>
            <div class="card primary">
                <div class="card-label">Ganancia Neta</div>
                <div class="card-value">S/ {{ number_format($gananciaNetaProductos, 2) }}</div>
                <div class="card-desc">Venta - Costo</div>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Top 5 Productos</th>
                    <th class="text-center">Unidades Vendidas</th>
                    <th class="text-right">Ganancia Neta</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topProductosRentables as $prod)
                    <tr>
                        <td>{{ $prod->nombre }}</td>
                        <td class="text-center">
                            <span class="badge badge-warning">{{ $prod->cantidad_vendida }}</span>
                        </td>
                        <td class="text-right text-success">S/ {{ number_format($prod->ganancia_neta, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="3" class="text-center">Sin datos</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- RESUMEN FINAL --}}
    <div class="section">
        <div class="section-title">RESUMEN TOTAL</div>
        <table>
            <tr>
                <td><strong>Ganancia Neta de Servicios:</strong></td>
                <td class="text-right text-success"><strong>S/ {{ number_format($gananciaNetaServicios, 2) }}</strong></td>
            </tr>
            <tr>
                <td><strong>Ganancia Neta de Productos:</strong></td>
                <td class="text-right text-success"><strong>S/ {{ number_format($gananciaNetaProductos, 2) }}</strong></td>
            </tr>
            <tr style="background: #f5f5f5;">
                <td><strong>GANANCIA TOTAL DEL NEGOCIO:</strong></td>
                <td class="text-right" style="font-size: 14px; color: #28a745;"><strong>S/ {{ number_format($gananciaNetaServicios + $gananciaNetaProductos, 2) }}</strong></td>
            </tr>
        </table>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Belén Spa System
    </div>
</body>
</html>
