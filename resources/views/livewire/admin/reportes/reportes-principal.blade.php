<div>
    {{-- ======================================================================== --}}
    {{-- IMPORTANTE: ESTE DIV ABRE EL COMPONENTE. NO BORRAR. --}}
    {{-- ======================================================================== --}}

    {{-- 1. BARRA DE FILTROS --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body py-3">
            <div class="row align-items-end g-3">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Desde</label>
                    <input type="date" class="form-control" wire:model.live="fechaInicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-muted text-uppercase">Hasta</label>
                    <input type="date" class="form-control" wire:model.live="fechaFin">
                </div>
                <div class="col-md-6 text-end">
                    <div class="text-muted small text-uppercase">Ingresos del Periodo</div>
                    <h2 class="text-success fw-bold mb-0">S/ {{ number_format($totalIngresos, 2) }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. NAVEGACIÓN POR PESTAÑAS --}}
    <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold px-4" id="tab-general" data-bs-toggle="pill" data-bs-target="#content-general" type="button">
                <i class="bi bi-cash-coin me-2"></i>Ventas
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4" id="tab-marketing" data-bs-toggle="pill" data-bs-target="#content-marketing" type="button">
                <i class="bi bi-people-fill me-2"></i>Clientes
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4" id="tab-finanzas" data-bs-toggle="pill" data-bs-target="#content-finanzas" type="button">
                <i class="bi bi-graph-up-arrow me-2"></i>Rentabilidad
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold px-4" id="tab-equipo" data-bs-toggle="pill" data-bs-target="#content-equipo" type="button">
                <i class="bi bi-person-badge me-2"></i>Equipo
            </button>
        </li>
    </ul>

    {{-- 3. CONTENIDO DE PESTAÑAS --}}
    <div class="tab-content" id="pills-tabContent">
        
        {{-- PESTAÑA 1: GENERAL --}}
        <div class="tab-pane fade show active" id="content-general" role="tabpanel" wire:ignore.self>
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <h6 class="text-muted text-uppercase mb-2">Ticket Promedio</h6>
                            <h1 class="display-5 fw-bold text-primary">S/ {{ number_format($ticketPromedio, 2) }}</h1>
                            <small class="text-muted">Promedio de venta por cliente</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-body text-center d-flex flex-column justify-content-center">
                            <h6 class="text-muted text-uppercase mb-2">Transacciones</h6>
                            <h1 class="display-5 fw-bold text-dark">{{ $cantidadTickets }}</h1>
                            <small class="text-muted">Total de ventas cerradas</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white"><h5 class="card-title mb-0">Evolución Diaria de Ventas</h5></div>
                <div class="card-body" wire:ignore>
                    <div style="height: 300px; position: relative;">
                        <canvas id="chartVentas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- PESTAÑA 2: CLIENTES --}}
        <div class="tab-pane fade" id="content-marketing" role="tabpanel" wire:ignore.self>
            <div class="row mb-4">
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white"><h5 class="card-title mb-0">Procedencia de Clientes</h5></div>
                        <div class="card-body d-flex justify-content-center align-items-center" wire:ignore>
                            <div style="height: 250px; width: 100%; position: relative;">
                                <canvas id="chartProcedencia"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white"><h5 class="card-title mb-0">Rango de Edades</h5></div>
                        <div class="card-body d-flex justify-content-center align-items-center" wire:ignore>
                            <div style="height: 250px; width: 100%; position: relative;">
                                <canvas id="chartEdades"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- TABLA: TOP 10 CLIENTES FRECUENTES (NUEVO) --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-star-fill text-warning me-2"></i>Top 10 Clientes Más Frecuentes
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">#</th>
                                            <th>Cliente</th>
                                            <th class="text-center">Edad</th>
                                            <th class="text-center">Visitas</th>
                                            <th class="text-end pe-4">Total</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($topClientesFrecuentes as $index => $cliente)
                                            <tr>
                                                <td class="ps-4 fw-bold text-muted">{{ $index + 1 }}</td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle bg-primary bg-opacity-10 d-flex justify-content-center align-items-center me-2" style="width: 36px; height: 36px;">
                                                            <i class="bi bi-person-fill text-primary"></i>
                                                        </div>
                                                        <span class="fw-medium">{{ $cliente->nombre }}</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                        {{ $cliente->edad ?? 'N/A' }} años
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-info bg-opacity-10 text-info px-3 py-2">
                                                        {{ $cliente->visitas }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4 fw-bold text-success">
                                                    S/ {{ number_format($cliente->total_gastado, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-5">
                                                    <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                                    No hay datos de clientes en este periodo
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-cake2-fill text-danger me-2"></i>Próximos Cumpleaños
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th class="ps-4">Cliente</th>
                                            <th class="text-center">Edad</th>
                                            <th class="text-end pe-4">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($proximosCumpleanos as $cliente)
                                            @php
                                                $esHoy = \Carbon\Carbon::parse($cliente->proximo_cumple)->isToday();
                                            @endphp
                                            <tr class="{{ $esHoy ? 'table-warning' : '' }}">
                                                <td class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="rounded-circle {{ $esHoy ? 'bg-warning' : 'bg-danger bg-opacity-10' }} d-flex justify-content-center align-items-center me-2" style="width: 36px; height: 36px;">
                                                            <i class="bi bi-{{ $esHoy ? 'cake2-fill text-white' : 'gift-fill text-danger' }}"></i>
                                                        </div>
                                                        <div>
                                                            <span class="fw-medium">{{ $cliente->nombre }}</span>
                                                            @if($esHoy)
                                                                <span class="badge bg-warning text-dark ms-2">🎉 ¡HOY!</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary">
                                                        {{ $cliente->edad_actual + 1 }} años
                                                    </span>
                                                </td>
                                                <td class="text-end pe-4">
                                                    <span class="badge {{ $esHoy ? 'bg-warning text-dark' : 'bg-danger bg-opacity-10 text-danger' }} px-3 py-2">
                                                        {{ \Carbon\Carbon::parse($cliente->proximo_cumple)->format('d/m/Y') }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted py-5">
                                                    <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-25"></i>
                                                    No hay cumpleaños próximos
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PESTAÑA 3: RENTABILIDAD --}}
        <div class="tab-pane fade" id="content-finanzas" role="tabpanel" wire:ignore.self>
            
            {{-- FILA 1: RESUMEN SERVICIOS --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-scissors text-info me-2"></i>Rentabilidad de Servicios</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <h6 class="text-muted text-uppercase small mb-2">Venta de Servicios</h6>
                                <h3 class="fw-bold text-success mb-0">S/ {{ number_format($totalServicios, 2) }}</h3>
                                <small class="text-muted">Ingresos brutos</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <h6 class="text-muted text-uppercase small mb-2">Costo de Insumos</h6>
                                <h3 class="fw-bold text-danger mb-0">S/ {{ number_format($costoInsumosPeriodo, 2) }}</h3>
                                <small class="text-muted">Consumidos en el periodo</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <h6 class="text-muted text-uppercase small mb-2">Ganancia Neta</h6>
                                <h3 class="fw-bold text-primary mb-0">S/ {{ number_format($gananciaNetaServicios, 2) }}</h3>
                                <small class="text-muted">Servicios - Insumos</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA 2: RESUMEN PRODUCTOS --}}
            <div class="card mb-4 border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="bi bi-bag-fill text-warning me-2"></i>Rentabilidad de Productos</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-success bg-opacity-10 rounded">
                                <h6 class="text-muted text-uppercase small mb-2">Venta de Productos</h6>
                                <h3 class="fw-bold text-success mb-0">S/ {{ number_format($totalVentaProductos, 2) }}</h3>
                                <small class="text-muted">Ingresos brutos</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-danger bg-opacity-10 rounded">
                                <h6 class="text-muted text-uppercase small mb-2">Costo de Productos</h6>
                                <h3 class="fw-bold text-danger mb-0">S/ {{ number_format($costoProductosVendidos, 2) }}</h3>
                                <small class="text-muted">Costo de lo vendido</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-center p-3 bg-primary bg-opacity-10 rounded">
                                <h6 class="text-muted text-uppercase small mb-2">Ganancia Neta</h6>
                                <h3 class="fw-bold text-primary mb-0">S/ {{ number_format($gananciaNetaProductos, 2) }}</h3>
                                <small class="text-muted">Venta - Costo</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA 3: RANKING SERVICIOS Y PRODUCTOS --}}
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trophy-fill text-info me-2"></i>Top 5 Servicios
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Servicio</th>
                                        <th class="text-center">Veces</th>
                                        <th class="text-end pe-4">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($rankingServicios as $servicio)
                                        <tr>
                                            <td class="ps-4">{{ Str::limit($servicio->nombre, 30) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info bg-opacity-10 text-info">{{ $servicio->veces_realizado }}</span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-success">
                                                S/ {{ number_format($servicio->total_generado, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Sin datos</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card shadow-sm border-0 h-100">
                        <div class="card-header bg-white">
                            <h5 class="card-title mb-0">
                                <i class="bi bi-trophy-fill text-warning me-2"></i>Top 5 Productos
                            </h5>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-4">Producto</th>
                                        <th class="text-center">Uds.</th>
                                        <th class="text-end pe-4">Ganancia</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($topProductosRentables as $prod)
                                        <tr>
                                            <td class="ps-4">{{ Str::limit($prod->nombre, 30) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-warning bg-opacity-10 text-warning">{{ $prod->cantidad_vendida }}</span>
                                            </td>
                                            <td class="text-end pe-4 fw-bold text-success">
                                                S/ {{ number_format($prod->ganancia_neta, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="3" class="text-center text-muted py-4">Sin datos</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA 4: MÉTODOS DE PAGO --}}
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-white"><h5 class="card-title mb-0">Métodos de Pago</h5></div>
                        <div class="card-body" wire:ignore>
                            <div style="height: 300px; position: relative;">
                                <canvas id="chartPagos"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PESTAÑA 4: EQUIPO --}}
        <div class="tab-pane fade" id="content-equipo" role="tabpanel" wire:ignore.self>
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white"><h5 class="card-title mb-0">Ranking de Ventas por Estilista</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle">
                            <thead>
                                <tr>
                                    <th>Estilista</th>
                                    <th>Total Generado</th>
                                    <th style="width: 50%;">Rendimiento</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $maxVenta = $rankingEstilistas->sum('total_vendido') ?: 1; @endphp
                                @foreach($rankingEstilistas as $estilista)
                                    @php $porcentaje = ($estilista->total_vendido / $maxVenta) * 100; @endphp
                                    <tr>
                                        <td class="fw-bold">{{ $estilista->nombre }}</td>
                                        <td>S/ {{ number_format($estilista->total_vendido, 2) }}</td>
                                        <td>
                                            <div class="progress" style="height: 8px;">
                                                <div class="progress-bar bg-success" role="progressbar" 
                                                     style="width: {{ $porcentaje }}%" 
                                                     aria-valuenow="{{ $porcentaje }}" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ======================================================================== --}}
    {{-- ZONA DE SCRIPTS (DENTRO DEL DIV PRINCIPAL) --}}
    {{-- ======================================================================== --}}

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    {{-- Script Normal (Define datos y funciones) --}}
    <script>
        // 1. Datos iniciales "blindados"
        const belenChartData = {
            ventasLabels: @json($ventasDiarias->pluck('fecha')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))),
            ventasValues: @json($ventasDiarias->pluck('total')),
            procedenciaLabels: @json($procedencia->pluck('procedencia')),
            procedenciaValues: @json($procedencia->pluck('total')),
            edadLabels: @json(array_keys($rangosEdad)),
            edadValues: @json(array_values($rangosEdad)),
            pagosLabels: @json($metodosPago->pluck('nombre')),
            pagosValues: @json($metodosPago->pluck('total'))
        };

        var myCharts = {};

        function renderMyChart(canvasId, type, labels, data, colors, labelName) {
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;
            if (myCharts[canvasId]) myCharts[canvasId].destroy();

            // Verificamos si hay datos
            const hasData = Array.isArray(data) && data.some(val => val > 0);
            
            myCharts[canvasId] = new Chart(canvas, {
                type: type,
                data: {
                    labels: labels,
                    datasets: [{
                        label: labelName,
                        data: data,
                        backgroundColor: colors,
                        borderColor: type === 'line' ? '#212124' : '#ffffff',
                        borderWidth: 1,
                        fill: type === 'line',
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: type !== 'line', position: 'bottom' } }
                }
            });
        }

        function updateAllCharts(payload) {
            renderMyChart('chartVentas', 'line', payload.ventasLabels, payload.ventasValues, 'rgba(33, 33, 36, 0.1)', 'Ventas');
            renderMyChart('chartProcedencia', 'bar', payload.procedenciaLabels, payload.procedenciaValues, '#E6DFD3', 'Clientes');
            renderMyChart('chartEdades', 'pie', payload.edadLabels, payload.edadValues, ['#E6DFD3', '#cfc5b1', '#8F8F8F', '#212124'], 'Edad');
            renderMyChart('chartPagos', 'doughnut', payload.pagosLabels, payload.pagosValues, ['#28a745', '#17a2b8', '#ffc107', '#dc3545'], 'Monto');
        }
    </script>

    {{-- Script de Livewire (Eventos) --}}
    @script
    <script>
        setTimeout(() => {
            updateAllCharts(belenChartData);
        }, 100);

        $wire.on('refresh-charts', (event) => {
            updateAllCharts(event.data);
        });

        document.querySelectorAll('button[data-bs-toggle="pill"]').forEach(tab => {
            tab.addEventListener('shown.bs.tab', function (event) {
                Object.values(myCharts).forEach(chart => {
                    if (typeof chart.resize === 'function') chart.resize();
                });
            });
        });

        // Abrir automáticamente la pestaña de Clientes si viene desde el ancla
        if (window.location.hash === '#content-marketing') {
            const tabButton = document.getElementById('tab-marketing');
            if (tabButton) {
                const tab = new bootstrap.Tab(tabButton);
                tab.show();
            }
        }
    </script>
    @endscript

</div> {{-- CIERRE DEL DIV PRINCIPAL (CRÍTICO PARA QUE NO DE ERROR 500) --}}