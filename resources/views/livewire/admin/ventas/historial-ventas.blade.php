<div>
    {{-- FILTROS DE BÚSQUEDA --}}
    <div class="card shadow-sm border-0 mb-4 bg-light">
        <div class="card-body py-3">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Desde</label>
                    <input type="date" wire:model.live="fecha_inicio" class="form-control shadow-sm border-0">
                </div>
                <div class="col-md-3">
                    <label class="form-label small text-uppercase text-muted fw-bold">Hasta</label>
                    <input type="date" wire:model.live="fecha_fin" class="form-control shadow-sm border-0">
                </div>
                <div class="col-md-6">
                    <label class="form-label small text-uppercase text-muted fw-bold">Buscar</label>
                    <div class="input-group shadow-sm">
                        <span class="input-group-text bg-white border-0"><i class="bi bi-search text-secondary"></i></span>
                        <input type="text" wire:model.live="search" class="form-control border-0" placeholder="N° Ticket, Cliente o Documento...">
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLA DE RESULTADOS --}}
    <div class="card shadow-sm border-0 rounded-3 mb-5">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="mb-0 fw-bold text-dark"><i class="bi bi-receipt me-2"></i> Historial de Tickets y Facturas</h5>
        </div>
        
        <div class="table-responsive" style="overflow: visible;">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-secondary text-uppercase small">
                    <tr>
                        <th class="ps-4 py-3">Ticket #</th>
                        <th class="py-3">Fecha/Hora</th>
                        <th class="py-3">Cliente</th>
                        <th class="py-3">Método Pago</th>
                        <th class="text-end py-3">Total</th>
                        <th class="text-center py-3">Estado</th>
                        <th class="text-center py-3 pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                        {{-- Fila con estilo tenue si está anulada --}}
                        <tr class="{{ $venta->estado == 'anulado' ? 'bg-light text-muted' : '' }}" style="position: static;">
                            
                            <td class="ps-4 font-monospace fw-bold text-secondary">
                                #{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}
                            </td>
                            
                            <td>
                                <div class="fw-bold">{{ $venta->fecha->format('d/m/Y') }}</div>
                                <small class="text-muted">{{ $venta->fecha->format('H:i A') }}</small>
                            </td>
                            
                            <td>
                                @if($venta->cliente)
                                    <div class="fw-bold">{{ Str::limit($venta->cliente->nombre . ' ' . $venta->cliente->apellido, 25) }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        {{ $venta->cliente->numero_documento ?? 'Sin Doc.' }}
                                    </small>
                                @else
                                    <span class="fst-italic text-secondary small">Público General</span>
                                @endif
                            </td>
                            
                            <td>
                                @foreach($venta->pagos as $pago)
                                    @php
                                        switch($pago->metodoPago->nombre) {
                                            case 'efectivo': $icon = 'bi-cash-stack'; break;
                                            case 'yape': $icon = 'bi-qr-code'; break;
                                            case 'plin': $icon = 'bi-qr-code'; break;
                                            case 'tarjeta': $icon = 'bi-credit-card'; break;
                                            default: $icon = 'bi-bank';
                                        }
                                    @endphp
                                    <span class="badge bg-light text-dark border fw-normal">
                                        <i class="bi {{ $icon }} me-1"></i> {{ ucfirst($pago->metodoPago->nombre) }}
                                    </span>
                                @endforeach
                            </td>
                            
                            <td class="text-end">
                                @if($venta->estado == 'anulado')
                                    <span class="text-decoration-line-through text-danger opacity-50">S/ {{ number_format($venta->total, 2) }}</span>
                                @else
                                    <span class="fw-bold text-dark fs-6">S/ {{ number_format($venta->total, 2) }}</span>
                                @endif
                            </td>
                            
                            <td class="text-center">
                                @if($venta->estado == 'anulada')
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2">ANULADO</span>
                                @else
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2">PAGADO</span>
                                @endif
                            </td>

                            {{-- ACCIONES DINÁMICAS --}}
                            <td class="text-center pe-4">
                                
                                {{-- CASO 1: VENTA ANULADA (CON DOCS FISCALES) --}}
                                @if($venta->estado == 'anulada')
                                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                                        
                                        {{-- BOTÓN DINÁMICO CORREGIDO: FAC o BOL (Tachado/Referencia) --}}
                                        @if($venta->comprobante)
                                            <a href="{{ route('comprobante.ticket', $venta->comprobante->id) }}" 
                                            target="_blank" 
                                            class="btn btn-outline-secondary" 
                                            title="Ver Comprobante Original">
                                                <i class="bi bi-file-earmark-text"></i> 
                                                {{-- LÓGICA INFALIBLE: Si la serie empieza con 'F', es Fac. --}}
                                                {{ Str::startsWith($venta->comprobante->serie, 'F') ? 'Fac.' : 'Bol.' }}
                                            </a>
                                        @endif

                                        {{-- BOTÓN NOTA DE CRÉDITO --}}
                                        @if($venta->notaCredito)
                                            <a href="{{ route('comprobante.ticket', $venta->notaCredito->id) }}" target="_blank" class="btn btn-outline-danger fw-bold" title="Ver Nota de Crédito">
                                                <i class="bi bi-file-earmark-x-fill"></i> NC
                                            </a>
                                        @endif
                                    </div>

                                {{-- CASO 2: VENTA VÁLIDA CON CPE (Factura/Boleta Activa) --}}
                                @elseif($venta->comprobante)
                                    <div class="btn-group btn-group-sm shadow-sm" role="group">
                                        
                                        <button type="button" class="btn btn-success fw-bold px-3">
                                            {{ Str::startsWith($venta->comprobante->serie, 'F') ? 'Fac.' : 'Bol.' }}
                                            {{ substr($venta->comprobante->serie, -3) }}-{{ $venta->comprobante->correlativo }}
                                        </button>
                                        
                                        {{-- MENÚ DESPLEGABLE --}}
                                        <button type="button" class="btn btn-success dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" data-bs-auto-close="true"></button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow">
                                            <li><a class="dropdown-item" href="{{ route('comprobante.ticket', $venta->comprobante->id) }}" target="_blank"><i class="bi bi-file-pdf text-danger me-2"></i> Ver PDF</a></li>
                                            <li><a class="dropdown-item" href="{{ route('comprobante.xml', $venta->comprobante->id) }}"><i class="bi bi-code-slash text-primary me-2"></i> XML</a></li>
                                            <li><a class="dropdown-item" href="{{ route('comprobante.cdr', $venta->comprobante->id) }}"><i class="bi bi-file-zip text-success me-2"></i> CDR</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a href="#" class="dropdown-item text-danger" 
                                                   wire:click.prevent="anularComprobante({{ $venta->id }})"
                                                   wire:confirm="⚠️ ¿Emitir Nota de Crédito para anular esta venta ante SUNAT?">
                                                    <i class="bi bi-x-circle me-2"></i> Anular (Nota Crédito)
                                                </a>
                                            </li>
                                        </ul>
                                    </div>

                                {{-- CASO 3: VENTA INTERNA (Ticket Simple) --}}
                                @else
                                    <div class="btn-group btn-group-sm shadow-sm">
                                        <button onclick="window.open('{{ route('print.ticket', $venta->id) }}', '_blank', 'width=400,height=600')" 
                                                class="btn btn-light border text-dark" title="Imprimir Ticket">
                                            <i class="bi bi-printer-fill"></i>
                                        </button>
                                        
                                        <button wire:click="emitirComprobante({{ $venta->id }})" 
                                                wire:confirm="¿Generar comprobante electrónico ahora?"
                                                class="btn btn-primary fw-bold" title="Emitir Factura/Boleta">
                                            <i class="bi bi-cloud-arrow-up-fill me-1"></i> CPE
                                        </button>

                                        <button wire:click="anularVenta({{ $venta->id }})" 
                                                wire:confirm="¿Anular esta venta interna? Se devolverá el stock."
                                                class="btn btn-outline-danger" title="Anular Internamente">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-1 d-block mb-2 opacity-50"></i>
                                No se encontraron ventas en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="card-footer bg-white py-3 border-top-0">
            {{ $ventas->links() }}
        </div>
    </div>
</div>