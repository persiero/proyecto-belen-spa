<div>
    <div class="row justify-content-center">

        {{-- ALERTAS --}}
        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold mb-4">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        @if(!$cajaAbierta)
            {{-- PANTALLA DE APERTURA (Diseño Minimalista) --}}
            <div class="col-md-5">
                <div class="text-center mb-4">
                    @php $ultimoCierre = App\Models\Caja::where('id_usuario_apertura', Auth::id())->latest()->first(); @endphp
                    @if($ultimoCierre && $ultimoCierre->estado == 'cerrada')
                        <button onclick="window.open('{{ route('caja.reporte', $ultimoCierre->id) }}', '_blank', 'width=400,height=600')"
                                class="btn btn-white border shadow-sm rounded-pill px-4 text-muted hover-shadow">
                            <i class="bi bi-clock-history me-2"></i> Ver último cierre
                        </button>
                    @endif
                </div>

                <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
                    <div class="card-body p-5 text-center">
                        <div class="mb-4">
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                <i class="bi bi-shop display-4"></i>
                            </div>
                        </div>

                        <h3 class="fw-bold text-dark mb-1">Iniciar Jornada</h3>
                        <p class="text-muted mb-4">Ingresa el monto base en efectivo para abrir caja.</p>

                        <div class="form-floating mb-4">
                            <input type="number" step="0.01" wire:model="monto_inicial" class="form-control text-center fw-bold fs-2 border-primary" id="montoInicial" placeholder="0.00">
                            <label for="montoInicial" class="text-center w-100">Monto Inicial (S/)</label>
                        </div>

                        <button wire:click="abrirCaja"
                            wire:loading.attr="disabled"
                            wire:target="abrirCaja"
                            class="btn btn-primary w-100 btn-lg shadow fw-bold py-3 rounded-3 d-flex justify-content-center align-items-center">

                        {{-- Texto Normal --}}
                        <span wire:loading.remove wire:target="abrirCaja">
                            <i class="bi bi-box-arrow-in-right me-2"></i> ABRIR CAJA
                        </span>

                        {{-- Texto de Carga (Spinner) --}}
                        <span wire:loading wire:target="abrirCaja">
                            <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                            PROCESANDO...
                        </span>

                    </button>
                    </div>
                </div>
            </div>

        @else
            {{-- PANTALLA DE CONTROL (Dashboard) --}}
            <div class="col-md-9">

                {{-- HEADER ESTADO --}}
                <div class="d-flex justify-content-between align-items-center mb-4 bg-white p-3 rounded-3 shadow-sm border">
                    <div class="d-flex align-items-center">
                        <span class="position-relative d-inline-block me-3">
                            <i class="bi bi-shop fs-2 text-dark"></i>
                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-success border border-light rounded-circle"></span>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Caja Abierta</h5>
                            <small class="text-muted">Apertura: {{ $cajaAbierta->fecha_apertura->format('h:i A') }}</small>
                        </div>
                    </div>
                    <button class="btn btn-outline-danger fw-bold border-0 bg-light btn-cierre" data-bs-toggle="modal" data-bs-target="#modalCierre">
                        <i class="bi bi-lock-fill me-2"></i> Cerrar Caja
                    </button>
                </div>

                {{-- CARDS RESUMEN --}}
                <div class="row g-3 mb-4">
                    {{-- 1. Saldo Inicial --}}
                    <div class="col-md-4">
                        {{-- CORRECCIÓN: Quitamos 'border-4' y agregamos style para el grosor izquierdo --}}
                        <div class="card border-0 shadow-sm h-100 bg-white border-start border-secondary" style="border-left-width: 5px !important;">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-bold ls-1" style="font-size: 0.7rem;">Saldo Inicial</small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <h3 class="fw-bold text-dark mb-0">S/ {{ number_format($cajaAbierta->monto_apertura, 2) }}</h3>
                                    <i class="bi bi-safe text-secondary fs-3 opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. Ventas del Día --}}
                    <div class="col-md-4">
                        {{-- CORRECCIÓN: Quitamos 'border-4' y agregamos style para el grosor izquierdo --}}
                        <div class="card border-0 shadow-sm h-100 bg-white border-start border-primary" style="border-left-width: 5px !important;">
                            <div class="card-body">
                                <small class="text-uppercase text-muted fw-bold ls-1" style="font-size: 0.7rem;">Ingresos del Día</small>
                                <div class="d-flex justify-content-between align-items-center mt-2">
                                    <h3 class="fw-bold text-primary mb-0">+{{ number_format($totalVentas, 2) }}</h3>
                                    <i class="bi bi-graph-up-arrow text-primary fs-3 opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Efectivo en Caja (CORREGIDO) --}}
                    <div class="col-md-4">
                        <div class="card border-0 shadow h-100 bg-success text-white position-relative overflow-hidden">
                            {{-- Capa de Texto (z-index bajo) --}}
                            <div class="card-body position-relative" style="z-index: 1;">
                                <small class="text-white-50 text-uppercase fw-bold ls-1" style="font-size: 0.7rem;">Efectivo en Cajón</small>
                                <h3 class="fw-bold mb-0 mt-2">S/ {{ number_format($totalEfectivoEnCaja, 2) }}</h3>

                                @if($totalGastos > 0)
                                    <div class="mt-2 text-white-50 small">
                                        <i class="bi bi-arrow-down"></i> Salidas: S/ {{ number_format($totalGastos, 2) }}
                                    </div>
                                @endif
                            </div>

                            {{-- Botón Registrar Gasto (z-index ALTO: 10) --}}
                            {{-- Agregué 'd-flex' y 'justify-content-center' para centrar perfecto el signo menos --}}
                            <button type="button"
                                    class="btn btn-warning position-absolute top-50 end-0 translate-middle-y me-3 shadow rounded-circle p-0 d-flex align-items-center justify-content-center"
                                    style="width: 50px; height: 50px; z-index: 10; border: 2px solid rgba(255,255,255,0.2);"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalGasto"
                                    title="Registrar Salida de Dinero">
                                <i class="bi bi-dash-lg fw-bold fs-3"></i>
                            </button>
                        </div>
                    </div>

                {{-- DETALLE POR MÉTODO --}}
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-wallet2 me-2"></i> Desglose de Ingresos</h6>
                            </div>
                            <div class="card-body p-0">
                                <ul class="list-group list-group-flush">
                                    @foreach($resumenMetodos as $metodo => $monto)
                                        <li class="list-group-item d-flex justify-content-between align-items-center py-3">
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-light p-2 me-3 text-secondary">
                                                    @if($metodo == 'efectivo') <i class="bi bi-cash-stack text-success"></i>
                                                    @elseif($metodo == 'tarjeta') <i class="bi bi-credit-card text-primary"></i>
                                                    @elseif($metodo == 'yape' || $metodo == 'plin') <i class="bi bi-qr-code text-purple"></i>
                                                    @else <i class="bi bi-bank"></i>
                                                    @endif
                                                </div>
                                                <span class="text-capitalize fw-medium">{{ $metodo }}</span>
                                            </div>
                                            <span class="fw-bold">S/ {{ number_format($monto, 2) }}</span>
                                        </li>
                                    @endforeach
                                    <li class="list-group-item bg-light d-flex justify-content-between fw-bold">
                                        <span>TOTAL</span>
                                        <span>S/ {{ number_format($totalVentas, 2) }}</span>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card border-0 shadow-sm h-100">
                            <div class="card-header bg-white py-3">
                                <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-list-check me-2"></i> Movimientos de Caja</h6>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light text-secondary small">
                                        <tr>
                                            <th class="ps-3">Hora</th>
                                            <th>Detalle</th>
                                            <th class="text-end pe-3">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($movimientos as $mov)
                                            <tr>
                                                <td class="ps-3 small text-muted">{{ $mov->created_at->format('H:i') }}</td>
                                                <td>
                                                    <div class="text-dark fw-medium lh-sm">{{ $mov->descripcion }}</div>
                                                    <small class="text-muted" style="font-size: 0.7rem;">{{ $mov->usuario->nombre ?? 'Sistema' }}</small>
                                                </td>
                                                <td class="text-end pe-3">
                                                    <span class="badge {{ $mov->tipo == 'ingreso' ? 'text-success bg-success' : 'text-danger bg-danger' }} bg-opacity-10">
                                                        {{ $mov->tipo == 'ingreso' ? '+' : '-' }} S/ {{ number_format($mov->monto, 2) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="3" class="text-center py-4 text-muted small">Sin movimientos manuales.</td></tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        @endif
    </div>

    {{-- MODAL GASTO --}}
    <div wire:ignore.self class="modal fade" id="modalGasto" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning border-0">
                    <h5 class="modal-title fw-bold text-dark"><i class="bi bi-dash-circle me-2"></i> Retiro / Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <form wire:submit.prevent="registrarGasto">
                        <div class="form-floating mb-3">
                            <input type="number" step="0.10" wire:model="gasto_monto" class="form-control fw-bold text-center fs-3" id="montoGasto" placeholder="0.00" required>
                            <label for="montoGasto">Monto (S/)</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-secondary">Motivo</label>
                            <textarea wire:model="gasto_descripcion" class="form-control" rows="2" placeholder="Ej: Pago almuerzo..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 fw-bold">Registrar Salida</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CIERRE (ARQUEO) --}}
    <div wire:ignore.self class="modal fade" id="modalCierre" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-calculator me-2"></i> Arqueo de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">

                    <div class="row align-items-center mb-4">
                        <div class="col-6 text-end border-end pe-4">
                            <small class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem;">Efectivo Esperado</small>
                            <h3 class="fw-bold mb-0">S/ {{ number_format($totalEfectivoEnCaja, 2) }}</h3>
                        </div>
                        <div class="col-6 ps-4">
                            <label class="form-label small text-secondary fw-bold">EFECTIVO CONTADO</label>
                            <input type="number" step="0.10" wire:model.live="dinero_fisico" class="form-control form-control-lg fw-bold border-2" placeholder="0.00">
                        </div>
                    </div>

                    @if(is_numeric($dinero_fisico))
                        <div class="card border-0 mb-3 {{ $diferencia == 0 ? 'bg-success' : ($diferencia > 0 ? 'bg-warning' : 'bg-danger') }} text-white shadow-sm">
                            <div class="card-body text-center py-4">
                                <small class="text-uppercase fw-bold opacity-75">Diferencia</small>
                                <h1 class="display-4 fw-bold mb-0">
                                    @if($diferencia > 0)+@endif{{ number_format($diferencia, 2) }}
                                </h1>
                                <div class="mt-2 badge bg-black bg-opacity-25 px-3 py-2 rounded-pill">
                                    @if($diferencia == 0) <i class="bi bi-check-circle-fill me-1"></i> CUADRE PERFECTO
                                    @elseif($diferencia > 0) <i class="bi bi-exclamation-triangle-fill me-1"></i> SOBRANTE
                                    @else <i class="bi bi-x-circle-fill me-1"></i> FALTANTE
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-light border text-muted small mb-0">
                        <i class="bi bi-info-circle me-1"></i> Al confirmar, se cerrará la sesión de caja y se generará el reporte final.
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light text-muted" data-bs-dismiss="modal">Cancelar</button>
                    <button wire:click="cerrarCaja" class="btn btn-dark px-4 fw-bold shadow-sm">
                        <i class="bi bi-lock-fill me-2"></i> Confirmar Cierre
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

@script
<script>
    $wire.on('close-modal-cierre', () => {
        bootstrap.Modal.getInstance(document.getElementById('modalCierre')).hide();
    });
    $wire.on('close-modal', () => {
        bootstrap.Modal.getInstance(document.getElementById('modalGasto')).hide();
    });
</script>
@endscript
