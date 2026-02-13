<div>
    {{-- COMPONENTE HISTORIAL --}}
    @livewire('admin.clientes.historial-cliente')

    {{-- MENSAJES DE ALERTA --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert" 
             style="background-color: #d1e7dd; color: #0f5132;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 1. MONITOR DE ESTILISTAS (Auto-refresco cada 30s) --}}
    <div wire:poll.30s>
        <h5 class="fw-bold text-dark mb-3 ps-1">
            <i class="bi bi-person-workspace text-primary me-2"></i> Monitor de Estilistas
        </h5>
        
        <div class="row g-3 mb-4">
            @foreach($monitorEstilistas as $estilista)
                @php
                    $ocupado = $estilista->atencionesEnCurso->isNotEmpty();
                    // Tomamos la primera atención activa (si tiene varias, muestra la primera)
                    $atencion = $ocupado ? $estilista->atencionesEnCurso->first() : null;
                @endphp
        
                <div class="col-md-3 col-sm-6">
                    <div class="card h-100 shadow-sm border-0 position-relative overflow-hidden">
                        {{-- Borde superior de color según estado --}}
                        <div class="position-absolute top-0 start-0 w-100" 
                             style="height: 4px; background-color: {{ $ocupado ? '#ffc107' : '#198754' }};">
                        </div>
                        
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold mb-0 text-truncate text-dark" title="{{ $estilista->nombre }}">
                                    {{ Str::limit($estilista->nombre, 18) }}
                                </h6>
                                @if($ocupado)
                                    <span class="badge bg-warning text-dark shadow-sm">
                                        <i class="bi bi-scissors"></i> Ocupado
                                    </span>
                                @else
                                    <span class="badge bg-success shadow-sm">
                                        <i class="bi bi-check-lg"></i> Libre
                                    </span>
                                @endif
                            </div>
        
                            @if($ocupado && $atencion)
                                <div class="bg-light p-2 rounded border border-light mt-2" style="font-size: 0.85rem;">
                                    <div class="text-primary fw-bold text-truncate mb-1">
                                        {{ $atencion->servicio->nombre ?? 'Servicio' }}
                                    </div>
                                    <div class="text-muted text-truncate mb-2">
                                        <i class="bi bi-person-fill me-1"></i> {{ $atencion->turno->cliente->nombre ?? 'Cliente' }}
                                    </div>
                                    <div class="d-flex justify-content-between text-secondary border-top pt-2 mt-1">
                                        <span><i class="bi bi-clock me-1"></i> {{ $atencion->created_at->format('H:i') }}</span>
                                        <span class="fw-bold">{{ $atencion->created_at->diffForHumans(null, true) }}</span>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-3 opacity-25">
                                    <i class="bi bi-emoji-smile fs-3"></i>
                                    <p class="mb-0 small fw-bold">Disponible</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    {{-- 2. LISTADO DE TURNOS ACTIVOS --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-event me-2"></i> Recepción: Atenciones en Curso</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.clientes') }}" 
                       target="_blank" 
                       class="btn btn-outline-secondary shadow-sm"
                       title="Abrir módulo de clientes">
                        <i class="bi bi-person-plus me-1"></i> Nuevo Cliente
                    </a>
                    <button wire:click="create()" class="btn btn-primary shadow-sm text-dark fw-bold">
                        <i class="bi bi-plus-lg me-1"></i> Nueva Atención
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead style="background-color: var(--belen-dark); color: white;">
                    <tr>
                        <th class="ps-4 py-3">Turno / Cliente</th>
                        <th class="py-3">Inicio</th>
                        <th class="py-3">Servicios en Curso</th>
                        <th class="py-3 text-center">Estado</th>
                        <th class="py-3 text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($turnos as $turno)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-dark fs-6">{{ $turno->cliente->nombre }} {{ $turno->cliente->apellido }}</div>
                                <small class="text-muted fw-bold">#{{ str_pad($turno->id, 5, '0', STR_PAD_LEFT) }}</small>
                            </td>
                            <td>
                                <div class="text-dark"><i class="bi bi-clock me-1 text-secondary"></i> {{ $turno->hora_inicio->format('h:i A') }}</div>
                                <small class="text-muted">{{ $turno->hora_inicio->diffForHumans() }}</small>
                            </td>
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    @foreach($turno->servicios as $detalle)
                                        <div class="d-flex align-items-center bg-light px-2 py-1 rounded border">
                                            <small class="fw-bold text-dark me-auto">{{ $detalle->servicio->nombre }}</small>
                                            <span class="badge bg-secondary fw-normal text-white ms-2" style="font-size: 0.7rem;">
                                                {{ $detalle->estilista->nombre }}
                                            </span>
                                        </div>
                                    @endforeach
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                                    En Atención
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <button wire:click="edit({{ $turno->id }})" class="btn btn-sm btn-outline-primary border-0 bg-light me-1 shadow-sm" title="Editar / Agregar">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                
                                <a href="{{ route('admin.pos', $turno->id) }}" class="btn btn-sm btn-success text-white shadow-sm fw-bold me-1" title="Ir a Cobros">
                                    <i class="bi bi-cash-coin me-1"></i> Cobrar
                                </a>

                                <button wire:confirm="¿Cancelar este turno? Se perderá el registro." wire:click="cancelar({{ $turno->id }})" class="btn btn-sm btn-outline-danger border-0 bg-light shadow-sm" title="Cancelar">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted opacity-50">
                                    <i class="bi bi-shop-window fs-1 d-block mb-2"></i>
                                    No hay clientes en atención en este momento.
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">{{ $turnos->links() }}</div>
    </div>

    {{-- MODAL CREAR / EDITAR ATENCIÓN --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $turno_id ? 'Editar Atención' : 'Nueva Atención' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form>
                        {{-- 1. CLIENTE --}}
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary small text-uppercase">1. Cliente</label>
                            {{-- 1. ESTADO: CLIENTE YA SELECCIONADO --}}
                            @if($id_cliente && $cliente_seleccionado_nombre)
                                <div class="d-flex gap-2 mb-2">
                                    <button type="button" wire:click="$dispatch('abrirHistorial', { clienteId: {{ $id_cliente }} })" 
                                        class="btn btn-sm btn-light border w-100 shadow-sm">
                                        <i class="bi bi-clock-history text-purple me-1"></i>
                                        <span class="text-purple fw-bold">Ver Historial del Cliente</span>
                                    </button>
                                </div>
                                <div class="input-group" wire:key="turno-cliente-selected">
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
                                <div class="input-group" wire:key="turno-cliente-search">
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
                                
                                {{-- MENSAJE SI NO ENCUENTRA NADA --}}
                                @elseif(strlen($buscar_cliente) > 2)
                                    <div class="position-absolute mt-1" style="z-index: 1050; left: 0; right: 0;">
                                        <div class="alert alert-warning p-2 mb-0 shadow-sm">
                                            <div class="d-flex flex-column gap-1">
                                                <small class="text-dark fw-medium">
                                                    <i class="bi bi-exclamation-triangle me-1"></i>Cliente no encontrado
                                                </small>
                                                <a href="{{ route('admin.clientes') }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-dark fw-bold"
                                                   style="font-size: 0.75rem;">
                                                    <i class="bi bi-person-plus-fill me-1"></i> Registrar Cliente
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            
                            @error('id_cliente') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- 2. SERVICIOS --}}
                        <div class="p-3 bg-white rounded shadow-sm border mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <label class="form-label fw-bold text-primary mb-0 small text-uppercase">2. Servicios</label>
                                <button wire:click="addItem()" type="button" class="btn btn-sm btn-outline-primary fw-bold">
                                    <i class="bi bi-plus-lg"></i> Agregar Servicio
                                </button>
                            </div>

                            @foreach($items as $index => $item)
                                <div class="row g-2 align-items-center mb-2">
                                    <div class="col-md-5">
                                        <select wire:model.live="items.{{ $index }}.servicio_id" 
                                            class="form-select form-select-sm border-0 fw-bold {{ !empty($item['servicio_id']) ? 'bg-white text-primary' : 'bg-light' }}">
                                            <option value="">-- Servicio --</option>
                                            @foreach($servicios as $s) <option value="{{ $s->id }}">{{ $s->nombre }}</option> @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select wire:model.live="items.{{ $index }}.estilista_id" 
                                            class="form-select form-select-sm border-0 fw-bold {{ !empty($item['estilista_id']) ? 'bg-white text-primary' : 'bg-light' }}">
                                            <option value="">-- Profesional --</option>
                                            @foreach($estilistas as $e)
                                                @php
                                                    // Validar si está ocupado
                                                    $isBusy = $e->atencionesEnCurso->isNotEmpty();
                                                    // Validar si está ocupado EN ESTE TURNO (es decir, conmigo)
                                                    $busyWithMe = $turno_id && $e->atencionesEnCurso->where('id_turno', $turno_id)->isNotEmpty();
                                                    
                                                    // Estilos visuales
                                                    $style = '';
                                                    $text = '';
                                                    if($isBusy) {
                                                        if($busyWithMe) { $style = 'color: green; font-weight: bold;'; $text = '(Asignado)'; }
                                                        else { $style = 'color: red;'; $text = '(Ocupado)'; }
                                                    }
                                                @endphp
                                                <option value="{{ $e->id }}" style="{{ $style }}">
                                                    {{ $e->nombre }} {{ $text }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text border-0 bg-transparent text-muted">S/</span>
                                            <input type="number" step="0.01" wire:model="items.{{ $index }}.precio" 
                                                class="form-control border-0 fw-bold text-end {{ !empty($item['precio']) && $item['precio'] > 0 ? 'bg-white text-primary' : 'bg-light' }}">
                                        </div>
                                    </div>
                                    <div class="col-md-1 text-center">
                                        <button wire:click="removeItem({{ $index }})" type="button" class="btn btn-sm text-danger hover-bg-danger rounded-circle">
                                            <i class="bi bi-x-lg"></i>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                            @error('items') <div class="text-danger small mt-2 text-center">{{ $message }}</div> @enderror
                        </div>

                        {{-- NUEVO: 3. PRODUCTOS SUGERIDOS --}}
                        <div class="p-3 bg-white rounded shadow-sm border mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
                                <label class="form-label fw-bold text-success mb-0 small text-uppercase">
                                    <i class="bi bi-bag-heart-fill me-1"></i> 3. Productos
                                </label>
                                <button wire:click="addProducto()" type="button" class="btn btn-sm btn-outline-success fw-bold">
                                    <i class="bi bi-plus-lg"></i> Agregar Producto
                                </button>
                            </div>

                            @if(empty($items_productos))
                                <div class="text-center text-muted py-2 small">
                                    Sin productos agregados.
                                </div>
                            @else
                                @foreach($items_productos as $index => $prod)
                                    <div class="row g-2 align-items-center mb-3 p-2 bg-light rounded position-relative" wire:key="producto-{{ $index }}-{{ $prod['producto_id'] ?? 'new' }}">
                                        {{-- BUSCADOR DE PRODUCTO --}}
                                        <div class="col-md-4">
                                            @if(isset($prod['producto_id']) && $prod['producto_id'] && isset($prod['producto_nombre']) && $prod['producto_nombre'])
                                                {{-- PRODUCTO YA SELECCIONADO --}}
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-success text-white border-0">
                                                        <i class="bi bi-check-circle-fill"></i>
                                                    </span>
                                                    <input type="text" class="form-control bg-white fw-bold text-success border" 
                                                        value="{{ $prod['producto_nombre'] }}" readonly>
                                                    <button type="button" class="btn btn-outline-danger btn-sm" 
                                                        wire:click="limpiarProducto({{ $index }})" title="Cambiar producto">
                                                        <i class="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            @else
                                                {{-- BUSCADOR ACTIVO --}}
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light border-end-0">
                                                        <i class="bi bi-search text-secondary"></i>
                                                    </span>
                                                    <input type="text" 
                                                        class="form-control border-start-0 ps-0" 
                                                        wire:model.live.debounce.300ms="buscar_producto.{{ $index }}" 
                                                        placeholder="Buscar producto..."
                                                        autocomplete="off">
                                                </div>

                                                {{-- RESULTADOS DE BÚSQUEDA --}}
                                                @if(isset($productos_encontrados[$index]) && count($productos_encontrados[$index]) > 0)
                                                    <div class="list-group position-absolute w-100 shadow-lg mt-1" 
                                                        style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                                        @foreach($productos_encontrados[$index] as $p)
                                                            <button type="button" 
                                                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
                                                                wire:click="seleccionarProducto({{ $index }}, {{ $p->id }})">
                                                                <div>
                                                                    <span class="fw-bold">{{ $p->nombre }}</span><br>
                                                                    <small class="text-muted">
                                                                        <i class="bi bi-box-seam"></i> Stock: {{ $p->stock_actual }} | 
                                                                        S/ {{ number_format($p->precio_venta, 2) }}
                                                                    </small>
                                                                </div>
                                                                <i class="bi bi-chevron-right text-muted small"></i>
                                                            </button>
                                                        @endforeach
                                                    </div>
                                                @elseif(isset($buscar_producto[$index]) && strlen($buscar_producto[$index]) > 2)
                                                    <div class="position-absolute mt-1" style="z-index: 1050; left: 0; right: 0;">
                                                        <div class="alert alert-warning p-2 mb-0 shadow-sm small">
                                                            <i class="bi bi-exclamation-triangle me-1"></i>Producto no encontrado o sin stock
                                                        </div>
                                                    </div>
                                                @endif
                                            @endif
                                        </div>
                                        
                                        {{-- SELECT ESTILISTA (QUIÉN VENDIÓ) - OPCIONAL --}}
                                        <div class="col-md-3">
                                            <select wire:model.live="items_productos.{{ $index }}.estilista_id" 
                                                class="form-select form-select-sm border fw-bold {{ !empty($prod['estilista_id']) ? 'bg-white text-success' : 'bg-white' }}">
                                                <option value="">-- Sin vendedor --</option>
                                                @foreach($estilistas as $e)
                                                    <option value="{{ $e->id }}">{{ $e->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- CANTIDAD CON BOTONES +/- --}}
                                        <div class="col-md-2">
                                            <div class="input-group input-group-sm">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                    wire:click="$set('items_productos.{{ $index }}.cantidad', {{ max(1, ($prod['cantidad'] ?? 1) - 1) }})">
                                                    <i class="bi bi-dash"></i>
                                                </button>
                                                <input type="number" min="1" 
                                                    wire:model.live="items_productos.{{ $index }}.cantidad" 
                                                    class="form-control text-center bg-white border" 
                                                    style="max-width: 60px;">
                                                <button type="button" class="btn btn-outline-secondary" 
                                                    wire:click="$set('items_productos.{{ $index }}.cantidad', {{ ($prod['cantidad'] ?? 1) + 1 }})">
                                                    <i class="bi bi-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        {{-- PRECIO TOTAL (EDITABLE) --}}
                                        <div class="col-md-2">
                                            @if(isset($prod['precio_unitario']) && $prod['precio_unitario'] > 0)
                                                <small class="text-muted d-block text-end">S/ {{ number_format($prod['precio_unitario'], 2) }} c/u</small>
                                            @endif
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text border-0 bg-transparent text-muted">S/</span>
                                                <input type="number" step="0.01" 
                                                    wire:model="items_productos.{{ $index }}.precio" 
                                                    class="form-control border fw-bold text-end {{ !empty($prod['precio']) && $prod['precio'] > 0 ? 'bg-white text-success' : 'bg-light' }}">
                                            </div>
                                        </div>

                                        {{-- BORRAR --}}
                                        <div class="col-md-1 text-center">
                                            <button wire:click="removeProducto({{ $index }})" type="button" class="btn btn-sm text-danger hover-bg-danger rounded-circle">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                            @error('items_productos') <div class="text-danger small mt-2 text-center">{{ $message }}</div> @enderror
                        </div>

                        {{-- 4. OBSERVACIONES --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary small text-uppercase">Observaciones (Opcional)</label>
                            <textarea wire:model="observaciones" class="form-control border-0 shadow-sm" rows="2" placeholder="Notas internas..."></textarea>
                        </div>

                    </form>
                </div>
                
                <div class="modal-footer bg-white py-3">
                    <button wire:click="closeModal()" type="button" class="btn btn-light border">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary px-4 shadow-sm text-dark fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> Confirmar Atención
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>