<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Ventas</title>
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
        
        .venta-item { border: 1px solid #ddd; margin-bottom: 8px; padding: 8px; background: #fafafa; page-break-inside: avoid; }
        .venta-header { display: table; width: 100%; margin-bottom: 5px; padding-bottom: 5px; border-bottom: 1px solid #ddd; }
        .venta-col { display: table-cell; vertical-align: top; }
        .venta-col.left { width: 60%; }
        .venta-col.right { width: 40%; text-align: right; }
        .venta-label { font-size: 8px; color: #999; text-transform: uppercase; }
        .venta-value { font-size: 10px; color: #333; font-weight: bold; }
        
        .detalle-table { width: 100%; border-collapse: collapse; margin-top: 5px; }
        .detalle-table th { background: #f0f0f0; padding: 4px; text-align: left; font-size: 8px; border-bottom: 1px solid #ddd; }
        .detalle-table td { padding: 4px; font-size: 9px; border-bottom: 1px solid #eee; }
        .detalle-table .text-right { text-align: right; }
        .detalle-table .text-center { text-align: center; }
        
        .metodos-pago { margin-top: 15px; }
        .metodos-table { width: 100%; border-collapse: collapse; }
        .metodos-table th { background: #f5f5f5; padding: 6px; text-align: left; font-size: 9px; border-bottom: 2px solid #ddd; }
        .metodos-table td { padding: 6px; font-size: 9px; border-bottom: 1px solid #eee; }
        .metodos-table .text-right { text-align: right; font-weight: bold; }
        
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 8px; color: #999; padding: 8px 0; border-top: 1px solid #ddd; }
        
        .badge { display: inline-block; padding: 2px 6px; border-radius: 3px; font-size: 8px; font-weight: bold; }
        .badge-service { background: #d1ecf1; color: #0c5460; }
        .badge-product { background: #fff3cd; color: #856404; }
        
        .total-general { background: #28a745; color: white; padding: 8px; text-align: right; font-size: 12px; font-weight: bold; margin-top: 10px; }
    </style>
</head>
<body>
    {{-- HEADER --}}
    <div class="header">
        <h1>REPORTE DE VENTAS DETALLADO</h1>
        <p>Belén Spa - Registro de Transacciones</p>
    </div>

    {{-- PERIODO --}}
    <div class="periodo">
        <strong>Periodo:</strong> {{ $fechaInicio }} - {{ $fechaFin }}
    </div>

    {{-- RESUMEN --}}
    <div class="resumen">
        <div class="resumen-item">
            <div class="resumen-label">Total Ventas</div>
            <div class="resumen-value">{{ $cantidadVentas }}</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-label">Ticket Promedio</div>
            <div class="resumen-value">S/ {{ number_format($ticketPromedio, 2) }}</div>
        </div>
        <div class="resumen-item">
            <div class="resumen-label">Total General</div>
            <div class="resumen-value" style="color: #28a745;">S/ {{ number_format($totalGeneral, 2) }}</div>
        </div>
    </div>

    {{-- LISTADO DE VENTAS --}}
    <div class="section-title">DETALLE DE TRANSACCIONES</div>

    @forelse($ventas as $venta)
        <div class="venta-item">
            <div class="venta-header">
                <div class="venta-col left">
                    <div class="venta-label">Fecha y Hora</div>
                    <div class="venta-value">{{ \Carbon\Carbon::parse($venta->fecha)->format('d/m/Y H:i') }}</div>
                </div>
                <div class="venta-col right">
                    <div class="venta-label">Cliente</div>
                    <div class="venta-value">{{ $venta->cliente }}</div>
                </div>
            </div>

            <table class="detalle-table">
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Descripción</th>
                        <th>Estilista</th>
                        <th class="text-center">Cant.</th>
                        <th class="text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($venta->detalles as $detalle)
                        <tr>
                            <td>
                                @if($detalle->tipo_item == 'servicio')
                                    <span class="badge badge-service">Servicio</span>
                                @else
                                    <span class="badge badge-product">Producto</span>
                                @endif
                            </td>
                            <td>{{ $detalle->nombre }}</td>
                            <td>{{ $detalle->estilista }}</td>
                            <td class="text-center">{{ $detalle->cantidad }}</td>
                            <td class="text-right">S/ {{ number_format($detalle->subtotal, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr style="background: #f5f5f5; font-weight: bold;">
                        <td colspan="4" class="text-right">TOTAL:</td>
                        <td class="text-right" style="color: #28a745;">S/ {{ number_format($venta->total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    @empty
        <p style="text-align: center; padding: 20px; color: #999;">No hay ventas en este periodo</p>
    @endforelse

    {{-- MÉTODOS DE PAGO --}}
    @if($metodosPago->count() > 0)
        <div class="metodos-pago">
            <div class="section-title">RESUMEN POR MÉTODO DE PAGO</div>
            <table class="metodos-table">
                <thead>
                    <tr>
                        <th>Método de Pago</th>
                        <th class="text-right">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metodosPago as $metodo)
                        <tr>
                            <td>{{ $metodo->nombre }}</td>
                            <td class="text-right">S/ {{ number_format($metodo->total, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- TOTAL GENERAL --}}
    <div class="total-general">
        TOTAL GENERAL DEL PERIODO: S/ {{ number_format($totalGeneral, 2) }}
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Generado el {{ now()->format('d/m/Y H:i') }} | Belén Spa System | Página <span class="pagenum"></span>
    </div>
</body>
</html>
