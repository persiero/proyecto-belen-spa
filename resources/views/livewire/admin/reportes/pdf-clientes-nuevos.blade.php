<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Clientes Nuevos</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; color: #1a1a1a; }
        .header p { margin: 5px 0; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th { background-color: #6f42c1; color: white; padding: 10px; text-align: left; font-size: 11px; text-transform: uppercase;}
        td { padding: 8px; border-bottom: 1px solid #ddd; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .total-row { font-weight: bold; background-color: #f8f9fa; }
    </style>
</head>
<body>

    <div class="header">
        <h2>Reporte de Clientes Nuevos Captados</h2>
        <p>Belén System Spa</p>
        <p>Periodo: {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>N°</th>
                <th>Cliente</th>
                <th>Teléfono</th>
                <th class="text-center">Canal</th>
                <th class="text-center">1ra Atención</th>
                <th class="text-end">Total Gastado</th>
            </tr>
        </thead>
        <tbody>
            @php $totalGlobal = 0; @endphp
            @foreach($clientes as $index => $cliente)
                @php $totalGlobal += $cliente->total_gastado; @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $cliente->nombre }} {{ $cliente->apellido }}</strong><br>
                        <span style="color: #666; font-size: 10px;">Doc: {{ $cliente->numero_documento ?? 'S/D' }}</span>
                    </td>
                    <td>{{ $cliente->telefono ?? '-' }}</td>
                    <td class="text-center">{{ $cliente->procedencia ?? 'No registrado' }}</td>
                    <td class="text-center">{{ \Carbon\Carbon::parse($cliente->fecha_primera_atencion)->format('d/m/Y') }}</td>
                    <td class="text-end">S/ {{ number_format($cliente->total_gastado, 2) }}</td>
                </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="5" class="text-end">TOTAL DE INGRESOS POR CLIENTES NUEVOS:</td>
                <td class="text-end">S/ {{ number_format($totalGlobal, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
