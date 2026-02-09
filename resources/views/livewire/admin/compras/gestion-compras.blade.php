<div>
    {{-- HEADER CON TÍTULO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-cart-plus me-2"></i> Ingreso de Mercadería</h4>
            <small class="text-muted">Registra compras y actualiza el inventario</small>
        </div>
    </div>

    {{-- ALERTAS --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold mb-3">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }} 
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        
        {{-- COLUMNA IZQUIERDA: CATÁLOGO --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
                    <h5 class="card-title fw-bold text-dark mb-0">
                        <i class="bi bi-box-seam me-2"></i> Catálogo de Productos
                    </h5>
                </div>
                <div class="card-body bg-light">
                    {{-- Buscador --}}
                    <div class="input-group mb-3 shadow-sm">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" wire:model.live.debounce.500ms="searchProducto" class="form-control border-start-0" placeholder="Buscar o escanear código...">
                        <span class="input-group-text bg-white text-muted"><i class="bi bi-upc-scan"></i></span>
                    </div>
                    
                    {{-- Lista de Resultados --}}
                    <div class="list-group shadow-sm">
                        @forelse($productos as $p)
                            <button wire:click="addProducto({{ $p->id }})" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-3 border-0 mb-1 rounded">
                                <div>
                                    <div class="fw-bold text-dark">{{ $p->nombre }}</div>
                                    <small class="text-muted d-block">
                                        Stock: 
                                        @if($p->tipo == 'insumo') {{ $p->stock_insumo }} (Interno)
                                        @else {{ $p->stock_actual }} (Venta)
                                        @endif
                                    </small>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-primary rounded-pill mb-1">S/ {{ number_format($p->costo_compra, 2) }}</span>
                                    <br>
                                    <i class="bi bi-plus-circle-fill text-success fs-5"></i>
                                </div>
                            </button>
                        @empty
                            @if(strlen($searchProducto) > 1)
                                <div class="text-center py-4 text-muted">
                                    <i class="bi bi-emoji-frown fs-1"></i><br>No encontrado
                                </div>
                            @else
                                <div class="text-center py-4 text-muted small">
                                    <i class="bi bi-search fs-1 opacity-25"></i><br>Escribe para buscar...
                                </div>
                            @endif
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: DOCUMENTO DE INGRESO --}}
        <div class="col-md-8">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
                    <h5 class="card-title fw-bold text-dark mb-0">
                        <i class="bi bi-file-earmark-text me-2"></i> Documento de Compra
                    </h5>
                </div>
                <div class="card-body p-4">
                    
                    {{-- CABECERA DEL DOCUMENTO --}}
                    <div class="row g-3 mb-4 p-3 bg-light rounded border">
                        <div class="col-md-3">
                            <label class="form-label small fw-bold text-secondary">FECHA</label>
                            <input type="date" wire:model="fecha_compra" class="form-control form-control-sm border-0 shadow-sm">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold text-secondary">PROVEEDOR</label>
                            <select wire:model="id_proveedor" class="form-select form-select-sm border-0 shadow-sm">
                                <option value="">-- Público / Sin Proveedor --</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-secondary">DOC. REFERENCIA</label>
                            <input type="text" wire:model="numero_documento" class="form-control form-control-sm border-0 shadow-sm" placeholder="Ej: F001-456">
                        </div>
                    </div>

                    {{-- TABLA DE DETALLE --}}
                    <div class="table-responsive mb-4">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: var(--belen-dark); color: white;">
                                <tr>
                                    <th class="ps-3 py-3">Producto</th>
                                    <th width="15%" class="text-center py-3">Cant.</th>
                                    <th width="20%" class="text-end py-3">Costo Unit.</th>
                                    <th width="20%" class="text-end py-3">Subtotal</th>
                                    <th width="5%" class="py-3"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cart as $index => $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-bold text-dark">{{ $item['nombre'] }}</div>
                                            @if($item['tipo'] == 'insumo')
                                                <span class="badge bg-warning text-dark border border-warning" style="font-size: 0.65rem;">
                                                    <i class="bi bi-bucket-fill"></i> Destino: Interno
                                                </span>
                                            @else
                                                <span class="badge bg-success bg-opacity-10 text-success border border-success" style="font-size: 0.65rem;">
                                                    <i class="bi bi-shop"></i> Destino: Venta
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <input type="number" 
                                                wire:change="updateItem({{ $index }}, 'cantidad', $event.target.value)"
                                                value="{{ $item['cantidad'] }}" 
                                                class="form-control form-control-sm text-center fw-bold bg-light border-0">
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text border-0 bg-transparent text-muted">S/</span>
                                                <input type="number" step="0.01" 
                                                    wire:change="updateItem({{ $index }}, 'costo', $event.target.value)"
                                                    value="{{ $item['costo'] }}" 
                                                    class="form-control form-control-sm text-end fw-bold bg-light border-0">
                                            </div>
                                        </td>
                                        <td class="text-end fw-bold text-dark fs-6">
                                            S/ {{ number_format($item['subtotal'], 2) }}
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="removeItem({{ $index }})" class="btn btn-sm text-danger hover-bg-danger">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <i class="bi bi-cart-x fs-1 opacity-25"></i>
                                            <p class="mt-2 small">El carrito de ingreso está vacío.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if(!empty($cart))
                                <tfoot class="border-top border-2">
                                    <tr>
                                        <td colspan="3" class="text-end fw-bold text-dark pt-3 pe-3">TOTAL A PAGAR:</td>
                                        <td class="text-end fw-bold text-success fs-4 pt-3">S/ {{ number_format($total, 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>

                    {{-- BOTÓN GUARDAR --}}
                    <div class="d-grid">
                        <button wire:confirm="¿Confirmar ingreso? Se actualizará el stock de los productos." 
                                wire:click="guardarCompra" 
                                class="btn btn-success btn-lg shadow fw-bold text-white" 
                                @if(empty($cart)) disabled @endif>
                            <i class="bi bi-check-circle-fill me-2"></i> Registrar Compra
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>