<div>
    <div class="row justify-content-center">
        
        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        @if(!$cajaAbierta)
            {{-- BOTÓN PARA IMPRIMIR ÚLTIMO CIERRE --}}
            <div class="text-center mt-3">
                @php
                    $ultimoCierre = App\Models\Caja::where('id_usuario_apertura', Auth::id())->latest()->first();
                @endphp
                
                @if($ultimoCierre && $ultimoCierre->estado == 'cerrada')
                    <button onclick="window.open('{{ route('caja.reporte', $ultimoCierre->id) }}', '_blank', 'width=400,height=600')" 
                            class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-printer"></i> Imprimir Último Cierre
                    </button>
                @endif
            </div>
            {{-- PANTALLA DE APERTURA (Sin cambios) --}}
            <div class="col-md-6">
                <div class="card card-outline card-primary shadow-sm mt-4">
                    <div class="card-header text-center">
                        <h3 class="card-title w-100">🏁 Iniciar Jornada de Caja</h3>
                    </div>
                    <div class="card-body text-center p-5">
                        <i class="bi bi-shop display-1 text-muted mb-3"></i>
                        <p class="lead">La caja se encuentra cerrada.</p>
                        
                        <div class="form-group mb-4 text-start">
                            <label class="form-label fw-bold">Monto Inicial en Efectivo (S/)</label>
                            <input type="number" step="0.01" wire:model="monto_inicial" class="form-control form-control-lg" placeholder="0.00">
                            <small class="text-muted">Ingresa el dinero base en el cajón (sencillo).</small>
                        </div>

                        <button wire:click="abrirCaja" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-unlock-fill"></i> ABRIR CAJA
                        </button>
                    </div>
                </div>
            </div>

        @else
            {{-- PANTALLA DE CONTROL (Con cambios) --}}
            <div class="col-md-8">
                <div class="card card-outline card-success shadow-sm">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="bi bi-circle-fill text-success blink_me"></i> Caja Abierta
                        </h3>
                        <div class="card-tools">
                            <span class="badge bg-light text-dark border">
                                Apertura: {{ $cajaAbierta->fecha_apertura->format('H:i A') }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        
                        {{-- BLOQUE DE RESUMEN --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light text-center">
                                    <small class="text-muted text-uppercase">Saldo Inicial</small>
                                    <h4 class="fw-bold mb-0">S/ {{ number_format($cajaAbierta->monto_apertura, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-light text-center">
                                    <small class="text-muted text-uppercase">Ventas del Día</small>
                                    <h4 class="fw-bold text-primary mb-0">+ S/ {{ number_format($totalVentas, 2) }}</h4>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded bg-success text-white text-center shadow-sm position-relative">
                                    <small class="text-white-50 text-uppercase">Efectivo en Cajón</small>
                                    <h4 class="fw-bold mb-0">S/ {{ number_format($totalEfectivoEnCaja, 2) }}</h4>
                                    
                                    {{-- NUEVO BOTÓN: REGISTRAR GASTO --}}
                                    <button type="button" class="btn btn-warning btn-sm position-absolute top-0 end-0 mt-2 me-2 rounded-circle shadow" 
                                            title="Registrar Salida de Dinero"
                                            data-bs-toggle="modal" data-bs-target="#modalGasto">
                                        <i class="bi bi-dash-lg fw-bold"></i>
                                    </button>
                                </div>
                                @if($totalGastos > 0)
                                    <div class="text-end mt-1">
                                        <small class="text-danger fw-bold"><i class="bi bi-arrow-down"></i> Salidas: S/ {{ number_format($totalGastos, 2) }}</small>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <h5 class="border-bottom pb-2 mb-3">💰 Arqueo por Método de Pago</h5>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Método</th>
                                        <th class="text-end">Total Recaudado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($resumenMetodos as $metodo => $monto)
                                        <tr>
                                            <td class="text-capitalize">
                                                @if($metodo == 'efectivo') <i class="bi bi-cash-stack text-success"></i>
                                                @elseif($metodo == 'tarjeta') <i class="bi bi-credit-card text-primary"></i>
                                                @elseif($metodo == 'yape' || $metodo == 'plin') <i class="bi bi-phone text-purple"></i>
                                                @elseif($metodo == 'transferencia') <i class="bi bi-bank text-info"></i>
                                                @endif
                                                {{ $metodo }}
                                            </td>
                                            <td class="text-end fw-bold">S/ {{ number_format($monto, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-dark">
                                        <td>TOTAL VENTAS</td>
                                        <td class="text-end">S/ {{ number_format($totalVentas, 2) }}</td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <hr>
                        <div class="alert alert-warning">
                            <i class="bi bi-exclamation-circle"></i> <strong>Atención:</strong> Al cerrar caja, se asume que el dinero físico coincide con el sistema.
                        </div>

                        <button type="button" class="btn btn-danger w-100 btn-lg" data-bs-toggle="modal" data-bs-target="#modalCierre">
                            <i class="bi bi-lock-fill"></i> REALIZAR ARQUEO Y CERRAR
                        </button>

                        <hr class="my-4">

                        <h5 class="text-secondary fw-bold"><i class="bi bi-list-check"></i> Historial de Movimientos (Turno Actual)</h5>

                        <div class="table-responsive bg-white border rounded">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light text-muted small">
                                    <tr>
                                        <th>Hora</th>
                                        <th>Tipo</th>
                                        <th>Descripción</th>
                                        <th>Usuario</th>
                                        <th class="text-end">Monto</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($movimientos as $mov)
                                        <tr>
                                            <td class="small">{{ $mov->created_at->format('H:i') }}</td>
                                            <td>
                                                @if($mov->tipo == 'ingreso')
                                                    <span class="badge bg-success bg-opacity-10 text-success">Ingreso</span>
                                                @else
                                                    <span class="badge bg-danger bg-opacity-10 text-danger">Salida</span>
                                                @endif
                                            </td>
                                            <td>{{ $mov->descripcion }}</td>
                                            <td class="small text-muted">{{ $mov->usuario->nombre ?? 'Sistema' }}</td>
                                            <td class="text-end fw-bold {{ $mov->tipo == 'ingreso' ? 'text-success' : 'text-danger' }}">
                                                {{ $mov->tipo == 'ingreso' ? '+' : '-' }} S/ {{ number_format($mov->monto, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-3 text-muted">
                                                <small>No hay movimientos manuales registrados.</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MODAL PARA REGISTRAR GASTO (NUEVO) --}}
            <div wire:ignore.self class="modal fade" id="modalGasto" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content">
                        <div class="modal-header bg-warning">
                            <h5 class="modal-title fw-bold"><i class="bi bi-cash-coin"></i> Registrar Salida de Dinero</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form wire:submit.prevent="registrarGasto">
                                <div class="mb-3">
                                    <label class="form-label">Monto a retirar en EFECTIVO (S/)</label>
                                    <div class="input-group">
                                        <span class="input-group-text">S/</span>
                                        <input type="number" step="0.10" wire:model="gasto_monto" class="form-control form-control-lg" placeholder="0.00" required>
                                    </div>
                                    @error('gasto_monto') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Motivo / Descripción</label>
                                    <textarea wire:model="gasto_descripcion" class="form-control" rows="3" placeholder="Ej: Compra de alcohol, Pago de taxi..." required></textarea>
                                    @error('gasto_descripcion') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>
                                
                                <div class="d-grid gap-2">
                                    <button type="submit" class="btn btn-warning btn-lg">Registrar Salida</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        @endif
    </div>

    <div wire:ignore.self class="modal fade" id="modalCierre" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-calculator"></i> Arqueo de Caja</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    
                    <div class="text-center mb-4">
                        <h6 class="text-muted text-uppercase">Dinero Esperado en Sistema</h6>
                        <h2 class="fw-bold">S/ {{ number_format($totalEfectivoEnCaja, 2) }}</h2>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">¿Cuánto dinero contaste físicamente?</label>
                        <div class="input-group">
                            <span class="input-group-text">S/</span>
                            <input type="number" step="0.10" wire:model.live="dinero_fisico" class="form-control form-control-lg fw-bold text-center" placeholder="0.00">
                        </div>
                    </div>

                    @if(is_numeric($dinero_fisico))
                        <div class="text-center p-3 rounded mb-3 {{ $diferencia < 0 ? 'bg-danger bg-opacity-10 text-danger' : ($diferencia > 0 ? 'bg-success bg-opacity-10 text-success' : 'bg-success text-white') }}">
                            <h1 class="display-4 fw-bold mb-0">
                                @if($diferencia > 0)+@endif{{ number_format($diferencia, 2) }}
                            </h1>
                            <small class="text-uppercase fw-bold ls-1">
                                @if($diferencia < 0) ❌ FALTANTE (PÉRDIDA)
                                @elseif($diferencia > 0) ⚠️ SOBRANTE (REVISAR)
                                @else ✅ CUADRE EXACTO
                                @endif
                            </small>
                        </div>
                    @endif

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button wire:click="cerrarCaja" class="btn btn-danger">
                        Confirmar Cierre
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>


{{-- SCRIPT PARA CERRAR EL MODAL --}}
@script
<script>
    $wire.on('close-modal-cierre', () => {
        var myModalEl = document.getElementById('modalCierre');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        modal.hide();
    });

    $wire.on('close-modal', () => {
        var myModalEl = document.getElementById('modalGasto');
        var modal = bootstrap.Modal.getInstance(myModalEl);
        modal.hide();
    });

</script>
@endscript
