<div>
    {{-- TODO EL CONTENIDO DEBE ESTAR DENTRO DE ESTE DIV, SIN EXCEPCIÓN --}}

    {{-- 1. BARRA DE FILTROS --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-body py-3">
            <div class="row align-items-end">
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase">Fecha Inicio</label>
                    <input type="date" class="form-control" wire:model.live="fechaInicio">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-bold small text-uppercase">Fecha Fin</label>
                    <input type="date" class="form-control" wire:model.live="fechaFin">
                </div>
                <div class="col-md-6 text-end">
                    <span class="text-muted small">Mostrando reporte del:</span><br>
                    <strong class="text-primary fs-5">
                        {{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} 
                        al 
                        {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}
                    </strong>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. TARJETAS RESUMEN --}}
    <div class="row">
        <div class="col-lg-4 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>S/ {{ number_format($totalIngresos, 2) }}</h3>
                    <p>Ingresos Totales</p>
                </div>
                <div class="icon"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $cantidadTickets }}</h3>
                    <p>Ventas Realizadas</p>
                </div>
                <div class="icon"><i class="bi bi-receipt"></i></div>
            </div>
        </div>
        <div class="col-lg-4 col-12">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h3>S/ {{ number_format($ticketPromedio, 2) }}</h3>
                    <p>Ticket Promedio</p>
                </div>
                <div class="icon"><i class="bi bi-person-up"></i></div>
            </div>
        </div>
    </div>

    {{-- 3. GRÁFICOS Y TABLAS --}}
    <div class="row">
        <div class="col-md-8">
            <div class="card card-outline card-primary h-100">
                <div class="card-header">
                    <h3 class="card-title">Evolución de Ventas</h3>
                </div>
                {{-- IMPORTANTE: Agregamos wire:ignore aquí --}}
                {{-- Esto evita que Livewire borre el gráfico al recargar --}}
                <div class="card-body" wire:ignore>
                    <div style="position: relative; height: 300px; width: 100%;">
                        <canvas id="ventasChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-warning h-100">
                <div class="card-header">
                    <h3 class="card-title">Servicios Top</h3>
                </div>
                <div class="card-body p-0">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th class="text-center">Cant.</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topServicios as $serv)
                                <tr>
                                    <td>{{ Str::limit($serv->nombre_item, 20) }}</td>
                                    <td class="text-center fw-bold">{{ $serv->total_cantidad }}</td>
                                    <td class="text-end text-success small">S/ {{ number_format($serv->total_dinero, 0) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. SCRIPTS (DENTRO DEL DIV PADRE) --}}
    
    {{-- Cargamos la librería directamente aquí para evitar problemas con @assets --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    @script
    <script>
        let chartInstance = null;
        const ctx = document.getElementById('ventasChart');

        // Función para dibujar/redibujar
        function dibujarGrafico(labels, values) {
            if (chartInstance) {
                chartInstance.destroy(); // Destruimos el anterior para limpiar
            }

            chartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Ventas (S/)',
                        data: values,
                        borderColor: '#28a745',
                        backgroundColor: 'rgba(40, 167, 69, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: { beginAtZero: true },
                        x: { grid: { display: false } }
                    }
                }
            });
        }

        // 1. Carga Inicial (Toma los datos que vienen desde PHP al cargar)
        let labelsIniciales = @json($ventasPorDia->pluck('fecha')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m')));
        let valuesIniciales = @json($ventasPorDia->pluck('total'));
        
        dibujarGrafico(labelsIniciales, valuesIniciales);

        // 2. Escuchar el evento "refresh-chart" desde PHP
        $wire.on('refresh-chart', (event) => {
            // Livewire envía los datos dentro del objeto event
            // Nota: dependiendo de la versión puede ser event.labels o event[0].labels
            // Esta sintaxis suele funcionar en Livewire 3:
            dibujarGrafico(event.labels, event.values);
        });
    </script>
    @endscript

</div>