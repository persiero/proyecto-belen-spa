<div>
    <div class="row">
        <div class="col-12">
            
            {{-- ALERTAS --}}
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card card-outline card-purple shadow-sm border-0"> 
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-primary fw-bold mb-0">
                            <i class="bi bi-tags-fill me-2"></i> Catálogo de Productos
                        </h3>
                        
                        <div class="card-tools d-flex gap-2">
                            <div class="input-group input-group-sm" style="width: 250px;">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                <input wire:model.live="search" type="text" class="form-control border-start-0" placeholder="Buscar por nombre...">
                            </div>
                            
                            <button wire:click="create()" class="btn btn-primary btn-sm px-3 fw-bold shadow-sm">
                                <i class="bi bi-plus-lg"></i> Nuevo
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary small text-uppercase">
                                <tr>
                                    <th class="ps-4">Producto / Código</th>
                                    <th>Estado Operativo</th> {{-- ANTES TIPO --}}
                                    <th class="text-center">Stock Venta</th>
                                    <th class="text-center">Stock Insumo</th>
                                    <th class="text-end">Precio Venta</th>
                                    <th class="text-end pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productos as $item)
                                    {{-- LÓGICA DE ESTADO DINÁMICO (Igual que en Inventario) --}}
                                    @php
                                        $estadoVisual = '';
                                        $claseBadge = '';
                                        $icono = '';

                                        if ($item->stock_actual > 0 && $item->stock_insumo > 0) {
                                            $estadoVisual = 'Mixto (Activo)';
                                            $claseBadge = 'bg-primary bg-opacity-10 text-primary border border-primary'; 
                                            $icono = 'bi-arrow-left-right';
                                        } elseif ($item->stock_insumo > 0 && $item->stock_actual == 0) {
                                            $estadoVisual = 'Uso Interno';
                                            $claseBadge = 'bg-warning bg-opacity-10 text-dark border border-warning';
                                            $icono = 'bi-bucket';
                                        } elseif ($item->stock_actual > 0 && $item->stock_insumo == 0) {
                                            $estadoVisual = 'Venta';
                                            $claseBadge = 'bg-success bg-opacity-10 text-success border border-success';
                                            $icono = 'bi-shop';
                                        } else {
                                            // Si no tiene stock, mostramos la intención original de la BD
                                            $estadoVisual = ucfirst($item->tipo);
                                            $claseBadge = 'bg-secondary bg-opacity-10 text-secondary';
                                            $icono = 'bi-box';
                                        }
                                    @endphp

                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $item->nombre }}</div>
                                            @if($item->codigo_barras)
                                                <small class="text-muted"><i class="bi bi-upc"></i> {{ $item->codigo_barras }}</small>
                                            @else
                                                <small class="text-muted fst-italic">Sin código</small>
                                            @endif
                                        </td>
                                        
                                        <td>
                                            <span class="badge rounded-pill {{ $claseBadge }} fw-normal px-2 py-1">
                                                <i class="bi {{ $icono }}"></i> {{ $estadoVisual }}
                                            </span>
                                        </td>

                                        {{-- STOCK VENTA --}}
                                        <td class="text-center">
                                            @if($item->stock_actual > 0)
                                                <span class="fw-bold fs-6 text-dark">{{ $item->stock_actual }}</span>
                                                @if($item->stock_actual <= $item->stock_minimo)
                                                    <i class="bi bi-exclamation-circle-fill text-danger small ms-1" title="Stock Bajo"></i>
                                                @endif
                                            @else
                                                <span class="text-muted opacity-25">-</span>
                                            @endif
                                        </td>

                                        {{-- STOCK INSUMO --}}
                                        <td class="text-center">
                                            @if($item->stock_insumo > 0)
                                                <span class="fw-bold fs-6 text-dark">{{ $item->stock_insumo }}</span>
                                            @else
                                                <span class="text-muted opacity-25">-</span>
                                            @endif
                                        </td>

                                        {{-- PRECIOS --}}
                                        <td class="text-end">
                                            @if($item->tipo != 'insumo')
                                                <div class="fw-bold text-success">S/ {{ number_format($item->precio_venta, 2) }}</div>
                                            @else
                                                <small class="text-muted">N/A</small>
                                            @endif
                                            <small class="text-muted d-block" style="font-size: 0.75rem;">
                                                Costo: S/ {{ number_format($item->costo_compra, 2) }}
                                            </small>
                                        </td>

                                        <td class="text-end pe-4">
                                            <div class="btn-group">
                                                <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-outline-secondary" title="Editar">
                                                    <i class="bi bi-pencil-fill"></i>
                                                </button>
                                                <button wire:confirm="¿Eliminar este producto?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-outline-danger" title="Eliminar">
                                                    <i class="bi bi-trash-fill"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <i class="bi bi-box-seam display-4 opacity-50"></i><br>
                                            <span class="mt-2 d-block">No se encontraron productos.</span>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $productos->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL CREAR / EDITAR --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5); backdrop-filter: blur(2px);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="bi {{ $producto_id ? 'bi-pencil-square' : 'bi-plus-circle' }} me-2"></i>
                        {{ $producto_id ? 'Editar Producto' : 'Nuevo Producto' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>
                
                <div class="modal-body p-4">
                    <form>
                        {{-- SECCIÓN 1: IDENTIFICACIÓN --}}
                        <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2">Información General</h6>
                        
                        <div class="row g-3 mb-4">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre" class="form-control form-control-lg @error('nombre') is-invalid @enderror" placeholder="Ej: Shampoo Keratina 500ml">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tipo Inicial</label>
                                <select wire:model.live="tipo" class="form-select form-select-lg">
                                    <option value="venta">🏪 Venta</option>
                                    <option value="insumo">🧴 Insumo</option>
                                    <option value="mixto">🔄 Mixto</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-muted small">Código de Barras</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" wire:model="codigo_barras" class="form-control" placeholder="Escanear...">
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Stock Mínimo (Alerta)</label>
                                <input type="number" wire:model="stock_minimo" class="form-control">
                            </div>
                        </div>

                        {{-- SECCIÓN 2: PRECIOS Y COSTOS --}}
                        <div class="row g-3 mb-4 bg-light p-3 rounded mx-0">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Costo de Compra (S/)</label>
                                <div class="input-group">
                                    <span class="input-group-text border-0">S/</span>
                                    <input type="number" step="0.01" wire:model="costo_compra" class="form-control border-0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">Precio de Venta (S/)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-success text-white border-0">S/</span>
                                    <input type="number" step="0.01" wire:model="precio_venta" 
                                        class="form-control border-success text-success fw-bold @error('precio_venta') is-invalid @enderror" 
                                        @if($tipo == 'insumo') disabled @endif>
                                </div>
                                @if($tipo == 'insumo') <small class="text-muted mt-1 d-block"><i class="bi bi-info-circle"></i> No aplica para insumos puros</small> @endif
                                @error('precio_venta') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                        </div>

                        {{-- SECCIÓN 3: INVENTARIO INICIAL (SOLO LECTURA SI ES EDICIÓN) --}}
                        <h6 class="text-uppercase text-muted small fw-bold mb-3 border-bottom pb-2 d-flex justify-content-between">
                            Inventario Inicial
                            @if($producto_id)
                                <span class="badge bg-warning text-dark"><i class="bi bi-lock-fill"></i> Modo Edición: Stock Bloqueado</span>
                            @endif
                        </h6>

                        @if($producto_id)
                            <div class="alert alert-info d-flex align-items-center py-2">
                                <i class="bi bi-info-circle-fill fs-4 me-3"></i>
                                <div>
                                    Para ajustar cantidades, utiliza el módulo de 
                                    <a href="{{ route('admin.inventario') }}" class="fw-bold text-decoration-none">Movimientos de Inventario</a>.
                                    Aquí solo puedes editar datos maestros.
                                </div>
                            </div>
                        @endif

                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Stock Venta (Vitrina)</label>
                                <input type="number" wire:model="stock_actual" class="form-control" 
                                    @if($producto_id) disabled @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted small">Stock Insumo (Interno)</label>
                                <input type="number" wire:model="stock_insumo" class="form-control" 
                                    @if($producto_id) disabled @endif>
                            </div>
                        </div>

                        {{-- SECCIÓN 4: SUNAT (Ocultable o al final) --}}
                        <div class="collapse" id="sunatCollapse">
                            </div>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small">Unidad SUNAT</label>
                                <select wire:model="id_unidad" class="form-select form-select-sm">
                                    @foreach($unidades as $u)
                                        <option value="{{ $u->id }}">{{ $u->codigo }} - {{ $u->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Afectación IGV</label>
                                <select wire:model="id_afectacion" class="form-select form-select-sm">
                                    @foreach($afectaciones as $a)
                                        <option value="{{ $a->id }}">{{ $a->codigo }} - {{ $a->descripcion }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                    </form>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button wire:click="closeModal()" type="button" class="btn btn-light text-secondary fw-bold px-4">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary fw-bold px-4 shadow-sm">
                        <i class="bi bi-save me-2"></i> Guardar Producto
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>