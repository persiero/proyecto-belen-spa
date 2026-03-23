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

    {{-- HEADER CON ACCESO RÁPIDO A HISTORIAL --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-cash-coin me-2"></i> Cobros & Ventas</h4>
            <small class="text-muted">Finaliza atenciones, agrega productos y registra el pago</small>
        </div>
        <a href="{{ route('admin.ventas.historial') }}" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-clock-history me-1"></i> Ver Historial de Cobros
        </a>
    </div>

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
                                <th>Servicios/Productos</th>
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
                                                <span class="badge bg-info text-dark bg-opacity-10 border border-info me-1">{{ $s->servicio?->nombre ?? 'Servicio Eliminado' }}</span>
                                            @endforeach
                                            @foreach($t->productos as $p)
                                                <span class="badge bg-warning text-dark bg-opacity-10 border border-warning me-1">{{ $p->producto?->nombre ?? 'Producto Eliminado' }}</span>
                                            @endforeach
                                        </small>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button wire:click="cargarTurno({{ $t->id }})" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="bi bi-arrow-right-short"></i> Cobrar
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
                        <input type="text" wire:model.live.debounce.500ms="searchProducto" class="form-control bg-light border-0" placeholder="Buscar productos para agregar al cobro (shampoo, cremas, etc.)">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($productos as $p)
                            <div class="list-group-item py-3 px-4">
                                <div class="row align-items-center g-3">
                                    {{-- INFO DEL PRODUCTO --}}
                                    <div class="col-md-5">
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded p-2 me-3 text-secondary">
                                                <i class="bi bi-box-seam fs-4"></i>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">{{ $p->nombre }}</h6>
                                                <small class="text-muted">Stock: {{ $p->stock_actual }} un. | S/ {{ number_format($p->precio_venta, 2) }}</small>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- SELECT ESTILISTA (VENDEDOR) --}}
                                    <div class="col-md-5">
                                        <select wire:model="estilista_temp.{{ $p->id }}" class="form-select form-select-sm">
                                            <option value="">Sin vendedor asignado</option>
                                            @foreach($estilistas as $e)
                                                <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- BOTÓN AGREGAR --}}
                                    <div class="col-md-2 text-end">
                                        <button wire:click="addProducto({{ $p->id }})"
                                            class="btn btn-primary rounded-circle shadow-sm"
                                            style="width: 45px; height: 45px;"
                                            title="Agregar al carrito">
                                            <i class="bi bi-plus-lg fs-5"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @empty
                            @if(strlen($searchProducto) > 0)
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-emoji-frown fs-4"></i><br>
                                    No encontramos productos con ese nombre.
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-search fs-4"></i><br>
                                    Busca productos para agregar al cobro
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
                    <h5 class="mb-0"><i class="bi bi-receipt-cutoff me-2"></i> Cobro Actual</h5>
                    @if(count($cart) > 0)
                        <span class="badge bg-danger rounded-pill">{{ count($cart) }} items</span>
                    @endif
                </div>

                <div class="card-body bg-light d-flex flex-column">

                    {{-- SELECCIÓN DE CLIENTE --}}
                    <div class="mb-3 bg-white p-2 rounded border">
                        <label class="form-label small text-muted text-uppercase fw-bold mb-1 ps-1">Cliente a Facturar</label>

                        {{-- ALERTA: Venta mayor a 700 sin cliente válido --}}
                        @if($this->requiereClienteValido)
                            <div class="alert alert-danger border-danger bg-danger bg-opacity-10 p-2 mb-2 small">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                <strong>¡Atención!</strong> Para ventas mayores a S/ 700.00 debe seleccionar un cliente con DNI/RUC válido.
                            </div>
                        @endif

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
                                    <th class="text-center py-2" width="20%">Cant.</th>
                                    <th class="text-center py-2" width="20%">Precio</th>
                                    <th class="text-end pe-3 py-2" width="20%">Subtotal</th>
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
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            {{-- CONTROLES DE CANTIDAD --}}
                                            <div class="input-group input-group-sm justify-content-center" style="width: 70px; margin: 0 auto;">
                                                <button wire:click="decrementQuantity({{ $index }})" class="btn btn-outline-secondary px-1" type="button"
                                                    @if($item['cantidad'] <= 1) disabled @endif>
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="text" class="form-control text-center px-0 bg-white" value="{{ $item['cantidad'] }}" readonly style="font-size: 0.85rem;">
                                                <button wire:click="incrementQuantity({{ $index }})" class="btn btn-outline-secondary px-1" type="button">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </td>
                                        <td class="text-center py-3">
                                            {{-- PRECIO UNITARIO EDITABLE --}}
                                            <div class="input-group input-group-sm" style="width: 90px; margin: 0 auto;">
                                                <span class="input-group-text bg-transparent border-0 px-1">S/</span>
                                                <input type="number" step="0.01"
                                                    wire:model.blur="cart.{{ $index }}.precio"
                                                    wire:change="updatePrice({{ $index }}, $event.target.value)"
                                                    class="form-control text-center border fw-bold"
                                                    style="font-size: 0.85rem;">
                                            </div>
                                        </td>
                                        <td class="text-end pe-3 py-3">
                                            <div class="fw-bold text-dark">S/ {{ number_format($item['subtotal'], 2) }}</div>
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-link text-danger p-0 small text-decoration-none mt-1" style="font-size: 0.75rem;">
                                                <i class="bi bi-trash"></i> Quitar
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-5">
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

                    <button wire:click="openPaymentModal"
                        class="btn btn-success w-100 btn-lg fw-bold shadow-sm py-3"
                        @if(empty($cart) || $this->requiereClienteValido) disabled @endif
                        @if($this->requiereClienteValido) title="Debe seleccionar un cliente válido para ventas mayores a S/ 700" @endif>
                        <i class="bi bi-credit-card-2-front me-2"></i> COBRAR S/ {{ number_format($total, 2) }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE PAGO MIXTO (Mejorado) --}}
    @if($isPaymentModalOpen)
    <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);">
        <div class="modal-dialog modal-dialog-centered modal-lg"> {{-- Ampliamos a modal-lg --}}
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white border-0 py-3">
                    <h5 class="modal-title fw-bold"><i class="bi bi-wallet2 me-2"></i> Finalizar Cobro</h5>
                    <button wire:click="closePaymentModal" class="btn-close btn-close-white"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <div class="row g-4">

                        {{-- COLUMNA IZQUIERDA: AGREGAR PAGO --}}
                        <div class="col-md-7 border-end pe-md-4">
                            <h6 class="fw-bold mb-3 text-secondary text-uppercase small">1. Seleccionar Método</h6>

                            <div class="d-flex flex-wrap gap-2 mb-4">
                                @php
                                    $iconosMetodo = [
                                        'efectivo' => 'bi-cash-stack',
                                        'tarjeta' => 'bi-credit-card',
                                        'yape' => 'bi-qr-code',
                                        'plin' => 'bi-qr-code',
                                        'transferencia' => 'bi-bank',
                                    ];
                                @endphp
                                @foreach($metodos as $m)
                                    <button type="button"
                                        class="btn flex-grow-1 py-2 {{ $metodo_pago_id == $m->id ? 'btn-primary shadow-sm border-primary' : 'btn-outline-secondary bg-white border' }}"
                                        wire:click="cambiarMetodoPago({{ $m->id }})">
                                        <i class="bi {{ $iconosMetodo[strtolower($m->nombre)] ?? 'bi-wallet2' }} fs-5"></i>
                                        <span class="d-block small fw-bold mt-1">{{ ucfirst($m->nombre) }}</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="row g-2 mb-3">
                                <div class="col-12">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" wire:model.live="monto_recibido" class="form-control fs-4 fw-bold text-center text-success border-success" id="montoInput" placeholder="Monto">
                                        <label for="montoInput" class="text-center w-100 fw-bold">Monto a pagar (S/)</label>
                                    </div>
                                </div>

                                @if($metodo_pago_id != 1)
                                <div class="col-12">
                                    <div class="form-floating mt-2">
                                        <input type="text" wire:model.blur="referencia_pago" class="form-control bg-white" id="refInput" placeholder="Nro. Operación">
                                        <label for="refInput">Nro. Operación / Referencia <span class="text-danger">*</span></label>
                                    </div>
                                </div>
                                @endif
                            </div>

                            <button wire:click="agregarPago" class="btn btn-outline-success w-100 fw-bold border-2 py-2 shadow-sm">
                                <i class="bi bi-plus-circle me-2"></i> AGREGAR A LA LISTA DE PAGOS
                            </button>

                            @if (session()->has('error_pago'))
                                <div class="text-danger text-center small mt-3 fw-bold bg-danger bg-opacity-10 p-2 rounded">
                                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('error_pago') }}
                                </div>
                            @endif
                        </div>

                        {{-- COLUMNA DERECHA: RESUMEN Y LISTA --}}
                        <div class="col-md-5 ps-md-4 d-flex flex-column">
                            <div class="text-center mb-3 bg-white p-3 rounded shadow-sm border">
                                <h6 class="text-muted text-uppercase small ls-1 mb-1">Total del Ticket</h6>
                                <h2 class="fw-bold text-dark mb-0">S/ {{ number_format($total, 2) }}</h2>
                            </div>

                            {{-- LISTA DE PAGOS AÑADIDOS --}}
                            <div class="bg-white rounded border flex-grow-1 p-3 mb-3 d-flex flex-column">
                                <h6 class="small fw-bold text-muted border-bottom pb-2 mb-2"><i class="bi bi-list-check me-1"></i> Pagos Registrados</h6>

                                <div class="flex-grow-1" style="max-height: 150px; overflow-y: auto;">
                                    @forelse($listaPagos as $index => $pago)
                                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom small">
                                            <div>
                                                <span class="fw-bold text-dark text-uppercase"><i class="bi bi-check2-circle text-success me-1"></i> {{ $pago['nombre_metodo'] }}</span>
                                                @if($pago['referencia'])
                                                    <br><span class="text-muted ms-3" style="font-size: 0.7rem;">Ref: {{ $pago['referencia'] }}</span>
                                                @endif
                                            </div>
                                            <div class="text-end d-flex align-items-center">
                                                <span class="fw-bold text-dark fs-6">S/ {{ number_format($pago['monto'], 2) }}</span>
                                                <button wire:click="quitarPago({{ $index }})" class="btn btn-link text-danger p-0 ms-2 text-decoration-none" title="Quitar pago">
                                                    <i class="bi bi-x-circle-fill fs-5"></i>
                                                </button>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted small py-4 opacity-50 h-100 d-flex flex-column justify-content-center">
                                            <i class="bi bi-inbox fs-3 mb-1"></i>
                                            Aún no hay pagos añadidos.
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            {{-- ESTADO DEL COBRO (FALTA / VUELTO) --}}
                            @php
                                // Calculamos visualmente cuánto falta o sobra
                                // (Si la lista está vacía, calculamos en base a lo que haya escrito en Efectivo)
                                $total_pagado_ui = count($listaPagos) > 0 ? $total_pagado : (float)$monto_recibido;
                                $diferencia = $total_pagado_ui - $total;
                            @endphp

                            <div class="p-3 rounded border shadow-sm {{ $diferencia >= 0 ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-10 border-warning' }}">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold text-uppercase {{ $diferencia >= 0 ? 'text-success' : 'text-warning text-dark' }}">
                                        {{ $diferencia >= 0 ? 'VUELTO:' : 'FALTA:' }}
                                    </span>
                                    <span class="h4 mb-0 fw-bold {{ $diferencia >= 0 ? 'text-success' : 'text-dark' }}">
                                        S/ {{ number_format(abs($diferencia), 2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0 bg-light pb-4 px-4 mt-2">
                    <button wire:click="procesarVenta" class="btn btn-success w-100 btn-lg shadow fw-bold"
                        @if($diferencia < 0) disabled @endif>
                        <i class="bi bi-receipt me-2"></i> CONFIRMAR E IMPRIMIR TICKET
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
                    <h5 class="fw-bold mb-1">¡Pago Registrado!</h5>
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

                        <a href="{{ route('admin.ventas.historial') }}" class="btn btn-outline-primary border-2">
                            <i class="bi bi-receipt me-2"></i> Ver Todas las Ventas
                        </a>

                        <button wire:click="cerrarSuccessModal" class="btn btn-outline-secondary border-0">
                            Cerrar y Nuevo Cobro
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
    @endif
</div>
