<div>
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>S/ {{ number_format($totalVentasHoy, 2) }}</h3>
                    <p>Ventas de Hoy ({{ $cantidadVentas }} tickets)</p>
                </div>
                <div class="icon">
                    <i class="bi bi-cash-coin"></i>
                </div>
                <a href="{{ route('admin.caja') }}" class="small-box-footer">Ver Caja <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $turnosActivos }}</h3>
                    <p>Clientes en Atención</p>
                </div>
                <div class="icon">
                    <i class="bi bi-scissors"></i>
                </div>
                <a href="{{ route('admin.turnos') }}" class="small-box-footer">Ir a Recepción <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $clientesNuevos }}</h3>
                    <p>Clientes Nuevos (Mes)</p>
                </div>
                <div class="icon">
                    <i class="bi bi-person-plus-fill"></i>
                </div>
                <a href="{{ route('admin.clientes') }}" class="small-box-footer">Ver Directorio <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>

        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $productosBajoStock->count() }}</h3>
                    <p>Alertas de Stock</p>
                </div>
                <div class="icon">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                </div>
                <a href="{{ route('admin.productos') }}" class="small-box-footer">Ver Inventario <i class="bi bi-arrow-right"></i></a>
            </div>
        </div>
    </div>

    <div class="row">
        
        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Últimas Transacciones</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-valign-middle">
                            <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Cliente</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ultimasVentas as $venta)
                                <tr>
                                    <td>{{ $venta->created_at->format('H:i') }}</td>
                                    <td>
                                        @if($venta->cliente)
                                            {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}
                                        @else
                                            <span class="text-muted text-sm">Cliente Anónimo</span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-success">
                                        S/ {{ number_format($venta->total, 2) }}
                                    </td>
                                    <td>
                                        <span class="badge bg-success">Pagada</span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">No hay ventas registradas aún.</td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-danger">
                <div class="card-header">
                    <h3 class="card-title">⚠️ Reponer Stock</h3>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush">
                        @forelse($productosBajoStock as $prod)
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $prod->nombre }}</strong>
                                    <div class="small text-muted">Mín: {{ $prod->stock_minimo }}</div>
                                </div>
                                <span class="badge bg-danger rounded-pill">{{ $prod->stock_actual }} un.</span>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted">
                                <i class="bi bi-check-circle text-success"></i> Inventario saludable.
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer text-center">
                    <a href="{{ route('admin.productos') }}" class="text-uppercase small">Gestionar Inventario</a>
                </div>
            </div>
        </div>

    </div>
</div>