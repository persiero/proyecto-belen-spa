<div>
    {{-- 1. HEADER DE BIENVENIDA (NUEVO) --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold text-dark">Panel Principal</h4>
            <p class="text-muted small mb-0">Resumen general del negocio al {{ now()->format('d/m/Y') }}</p>
        </div>
        <div>
            <a href="{{ route('admin.pos') }}" class="btn btn-primary shadow-sm">
                <i class="bi bi-cash-coin me-1"></i>Nuevo Cobro/Venta
            </a>
        </div>
    </div>

    {{-- 2. TARJETAS DE ESTADÍSTICAS (REDISEÑADAS) --}}
    <div class="row g-3 mb-4">
        
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
                        <div class="icon-square bg-success bg-opacity-10 text-success rounded-3">
                            <i class="bi bi-cash-coin fs-4"></i>
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
                        <div class="icon-square bg-info bg-opacity-10 text-info rounded-3">
                            <i class="bi bi-scissors fs-4"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.turnos') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>

        {{-- Tarjeta: CLIENTES NUEVOS --}}
        <div class="col-lg-3 col-6">
            <div class="card border-0 shadow-sm h-100 overflow-hidden card-hover">
                <div class="card-body position-relative">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-uppercase text-muted small fw-bold mb-1">Nuevos (Mes)</p>
                            <h3 class="fw-bold text-dark mb-0">{{ $clientesNuevos }}</h3>
                            <span class="text-muted small mt-2 d-block">Registrados</span>
                        </div>
                        <div class="icon-square bg-warning bg-opacity-10 text-warning rounded-3">
                            <i class="bi bi-person-plus-fill fs-4"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.clientes') }}" class="stretched-link"></a>
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
                        <div class="icon-square bg-danger bg-opacity-10 text-danger rounded-3">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                    </div>
                    <a href="{{ route('admin.productos') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    {{-- 3. SECCIÓN INFERIOR --}}
    <div class="row">
        
        {{-- TABLA DE TRANSACCIONES --}}
        <div class="col-md-8 mb-4">
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
                                    <th>Total</th>
                                    <th>Estado</th>
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
                                        <td class="fw-bold text-dark">
                                            S/ {{ number_format($venta->total, 2) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded-pill">
                                                Pagada
                                            </span>
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
        <div class="col-md-4 mb-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title mb-0 fw-bold text-danger">
                        <i class="bi bi-lightning-charge-fill me-1"></i> Reponer Stock
                    </h5>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($productosBajoStock as $prod)
                            <li class="list-group-item px-4 py-3 d-flex justify-content-between align-items-center border-bottom-0">
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
</div>