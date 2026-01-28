<div>
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">Desde</label>
            <input type="date" wire:model.live="fecha_inicio" class="form-control">
        </div>
        <div class="col-md-3">
            <label class="form-label">Hasta</label>
            <input type="date" wire:model.live="fecha_fin" class="form-control">
        </div>
        <div class="col-md-6">
            <label class="form-label">Buscar (Nro Ticket o Cliente)</label>
            <input type="text" wire:model.live="search" class="form-control" placeholder="Buscar...">
        </div>
    </div>

    @if (session()->has('message'))
        <div class="alert alert-success">{{ session('message') }}</div>
    @endif

    <div class="card card-outline card-primary">
        <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
                <thead>
                    <tr>
                        <th>Ticket #</th>
                        <th>Fecha/Hora</th>
                        <th>Cliente</th>
                        <th>Método Pago</th>
                        <th class="text-end">Total</th>
                        <th class="text-center">Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($ventas as $venta)
                        {{-- Agregué 'align-middle' para que los botones se centren verticalmente --}}
                        <tr class="align-middle {{ $venta->estado == 'anulado' || $venta->estado == 'anulada' ? 'text-muted bg-light' : '' }}">
                            
                            <td>{{ str_pad($venta->id, 6, '0', STR_PAD_LEFT) }}</td>
                            
                            <td>{{ $venta->fecha->format('d/m/Y H:i') }}</td>
                            
                            <td>
                                @if($venta->cliente)
                                    {{ $venta->cliente->nombre }} {{ $venta->cliente->apellido }}
                                @else
                                    <span class="fst-italic text-secondary">Público General</span>
                                @endif
                            </td>
                            
                            <td>
                                @foreach($venta->pagos as $pago)
                                    <span class="badge bg-secondary">{{ ucfirst($pago->metodoPago->nombre) }}</span>
                                @endforeach
                            </td>
                            
                            <td class="text-end fw-bold">
                                @if($venta->estado == 'anulado' || $venta->estado == 'anulada')
                                    <span class="text-decoration-line-through text-danger">S/ {{ number_format($venta->total, 2) }}</span>
                                @else
                                    S/ {{ number_format($venta->total, 2) }}
                                @endif
                            </td>
                            
                            <td class="text-center">
                                @if($venta->estado == 'pagada' || $venta->estado == 'pagado')
                                    <span class="badge bg-success">PAGADA</span>
                                @else
                                    <span class="badge bg-danger">ANULADA</span>
                                @endif
                            </td>

                            {{-- ================= ACCIONES (LÓGICA MEJORADA) ================= --}}
                            <td class="text-center">
            
                                {{-- CASO 1: VENTA ANULADA --}}
                                @if($venta->estado == 'anulado' || $venta->estado == 'anulada')
                                    
                                    <div class="d-flex flex-column align-items-center gap-1">
                                        <span class="badge bg-danger">ANULADO</span>
                                        
                                        {{-- Si hay documentos fiscales asociados, los mostramos --}}
                                        @if($venta->comprobante || $venta->notaCredito)
                                            <div class="btn-group btn-group-sm">
                                                {{-- Ver Factura Original (Tachada/Referencia) --}}
                                                @if($venta->comprobante)
                                                    <a href="{{ route('comprobante.ticket', $venta->comprobante->id) }}" 
                                                    target="_blank" 
                                                    class="btn btn-outline-secondary" 
                                                    title="Ver Factura Original">
                                                        <i class="bi bi-file-earmark"></i> Fac.
                                                    </a>
                                                @endif
                                
                                                {{-- Ver NOTA DE CRÉDITO (Nuevo Botón Importante) --}}
                                                @if($venta->notaCredito)
                                                    <a href="{{ route('comprobante.ticket', $venta->notaCredito->id) }}" 
                                                    target="_blank" 
                                                    class="btn btn-outline-danger" 
                                                    title="Imprimir Nota de Crédito (Devolución)">
                                                        <i class="bi bi-file-earmark-x"></i> NC
                                                    </a>
                                                @endif
                                            </div>
                                        @endif
                                    </div>


                                {{-- CASO 2: VENTA FISCAL ACTIVA (Enviada a SUNAT) --}}
                                @elseif($venta->comprobante)
                                    <div class="btn-group" role="group">
                                        {{-- Badge Serie-Numero --}}
                                        <button type="button" class="btn btn-sm btn-success disabled fw-bold" style="opacity: 1">
                                            {{ $venta->comprobante->serie }}-{{ $venta->comprobante->correlativo }}
                                        </button>
                                        
                                        {{-- Menú Desplegable (PDF, XML, CDR) --}}
                                        <div class="btn-group" role="group">
                                            <button id="btnGroupDrop1" type="button" class="btn btn-sm btn-outline-dark dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                                <i class="bi bi-file-text"></i>
                                            </button>
                                            <ul class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                                <li><a class="dropdown-item" href="{{ route('comprobante.ticket', $venta->comprobante->id) }}" target="_blank"><i class="bi bi-file-pdf text-danger"></i> Ver PDF</a></li>
                                                <li><a class="dropdown-item" href="{{ route('comprobante.xml', $venta->comprobante->id) }}"><i class="bi bi-code-slash text-primary"></i> Descargar XML</a></li>
                                                <li><a class="dropdown-item" href="{{ route('comprobante.cdr', $venta->comprobante->id) }}"><i class="bi bi-file-zip text-success"></i> Descargar CDR</a></li>
                                            </ul>
                                        </div>

                                        {{-- Botón ANULAR (Nota de Crédito) --}}
                                        <button wire:click="anularComprobante({{ $venta->id }})"
                                                wire:confirm="¿Confirmas ANULAR este comprobante ante SUNAT? Se emitirá una Nota de Crédito y se devolverá el stock."
                                                wire:loading.attr="disabled"
                                                class="btn btn-sm btn-outline-danger ms-1" 
                                                title="Anular y Emitir Nota de Crédito">
                                            <i class="bi bi-x-circle"></i>
                                        </button>
                                    </div>


                                {{-- CASO 3: VENTA INTERNA (Pre-SUNAT) --}}
                                @else
                                    <div class="btn-group" role="group">
                                        {{-- Imprimir Ticket Interno --}}
                                        <button onclick="window.open('{{ route('print.ticket', $venta->id) }}', '_blank', 'width=400,height=600')" 
                                                class="btn btn-sm btn-outline-secondary" 
                                                title="Ticket Interno">
                                            <i class="bi bi-printer"></i>
                                        </button>

                                        {{-- Anular Internamente --}}
                                        <button wire:confirm="¿Anular venta interna? Se devolverá el stock." 
                                                wire:click="anularVenta({{ $venta->id }})" 
                                                class="btn btn-sm btn-outline-danger" 
                                                title="Anular">
                                            <i class="bi bi-trash"></i>
                                        </button>

                                        {{-- Emitir CPE --}}
                                        <button wire:click="emitirComprobante({{ $venta->id }})" 
                                                wire:loading.attr="disabled"
                                                wire:confirm="¿Enviar a SUNAT ahora?"
                                                class="btn btn-sm btn-primary" 
                                                title="Emitir CPE">
                                            <i class="bi bi-cloud-arrow-up-fill"></i> CPE
                                        </button>
                                    </div>
                                @endif

                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-4 d-block mb-2"></i>
                                No se encontraron ventas en este periodo.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $ventas->links() }}</div>
    </div>
</div>