<div>
    {{-- ALERTAS --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        {{-- COLUMNA IZQUIERDA: PRODUCTOS Y TURNOS --}}
        <div class="col-md-7 col-lg-8">
            
            {{-- 1. TURNOS EN ESPERA --}}
            @if($turnosPendientes->count() > 0)
            <div class="card card-outline card-primary mb-3 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="card-title mb-0 text-primary"><i class="bi bi-clock-history"></i> Turnos por Cobrar</h5>
                </div>
                <div class="card-body p-0 table-responsive" style="max-height: 200px;">
                    <table class="table table-hover mb-0 align-middle">
                        <thead class="table-light text-muted small text-uppercase">
                            <tr>
                                <th class="ps-3">Turno</th>
                                <th>Cliente</th>
                                <th>Servicios</th>
                                <th class="text-end pe-3">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($turnosPendientes as $t)
                                <tr>
                                    <td class="ps-3 fw-bold text-muted">#{{ str_pad($t->id, 4, '0', STR_PAD_LEFT) }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $t->cliente->nombre }} {{ $t->cliente->apellido }}</div>
                                        <small class="text-muted">{{ $t->hora_inicio->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <small class="text-truncate d-block" style="max-width: 200px;">
                                            @foreach($t->servicios as $s)
                                                {{ $s->servicio->nombre }}, 
                                            @endforeach
                                        </small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button wire:click="cargarTurno({{ $t->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-arrow-right-short"></i> Cargar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- 2. BUSCADOR DE PRODUCTOS --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-light border-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" wire:model.live="searchProducto" class="form-control bg-light border-0" placeholder="Buscar productos para venta rápida (Shampoo, cremas, etc)...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($productos as $p)
                            <button wire:click="addProducto({{ $p->id }})" class="list-group-item list-group-item-action py-3 px-4 d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center">
                                    <div class="bg-light rounded p-2 me-3 text-secondary">
                                        <i class="bi bi-box-seam fs-4"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0 fw-bold text-dark">{{ $p->nombre }}</h6>
                                        <small class="text-muted">Stock Disponible: {{ $p->stock_actual }} un.</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="fs-5 fw-bold text-success">S/ {{ number_format($p->precio_venta, 2) }}</span>
                                    <i class="bi bi-plus-circle-fill text-primary ms-2 fs-5"></i>
                                </div>
                            </button>
                        @empty
                            @if(strlen($searchProducto) > 0)
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-emoji-frown fs-4"></i><br>
                                    No encontramos productos con ese nombre.
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: CARRITO Y PAGO --}}
        <div class="col-md-5 col-lg-4">
            <div class="card shadow border-0 h-100">
                <div class="card-header bg-dark text-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="bi bi-cart4 me-2"></i> Venta Actual</h5>
                    @if(count($cart) > 0)
                        <span class="badge bg-danger rounded-pill">{{ count($cart) }} items</span>
                    @endif
                </div>
                
                <div class="card-body bg-light d-flex flex-column">
                    
                    {{-- SELECCIÓN DE CLIENTE --}}
                    <div class="mb-3 bg-white p-2 rounded border">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-1 ps-1">Cliente</label>
                        {{-- 1. ESTADO: CLIENTE YA SELECCIONADO --}}
                        @if($cliente_id && $cliente_seleccionado_nombre)
                            <div class="input-group" wire:key="cliente-seleccionado">
                                <span class="input-group-text bg-success text-white border-0">
                                    <i class="bi bi-person-check-fill"></i>
                                </span>
                                <input type="text" class="form-control bg-white fw-bold text-success" 
                                    value="{{ $cliente_seleccionado_nombre }}" readonly>
                                <button class="btn btn-outline-danger" wire:click="limpiarCliente" title="Quitar cliente">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </div>

                        {{-- 2. ESTADO: BUSCANDO CLIENTE --}}
                        @else
                            <div class="input-group" wire:key="cliente-buscador">
                                <span class="input-group-text bg-light border-end-0">
                                    <i class="bi bi-search text-secondary"></i>
                                </span>
                                {{-- Input con debounce para no saturar el servidor (300ms espera a que termines de escribir) --}}
                                <input type="text" 
                                    class="form-control border-start-0 ps-0" 
                                    wire:model.live.debounce.300ms="buscar_cliente" 
                                    placeholder="Buscar por Nombre o DNI/RUC..."
                                    autocomplete="off">
                            </div>

                            {{-- 3. LISTA DE RESULTADOS FLOTANTE --}}
                            @if(count($clientes_encontrados) > 0)
                                <div class="list-group position-absolute w-100 shadow-lg mt-1" 
                                    style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                    
                                    @foreach($clientes_encontrados as $cliente)
                                        <button type="button" 
                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                wire:click="seleccionarCliente({{ $cliente->id }})">
                                            
                                            <div>
                                                <span class="fw-bold">{{ $cliente->nombre }} {{ $cliente->apellido }}</span><br>
                                                <small class="text-muted">
                                                    <i class="bi bi-card-heading"></i> {{ $cliente->numero_documento }}
                                                </small>
                                            </div>
                                            
                                            <i class="bi bi-chevron-right text-muted small"></i>
                                        </button>
                                    @endforeach
                                </div>
                            
                            {{-- MENSAJE SI NO ENCUENTRA NADA (Opcional) --}}
                            @elseif(strlen($buscar_cliente) > 2)
                                <div class="position-absolute w-100 mt-1" style="z-index: 1050;">
                                    <div class="alert alert-warning p-2 small shadow-sm border-warning">
                                        <i class="bi bi-exclamation-circle me-1"></i> No se encontró el cliente.
                                        {{-- Aquí podrías poner un botón para abrir modal de crear cliente --}}
                                    </div>
                                </div>
                            @endif
                        @endif
                        
                        @error('cliente_id') <span class="text-danger small">{{ $message }}</span> @enderror
                    </div>

                    {{-- TABLA CARRITO MEJORADA --}}
                    <div class="flex-grow-1 overflow-auto bg-white rounded border mb-3" style="min-height: 300px; max-height: 500px;">
                        <table class="table table-borderless align-middle mb-0">
                            <thead class="bg-light sticky-top border-bottom">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-3 py-2">Producto/Servicio</th>
                                    <th class="text-center py-2" width="25%">Cant.</th>
                                    <th class="text-end pe-3 py-2" width="25%">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cart as $index => $item)
                                    <tr class="border-bottom">
                                        <td class="ps-3 py-3">
                                            <div class="fw-bold text-dark lh-sm">{{ $item['nombre'] }}</div>
                                            <div class="small text-muted mt-1">
                                                @if($item['tipo'] == 'servicio')
                                                    <span class="badge bg-info text-dark bg-opacity-10 border border-info px-1">Servicio</span>
                                                @else
                                                    <span class="badge bg-warning text-dark bg-opacity-10 border border-warning px-1">Producto</span>
                                                @endif
                                                <span class="ms-1">x S/ {{ number_format($item['precio'], 2) }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            {{-- CONTROLES DE CANTIDAD --}}
                                            <div class="input-group input-group-sm justify-content-center" style="width: 80px; margin: 0 auto;">
                                                <button wire:click="decrementQuantity({{ $index }})" class="btn btn-outline-secondary px-1" type="button" 
                                                    @if($item['cantidad'] <= 1) disabled @endif>
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="text" class="form-control text-center px-0 bg-white" value="{{ $item['cantidad'] }}" readonly style="font-size: 0.9rem;">
                                                <button wire:click="incrementQuantity({{ $index }})" class="btn btn-outline-secondary px-1" type="button">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-3">
                                            <div class="fw-bold text-dark">S/ {{ number_format($item['subtotal'], 2) }}</div>
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-link text-danger p-0 small text-decoration-none mt-1" style="font-size: 0.8rem;">
                                                <i class="bi bi-trash"></i> Eliminar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="bi bi-cart-x display-4"></i><br>
                                                <span class="mt-2 d-block">El carrito está vacío</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- TOTALES --}}
                    <div class="bg-white p-3 rounded border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Subtotal</span>
                            <span class="fw-bold">S/ {{ number_format($total / 1.18, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">IGV (18%)</span>
                            <span class="fw-bold">S/ {{ number_format($total - ($total / 1.18), 2) }}</span>
                        </div>
                        <div class="border-top my-2"></div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="h5 mb-0 fw-bold text-dark">TOTAL A PAGAR</span>
                            <span class="h3 mb-0 fw-bold text-success">S/ {{ number_format($total, 2) }}</span>
                        </div>
                    </div>

                    <button wire:click="openPaymentModal" class="btn btn-success w-100 btn-lg fw-bold shadow-sm py-3" @if(empty($cart)) disabled @endif>
                        <i class="bi bi-credit-card-2-front me-2"></i> COBRAR S/ {{ number_format($total, 2) }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE PAGO (Mejorado) --}}
    @if($isPaymentModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0">
                    <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 me-2"></i> Finalizar Venta</h5>
                    <button wire:click="closePaymentModal" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="text-center mb-4">
                        <h6 class="text-muted text-uppercase small ls-1">Monto Total a Cobrar</h6>
                        <h1 class="display-4 fw-bold text-success">S/ {{ number_format($total, 2) }}</h1>
                    </div>

                    <div class="card border-0 shadow-sm p-3 mb-3">
                        <label class="form-label fw-bold mb-2">Método de Pago</label>
                        <div class="d-flex gap-2 mb-3">
                            @foreach($metodos as $m)
                                <button type="button" class="btn flex-grow-1 {{ $metodo_pago_id == $m->id ? 'btn-primary' : 'btn-outline-secondary' }}" 
                                    wire:click="cambiarMetodoPago({{ $m->id }})">
                                    <i class="bi {{ $m->nombre == 'efectivo' ? 'bi-cash-stack' : ($m->nombre == 'yape' ? 'bi-qr-code' : 'bi-credit-card') }}"></i>
                                    <br><small>{{ ucfirst($m->nombre) }}</small>
                                </button>
                            @endforeach
                        </div>
                        
                        {{-- INPUT DE REFERENCIA (Solo si NO es efectivo) --}}
                        {{-- Asumimos que ID 1 es Efectivo. Ajusta si tu ID es otro --}}
                        @if($metodo_pago_id != 1) 
                            <div class="mb-3 text-start bg-light p-3 rounded border">
                                <label class="form-label small fw-bold text-secondary">
                                    <i class="bi bi-hash"></i> Nro. Operación / Referencia
                                </label>
                                <input type="text" wire:model.live="referencia_pago" class="form-control" 
                                    placeholder="Ej: 987654 (Yape) o 4 últimos dígitos tarjeta">
                            </div>
                        @endif

                        {{-- INPUT DE MONTO (Solo si ES efectivo pide vuelto, si es digital se asume exacto) --}}
                        @if($metodo_pago_id == 1) 
                            <div class="form-floating mb-3">
                                <input type="number" step="0.01" wire:model.live="monto_recibido" class="form-control fs-4 fw-bold text-center" id="montoInput" placeholder="Monto">
                                <label for="montoInput" class="text-center w-100">Dinero Recibido (S/)</label>
                            </div>
                            @if($vuelto >= 0)
                                <div class="alert alert-success d-flex align-items-center justify-content-between mb-0 border-0 shadow-sm">
                                    <span class="fw-bold"><i class="bi bi-arrow-return-left me-2"></i> VUELTO:</span>
                                    <span class="h4 mb-0 fw-bold">S/ {{ number_format($vuelto, 2) }}</span>
                                </div>
                            @else
                                <div class="alert alert-danger d-flex align-items-center justify-content-between mb-0 border-0 shadow-sm">
                                    <span class="fw-bold"><i class="bi bi-exclamation-circle me-2"></i> FALTA:</span>
                                    <span class="h4 mb-0 fw-bold">S/ {{ number_format(abs($vuelto), 2) }}</span>
                                </div>
                            @endif
                        @endif                

                    @if (session()->has('error_pago'))
                        <div class="text-danger text-center small mt-2 fw-bold">{{ session('error_pago') }}</div>
                    @endif
                </div>
                <div class="modal-footer border-0 pt-0 bg-light pb-4 px-4">
                    <button wire:click="procesarVenta" class="btn btn-success w-100 btn-lg shadow fw-bold" 
                        {{-- Lógica de Bloqueo: --}}
                        {{-- 1. Si es Efectivo (ID 1) y falta dinero (vuelto < 0) -> Bloqueado --}}
                        {{-- 2. Si NO es Efectivo y la referencia está vacía -> Bloqueado --}}
                        @if( ($metodo_pago_id == 1 && $vuelto < 0) || ($metodo_pago_id != 1 && empty($referencia_pago)) )
                            disabled 
                        @endif>
                        <i class="bi bi-check-lg me-2"></i> CONFIRMAR PAGO
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- MODAL ÉXITO (Ticket) --}}
    @if($isSuccessModalOpen && $ultimaVenta)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.8); backdrop-filter: blur(5px);">
        <div class="modal-dialog modal-dialog-centered modal-sm"> 
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 pb-0 justify-content-end">
                     <button wire:click="cerrarSuccessModal" class="btn-close"></button>
                </div>
                <div class="modal-body text-center px-4 pt-0 pb-4">
                    
                    <div class="mb-3">
                        <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 70px; height: 70px;">
                            <i class="bi bi-check-lg display-5"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-1">¡Venta Exitosa!</h5>
                    <p class="text-muted small">La operación se registró correctamente.</p>
                    
                    {{-- TICKET VIRTUAL --}}
                    <div class="bg-light p-3 rounded border text-start mb-3 font-monospace position-relative" style="font-size: 0.8rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                         {{-- Efecto de borde dentado (opcional, solo CSS) --}}
                        <div class="text-center fw-bold border-bottom pb-2 mb-2">
                            BELEN SPA<br>
                            TICKET #{{ str_pad($ultimaVenta->id, 6, '0', STR_PAD_LEFT) }}
                        </div>
                        
                        <div class="d-flex flex-column gap-1 mb-2">
                            @foreach($ultimaVenta->detalles as $det)
                                <div class="d-flex justify-content-between">
                                    <span>{{ $det->cantidad }} x {{ Str::limit($det->nombre_item, 18) }}</span>
                                    <span>{{ number_format($det->subtotal, 2) }}</span>
                                </div>
                            @endforeach
                        </div>
                        
                        <div class="border-top pt-2 d-flex justify-content-between fw-bold fs-6">
                            <span>TOTAL:</span>
                            <span>S/ {{ number_format($ultimaVenta->total, 2) }}</span>
                        </div>
                    </div>

                    <div class="d-grid gap-2">
                        <button class="btn btn-dark shadow-sm" 
                            onclick="window.open('{{ route('print.ticket', $ultimaVenta->id) }}', '_blank', 'width=400,height=600')">
                            <i class="bi bi-printer-fill me-2"></i> Imprimir Ticket
                        </button>

                        @if($ultimaVenta->cliente && $ultimaVenta->cliente->telefono)
                            @php
                                // Construimos el mensaje detallado para WhatsApp
                                $mensaje = "Hola *" . $ultimaVenta->cliente->nombre . "*! 👋\nGracias por visitarnos en Belen Spa.\n\n*Resumen de tu visita:*\n";
                                foreach($ultimaVenta->detalles as $det) {
                                    $mensaje .= "• " . $det->nombre_item . "\n";
                                }
                                $mensaje .= "\n*Total Pagado: S/ " . number_format($ultimaVenta->total, 2) . "*\n\n¡Esperamos verte pronto! ✨";
                                $url = "https://wa.me/51" . $ultimaVenta->cliente->telefono . "?text=" . urlencode($mensaje);
                            @endphp
                            <a href="{{ $url }}" target="_blank" class="btn btn-success shadow-sm">
                                <i class="bi bi-whatsapp me-2"></i> Enviar Comprobante
                            </a>
                        @endif

                        <button wire:click="cerrarSuccessModal" class="btn btn-outline-secondary border-0">
                            Cerrar y Nueva Venta
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>