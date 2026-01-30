<div>
    {{-- MENSJAE DE ÉXITO --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" 
             style="background-color: #d1e7dd; color: #0f5132;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold mb-0 text-uppercase" style="color: var(--belen-dark); letter-spacing: 1px;">
                    <i class="bi bi-tags-fill me-2"></i> Catálogo de Productos
                </h3>
                
                <div class="card-tools">
                    <div class="d-flex gap-2">
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input wire:model.live="search" type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar producto...">
                        </div>
                        <button wire:click="create()" class="btn btn-primary shadow-sm text-dark fw-bold">
                            <i class="bi bi-plus-lg"></i> Nuevo
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: var(--belen-dark); color: white;">
                        <tr>
                            <th class="py-3 ps-4">Producto / Código</th>
                            <th class="py-3 text-center">Tipo</th>
                            <th class="py-3 text-center">Stock Venta</th>
                            <th class="py-3 text-center">Stock Interno</th>
                            <th class="py-3 text-end">Precio</th>
                            <th class="py-3 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($productos as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $item->nombre }}</div>
                                    @if($item->codigo_barras)
                                        <small class="text-muted"><i class="bi bi-upc"></i> {{ $item->codigo_barras }}</small>
                                    @else
                                        <small class="text-muted fst-italic border px-1 rounded" style="font-size: 0.7rem;">S/C</small>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->tipo == 'venta')
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Venta</span>
                                    @elseif($item->tipo == 'insumo')
                                        <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">Insumo</span>
                                    @else
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Mixto</span>
                                    @endif
                                </td>
                                
                                {{-- STOCK VENTA --}}
                                <td class="text-center">
                                    @if($item->tipo == 'venta' || $item->tipo == 'mixto')
                                        <span class="fw-bold {{ $item->stock_actual <= $item->stock_minimo ? 'text-danger' : 'text-dark' }}">
                                            {{ $item->stock_actual }}
                                        </span>
                                        @if($item->stock_actual <= $item->stock_minimo)
                                            <i class="bi bi-exclamation-circle-fill text-danger small" title="Stock Bajo"></i>
                                        @endif
                                    @else
                                        <span class="text-muted opacity-25">-</span>
                                    @endif
                                </td>

                                {{-- STOCK INSUMO --}}
                                <td class="text-center">
                                    @if($item->tipo == 'insumo' || $item->tipo == 'mixto')
                                        <span class="fw-bold text-secondary">{{ $item->stock_insumo }}</span>
                                    @else
                                        <span class="text-muted opacity-25">-</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    @if($item->tipo != 'insumo')
                                        <div class="fw-bold text-success">S/ {{ number_format($item->precio_venta, 2) }}</div>
                                    @else
                                        <small class="text-muted">N/A</small>
                                    @endif
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Costo: {{ number_format($item->costo_compra, 2) }}</small>
                                </td>

                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm me-1"><i class="bi bi-pencil-square text-primary"></i></button>
                                    <button wire:confirm="¿Eliminar producto?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm"><i class="bi bi-trash text-danger"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-box-seam fs-1 d-block mb-2"></i>
                                        No hay productos registrados.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">{{ $productos->links() }}</div>
    </div>

    {{-- MODAL --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $producto_id ? 'Editar Producto' : 'Nuevo Producto' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form>
                        <div class="row g-3">
                            <div class="col-12"><label class="form-label fw-bold text-secondary small text-uppercase border-bottom w-100 pb-1">Datos Principales</label></div>

                            <div class="col-md-8">
                                <label class="form-label">Nombre del Producto <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre" class="form-control shadow-sm @error('nombre') is-invalid @enderror" placeholder="Ej: Shampoo Keratina">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Tipo de Uso</label>
                                <select wire:model.live="tipo" class="form-select shadow-sm bg-white">
                                    <option value="venta">🏪 Solo Venta</option>
                                    <option value="insumo">🧴 Solo Insumo</option>
                                    <option value="mixto">🔄 Mixto (Ambos)</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Código de Barras</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-upc-scan"></i></span>
                                    <input type="text" wire:model="codigo_barras" class="form-control border-start-0" placeholder="Escanear...">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Mínimo (Alerta)</label>
                                <input type="number" wire:model="stock_minimo" class="form-control shadow-sm">
                            </div>

                            {{-- PRECIOS --}}
                            <div class="col-12 mt-3"><label class="form-label fw-bold text-secondary small text-uppercase border-bottom w-100 pb-1">Costos y Precios</label></div>

                            <div class="col-md-6">
                                <label class="form-label">Costo Compra (S/)</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white border-end-0">S/.</span>
                                    <input type="number" step="0.01" wire:model="costo_compra" class="form-control border-start-0">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-success fw-bold">Precio Venta (S/)</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-success text-white border-end-0 fw-bold">S/.</span>
                                    <input type="number" step="0.01" wire:model="precio_venta" class="form-control border-start-0 text-success fw-bold" @if($tipo == 'insumo') disabled @endif>
                                </div>
                            </div>

                            {{-- INVENTARIO --}}
                            <div class="col-12 mt-3">
                                <label class="form-label fw-bold text-secondary small text-uppercase border-bottom w-100 pb-1 d-flex justify-content-between">
                                    <span>Inventario Inicial</span>
                                    @if($producto_id) <span class="badge bg-warning text-dark border border-dark"><i class="bi bi-lock-fill"></i> Bloqueado por Edición</span> @endif
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Stock Venta (Vitrina)</label>
                                <input type="number" wire:model="stock_actual" class="form-control shadow-sm" @if($producto_id || $tipo == 'insumo') disabled @endif>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Stock Insumo (Almacén)</label>
                                <input type="number" wire:model="stock_insumo" class="form-control shadow-sm" @if($producto_id || $tipo == 'venta') disabled @endif>
                            </div>

                            {{-- SUNAT --}}
                            <div class="col-12 mt-3"><label class="form-label fw-bold text-secondary small text-uppercase border-bottom w-100 pb-1">Configuración SUNAT</label></div>
                            
                            <div class="col-md-6">
                                <label class="form-label small">Unidad de Medida</label>
                                <select wire:model="id_unidad" class="form-select form-select-sm bg-white shadow-sm">
                                    @foreach($unidades as $u) <option value="{{ $u->id }}">{{ $u->codigo }} - {{ Str::limit($u->descripcion, 30) }}</option> @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small">Afectación IGV</label>
                                <select wire:model="id_afectacion" class="form-select form-select-sm bg-white shadow-sm">
                                    @foreach($afectaciones as $a) <option value="{{ $a->id }}">{{ $a->codigo }} - {{ Str::limit($a->descripcion, 30) }}</option> @endforeach
                                </select>
                            </div>

                        </div>
                    </form>
                </div>
                
                <div class="modal-footer bg-white py-3">
                    <button wire:click="closeModal()" type="button" class="btn btn-light border">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary px-4 shadow-sm text-dark fw-bold"><i class="bi bi-save me-1"></i> Guardar Producto</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>