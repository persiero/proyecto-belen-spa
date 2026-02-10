<div>
    {{-- 1. HEADER DE BIENVENIDA --}}
    <div class="card shadow-sm border-0 rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">
                        <i class="bi bi-speedometer2 me-2 text-primary"></i>Panel Principal
                    </h5>
                    <p class="text-muted mb-0 small">Resumen general del negocio al {{ now()->format('d/m/Y') }}</p>
                </div>
                <div>
                    <a href="{{ route('admin.pos') }}" class="btn btn-primary shadow-sm text-dark fw-bold">
                        <i class="bi bi-cash-coin me-2"></i>Nuevo Cobro/Venta
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. TARJETAS DE ESTADÍSTICAS --}}
    <div class="row g-4 mb-4">
        
        {{-- Tarjeta: VENTAS HOY --}}
        <div class="col-lg-3 col-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden card-hover">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Ventas Hoy</p>
                            <h3 class="fw-bold text-dark mb-0">S/ {{ number_format($totalVentasHoy, 2) }}</h3>
                            <span class="badge bg-success bg-opacity-10 text-success mt-2">
                                {{ $cantidadVentas }} tickets
                            </span>
                        </div>
                        <div class="icon-square bg-success bg-opacity-10 text-success rounded-3 p-3">
                            <i class="bi bi-cash-coin fs-3"></i>
                        </div>
                    </div>
                    {{-- Enlace invisible que cubre toda la tarjeta --}}
                    <a href="{{ route('admin.ventas.historial') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: EN ATENCIÓN --}}
        <div class="col-lg-3 col-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden card-hover">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">En Atención</p>
                            <h3 class="fw-bold text-dark mb-0">{{ $turnosActivos }}</h3>
                            <span class="text-muted small mt-2 d-block">Clientes en sala</span>
                        </div>
                        <div>
                            <i class="bi bi-scissors fs-3 text-purple"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.turnos') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: CLIENTES ATENDIDOS HOY --}}
        <div class="col-lg-3 col-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden card-hover">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Clientes Hoy</p>
                            <h3 class="fw-bold text-dark mb-0">{{ $clientesAtendidosHoy }}</h3>
                            <span class="text-muted small mt-2 d-block">Atendidos</span>
                        </div>
                        <div class="icon-square bg-warning bg-opacity-10 text-warning rounded-3 p-3">
                            <i class="bi bi-people-fill fs-3"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.turnos') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: ALERTAS STOCK --}}
        <div class="col-lg-3 col-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden card-hover">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Stock Bajo</p>
                            <h3 class="fw-bold text-dark mb-0">{{ $productosBajoStock->count() }}</h3>
                            <span class="badge bg-danger bg-opacity-10 text-danger mt-2">
                                Acción requerida
                            </span>
                        </div>
                        <div class="icon-square bg-danger bg-opacity-10 text-danger rounded-3 p-3">
                            <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.productos') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. CUMPLEAÑOS Y SALIDAS DE INSUMOS --}}
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            @if($cumpleanosHoy->count() > 0)
                <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="card-body py-4">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center flex-grow-1">
                                <div class="me-3">
                                    <i class="bi bi-cake2-fill text-white" style="font-size: 2.5rem;"></i>
                                </div>
                                <div class="text-white">
                                    <h5 class="fw-bold mb-1">🎉 ¡Cumpleaños de Hoy!</h5>
                                    <p class="mb-0 opacity-75 small">
                                        @if($cumpleanosHoy->count() == 1)
                                            <strong>{{ $cumpleanosHoy->first()->nombre }}</strong> cumple <strong>{{ $cumpleanosHoy->first()->edad }} años</strong>
                                        @else
                                            <strong>{{ $cumpleanosHoy->count() }} clientes</strong> cumplen años hoy
                                        @endif
                                    </p>
                                </div>
                            </div>
                            <a href="{{ route('admin.reportes.analitica') }}#content-marketing" class="btn btn-light btn-sm fw-bold">
                                <i class="bi bi-calendar-heart me-1"></i>Ver Próximos
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm h-100 bg-light">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-center align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-calendar-heart text-muted me-2"></i>
                                <span class="text-muted small">No hay cumpleaños hoy</span>
                            </div>
                            <a href="{{ route('admin.reportes.analitica') }}#content-marketing" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-calendar-heart me-1"></i>Ver Próximos
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold text-purple">
                            <i class="bi bi-box-seam me-1"></i> Salidas de Insumos (7d)
                        </h6>
                        <a href="{{ route('admin.inventario') }}?tab=ajuste" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-plus-lg me-1"></i>Registrar Salida
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($salidasInsumos->count() > 0)
                        <ul class="list-group list-group-flush" style="max-height: 200px; overflow-y: auto;">
                            @foreach($salidasInsumos as $salida)
                                <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                                    <div class="flex-grow-1">
                                        <span class="fw-medium text-dark small">{{ Str::limit($salida->nombre, 25) }}</span>
                                        <small class="text-muted d-block" style="font-size: 0.75rem;">
                                            {{ \Carbon\Carbon::parse($salida->fecha)->format('d/m/Y') }}
                                        </small>
                                    </div>
                                    <span class="badge bg-danger bg-opacity-10 text-danger">
                                        -{{ abs($salida->cantidad) }}
                                    </span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-4">
                            <i class="bi bi-inbox text-muted opacity-25 fs-1 d-block mb-2"></i>
                            <p class="text-muted small mb-0">No hay salidas registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- 4. TRANSACCIONES Y STOCK --}}
    <div class="row g-4 mb-4">
        
        {{-- TABLA DE TRANSACCIONES --}}
        <div class="col-lg-8 col-md-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 fw-bold text-dark">Últimas Transacciones</h5>
                    <a href="{{ route('admin.ventas.historial') }}" class="btn btn-sm btn-outline-light text-muted border">
                        Ver todo
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle table-hover mb-0">
                            <thead class="bg-light">
                                <tr class="text-muted small text-uppercase">
                                    <th class="ps-4">Hora</th>
                                    <th>Cliente</th>
                                    <th>Tipo</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody class="border-top-0">
                                @forelse($ultimasVentas as $venta)
                                    <tr>
                                        <td class="ps-4 fw-medium text-muted">{{ $venta->created_at->format('H:i') }}</td>
                                        <td>
                                            @if($venta->cliente)
                                                <div class="d-flex align-items-center">
                                                    <div class="rounded-circle bg-light d-flex justify-content-center align-items-center me-2 text-secondary fw-bold" style="width: 32px; height: 32px; font-size: 12px;">
                                                        {{ substr($venta->cliente->nombre, 0, 1) }}
                                                    </div>
                                                    <div>
                                                        <span class="d-block text-dark fw-medium">{{ $venta->cliente->nombre }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted fst-italic">Público General</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $tieneServicios = $venta->detalles->where('tipo_item', 'servicio')->count() > 0;
                                                $tieneProductos = $venta->detalles->where('tipo_item', 'producto')->count() > 0;
                                            @endphp
                                            @if($tieneServicios && $tieneProductos)
                                                <span class="badge bg-purple bg-opacity-10 text-purple">Mixto</span>
                                            @elseif($tieneServicios)
                                                <span class="badge bg-primary bg-opacity-10 text-primary">Servicio</span>
                                            @else
                                                <span class="badge bg-warning bg-opacity-10 text-warning">Producto</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold text-dark">
                                            S/ {{ number_format($venta->total, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25"></i>
                                            No hay cobros hoy
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- LISTA DE STOCK BAJO --}}
        <div class="col-lg-4 col-md-12">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold text-danger">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Reponer Stock
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="max-height: 350px; overflow-y: auto;">
                        @forelse($productosBajoStock as $prod)
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="mb-0 text-dark fw-medium">{{ $prod->nombre }}</h6>
                                    <small class="text-danger fw-bold">Quedan: {{ $prod->stock_actual }}</small>
                                    <span class="text-muted small mx-1">|</span>
                                    <small class="text-muted">Mín: {{ $prod->stock_minimo }}</small>
                                </div>
                                <a href="{{ route('admin.compras') }}" class="btn btn-sm btn-light text-muted">
                                    <i class="bi bi-bag-plus"></i>
                                </a>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-5 border-0">
                                <div class="text-success mb-2">
                                    <i class="bi bi-check-circle-fill fs-1 opacity-25"></i>
                                </div>
                                <h6 class="fw-bold text-success">Inventario Saludable</h6>
                                <p class="small text-muted mb-0">No hay alertas de stock por ahora.</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
                @if($productosBajoStock->count() > 0)
                    <div class="card-footer bg-white border-0 text-center pb-3">
                        <a href="{{ route('admin.inventario') }}" class="btn btn-outline-danger btn-sm w-100">
                            Ver Inventario
                        </a>
                    </div>
                @endif
            </div>
        </div>

    </div>

    {{-- 5. RENDIMIENTO Y TENDENCIAS --}}
    <div class="row g-4">
        
        {{-- CAJA HOY --}}
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-wallet2 me-1"></i> Caja Hoy
                    </h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <p class="text-muted small mb-1">💵 Ingresos</p>
                            <h5 class="fw-bold text-success mb-0">S/ {{ number_format($ingresosCajaHoy, 2) }}</h5>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                        <div>
                            <p class="text-muted small mb-1">💸 Egresos</p>
                            <h5 class="fw-bold text-danger mb-0">S/ {{ number_format($egresosCajaHoy, 2) }}</h5>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted small mb-1">📊 Saldo</p>
                            <h4 class="fw-bold text-dark mb-0">S/ {{ number_format($saldoCajaHoy, 2) }}</h4>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white border-0 text-center pb-3">
                    <a href="{{ route('admin.caja') }}" class="btn btn-outline-primary btn-sm w-100">
                        Ver Caja Completa
                    </a>
                </div>
            </div>
        </div>

        {{-- TOP ESTILISTAS HOY --}}
        <div class="col-lg-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="bi bi-person-hearts me-1"></i> Estilistas Hoy
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="max-height: 280px; overflow-y: auto;">
                        @forelse($topEstilistasHoy as $estilista)
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center border-0">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-purple bg-opacity-10 d-flex justify-content-center align-items-center me-2" style="width: 36px; height: 36px;">
                                        <i class="bi bi-person-fill text-purple"></i>
                                    </div>
                                    <span class="fw-medium text-dark">{{ $estilista->nombre }}</span>
                                </div>
                                <span class="badge bg-purple bg-opacity-10 text-purple px-3 py-2">
                                    {{ $estilista->total_servicios }} servicios
                                </span>
                            </li>
                        @empty
                            <li class="list-group-item text-center py-5">
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25 text-muted"></i>
                                <p class="text-muted small mb-0">No hay servicios hoy</p>
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>
        </div>

        {{-- TOP SERVICIO Y PRODUCTO (7 DÍAS) --}}
        <div class="col-lg-4 col-md-12">
            <div class="row g-4">
                
                {{-- TOP SERVICIO --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="card-title mb-0 fw-bold text-dark">
                                <i class="bi bi-trophy-fill text-warning me-1"></i> Top Servicio (7d)
                            </h6>
                        </div>
                        <div class="card-body text-center py-4">
                            @if($topServicio)
                                <div class="mb-2">
                                    <i class="bi bi-scissors fs-1 text-primary opacity-75"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $topServicio->nombre }}</h5>
                                <p class="text-muted small mb-2">{{ $topServicio->total_veces }} veces realizado</p>
                                <h6 class="text-success fw-bold">S/ {{ number_format($topServicio->total_ingresos, 2) }}</h6>
                            @else
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25 text-muted"></i>
                                <p class="text-muted small mb-0">Sin datos</p>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- TOP PRODUCTO --}}
                <div class="col-12">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3">
                            <h6 class="card-title mb-0 fw-bold text-dark">
                                <i class="bi bi-trophy-fill text-warning me-1"></i> Top Producto (7d)
                            </h6>
                        </div>
                        <div class="card-body text-center py-4">
                            @if($topProducto)
                                <div class="mb-2">
                                    <i class="bi bi-bag-fill fs-1 text-warning opacity-75"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">{{ $topProducto->nombre }}</h5>
                                <p class="text-muted small mb-2">{{ $topProducto->total_ventas }} unidades vendidas</p>
                                <h6 class="text-success fw-bold">S/ {{ number_format($topProducto->total_ingresos, 2) }}</h6>
                            @else
                                <i class="bi bi-inbox fs-1 d-block mb-2 opacity-25 text-muted"></i>
                                <p class="text-muted small mb-0">Sin datos</p>
                            @endif
                        </div>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>