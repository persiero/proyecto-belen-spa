<div>
    @if($isOpen && $cliente)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.7); backdrop-filter: blur(3px); z-index: 1060;" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                
                {{-- HEADER --}}
                <div class="modal-header px-4 py-3" style="background: linear-gradient(135deg, var(--belen-dark) 0%, #2c3e50 100%); color: white;">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow" 
                             style="width: 50px; height: 50px; background-color: {{ $cliente->tipo_documento == 'RUC' ? '#212124' : ($cliente->genero == 'Masculino' ? '#4a6fa5' : 'var(--belen-grey)') }};">
                            @if($cliente->tipo_documento == 'RUC')
                                <i class="bi bi-building fs-5"></i>
                            @else
                                <span class="fs-5">{{ substr($cliente->nombre, 0, 1) }}{{ substr($cliente->apellido, 0, 1) }}</span>
                            @endif
                        </div>
                        <div>
                            <h5 class="modal-title mb-0 fw-bold">{{ $cliente->nombre }} {{ $cliente->apellido }}</h5>
                            <small class="opacity-75"><i class="bi bi-card-heading me-1"></i>{{ $cliente->numero_documento }}</small>
                        </div>
                    </div>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>

                {{-- ESTADÍSTICAS --}}
                <div class="bg-light border-bottom p-3">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f3e5f5 0%, #f5f5f5 100%);">
                                <div class="card-body text-center">
                                    <i class="bi bi-calendar-check text-purple fs-3 mb-2"></i>
                                    <h4 class="mb-0 fw-bold text-dark">{{ $total_visitas }}</h4>
                                    <small class="text-muted">Visitas</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #e8f5e9 0%, #f5f5f5 100%);">
                                <div class="card-body text-center">
                                    <i class="bi bi-cash-coin text-success fs-3 mb-2"></i>
                                    <h4 class="mb-0 fw-bold text-success">S/ {{ number_format($total_gastado, 2) }}</h4>
                                    <small class="text-muted">Total Gastado</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fff3e0 0%, #f5f5f5 100%);">
                                <div class="card-body text-center">
                                    <i class="bi bi-star-fill text-warning fs-3 mb-2"></i>
                                    <div class="fw-bold small text-truncate text-dark">{{ $servicio_favorito ?? 'N/A' }}</div>
                                    <small class="text-muted">Servicio Favorito</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f3e5f5 0%, #f5f5f5 100%);">
                                <div class="card-body text-center">
                                    <i class="bi bi-clock-history text-purple fs-3 mb-2"></i>
                                    <div class="fw-bold small text-dark">{{ $ultima_visita ? $ultima_visita->hora_inicio->format('d/m/Y') : 'N/A' }}</div>
                                    <small class="text-muted">Última Visita</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILTROS --}}
                <div class="px-4 py-3 bg-white border-bottom">
                    <div class="d-flex gap-2">
                        <button wire:click="$set('filtro_fecha', 'todo')" class="btn btn-sm {{ $filtro_fecha == 'todo' ? 'btn-dark' : 'btn-outline-secondary' }}">
                            Todo el Historial
                        </button>
                        <button wire:click="$set('filtro_fecha', 'mes')" class="btn btn-sm {{ $filtro_fecha == 'mes' ? 'btn-dark' : 'btn-outline-secondary' }}">
                            Este Mes
                        </button>
                        <button wire:click="$set('filtro_fecha', 'trimestre')" class="btn btn-sm {{ $filtro_fecha == 'trimestre' ? 'btn-dark' : 'btn-outline-secondary' }}">
                            Este Trimestre
                        </button>
                        <button wire:click="$set('filtro_fecha', 'año')" class="btn btn-sm {{ $filtro_fecha == 'año' ? 'btn-dark' : 'btn-outline-secondary' }}">
                            Este Año
                        </button>
                    </div>
                </div>

                {{-- HISTORIAL --}}
                <div class="modal-body p-4" style="max-height: 500px; overflow-y: auto;">
                    @forelse($turnos as $turno)
                        <div class="card mb-3 border shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <i class="bi bi-calendar3 text-purple me-2"></i>
                                    <strong>{{ $turno->hora_inicio->format('d/m/Y H:i') }}</strong>
                                    <small class="text-muted ms-2">({{ $turno->hora_inicio->diffForHumans() }})</small>
                                </div>
                                <span class="badge {{ $turno->estado == 'cerrado' ? 'bg-success' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($turno->estado) }}
                                </span>
                            </div>
                            <div class="card-body">
                                {{-- SERVICIOS --}}
                                @if($turno->servicios->count() > 0)
                                    <div class="mb-3">
                                        <h6 class="text-secondary mb-2"><i class="bi bi-scissors text-purple me-1"></i> Servicios Realizados</h6>
                                        <div class="row g-2">
                                            @foreach($turno->servicios as $ts)
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center bg-light p-2 rounded border">
                                                        <div class="flex-grow-1">
                                                            <div class="fw-bold small">{{ $ts->servicio->nombre }}</div>
                                                            <small class="text-muted">
                                                                <i class="bi bi-person-badge"></i> {{ $ts->estilista->nombre }}
                                                            </small>
                                                        </div>
                                                        <span class="badge bg-primary">S/ {{ number_format($ts->precio_aplicado, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- PRODUCTOS --}}
                                @if($turno->productos->count() > 0)
                                    <div class="mb-3">
                                        <h6 class="text-secondary mb-2"><i class="bi bi-bag text-success me-1"></i> Productos Comprados</h6>
                                        <div class="row g-2">
                                            @foreach($turno->productos as $tp)
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center bg-light p-2 rounded border">
                                                        <div class="flex-grow-1">
                                                            <div class="fw-bold small">{{ $tp->producto->nombre }}</div>
                                                            <small class="text-muted">Cant: {{ $tp->cantidad }} × S/ {{ number_format($tp->precio, 2) }}</small>
                                                        </div>
                                                        <span class="badge bg-success">S/ {{ number_format($tp->cantidad * $tp->precio, 2) }}</span>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                                {{-- TOTAL Y COMPROBANTE --}}
                                @php
                                    $venta = $cliente->ventas->where('id_turno', $turno->id)->first();
                                @endphp
                                @if($venta)
                                    <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                        <div>
                                            @if($venta->comprobante)
                                                <span class="badge bg-light text-purple border border-purple">
                                                    <i class="bi bi-file-earmark-text"></i>
                                                    {{ $venta->comprobante->tipoComprobante->nombre }} 
                                                    {{ $venta->comprobante->serie }}-{{ $venta->comprobante->correlativo }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-end">
                                            <small class="text-muted d-block">Total Pagado</small>
                                            <h5 class="mb-0 text-success fw-bold">S/ {{ number_format($venta->total, 2) }}</h5>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                            <p>No hay registros en el período seleccionado</p>
                        </div>
                    @endforelse
                </div>

                <div class="modal-footer bg-light">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
