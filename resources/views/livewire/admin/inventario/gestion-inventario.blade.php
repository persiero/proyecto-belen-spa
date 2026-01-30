<div>
    <div class="row">
        <div class="col-12 mb-4">
            
            {{-- HEADER Y NAVEGACIÓN --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'stock' ? 'active shadow-sm' : 'text-muted' }}" 
                               href="#" wire:click.prevent="cambiarTab('stock')" style="border-radius: 8px;">
                                <i class="bi bi-box-seam me-2"></i> Stock Actual
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'kardex' ? 'active shadow-sm' : 'text-muted' }}" 
                               href="#" wire:click.prevent="cambiarTab('kardex')" style="border-radius: 8px;">
                                <i class="bi bi-clock-history me-2"></i> Kardex (Movimientos)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'ajuste' ? 'active shadow-sm' : 'text-muted' }}" 
                               href="#" wire:click.prevent="cambiarTab('ajuste')" style="border-radius: 8px;">
                                <i class="bi bi-sliders me-2"></i> Ajustes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'transferencia' ? 'active shadow-sm' : 'text-muted' }}" 
                               href="#" wire:click.prevent="cambiarTab('transferencia')" style="border-radius: 8px;">
                                <i class="bi bi-arrow-left-right me-2"></i> Transferencias
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold">
                    <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }} 
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- 1. TABLA STOCK --}}
            @if($tab == 'stock')
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes me-2"></i> Existencias</h5>
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live="search" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead style="background-color: var(--belen-dark); color: white;">
                            <tr>
                                <th class="ps-4 py-3">Producto</th>
                                <th class="py-3 text-center">Tipo</th>
                                <th class="py-3 text-center bg-light text-dark border-start">Vitrina (Venta)</th>
                                <th class="py-3 text-center bg-light text-dark border-start">Interno (Insumo)</th>
                                <th class="pe-4 py-3 text-end">Total Global</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listaStock as $p)
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $p->nombre }}</div>
                                        <small class="text-muted">{{ $p->codigo_barras ?? '-' }}</small>
                                    </td>
                                    <td class="text-center">
                                        @if($p->tipo == 'venta') <span class="badge bg-success bg-opacity-10 text-success border border-success">Venta</span>
                                        @elseif($p->tipo == 'insumo') <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">Insumo</span>
                                        @else <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Mixto</span> @endif
                                    </td>
                                    
                                    {{-- Vitrina --}}
                                    <td class="text-center border-start">
                                        @if($p->tipo != 'insumo')
                                            <span class="fw-bold {{ $p->stock_actual <= $p->stock_minimo ? 'text-danger' : 'text-success' }}">
                                                {{ $p->stock_actual }}
                                            </span>
                                            @if($p->stock_actual <= $p->stock_minimo) <i class="bi bi-exclamation-circle-fill text-danger small"></i> @endif
                                        @else <span class="text-muted opacity-25">-</span> @endif
                                    </td>

                                    {{-- Insumo --}}
                                    <td class="text-center border-start">
                                        @if($p->tipo != 'venta')
                                            <span class="fw-bold {{ $p->stock_insumo <= $p->stock_minimo ? 'text-danger' : 'text-warning' }} text-dark">
                                                {{ $p->stock_insumo }}
                                            </span>
                                        @else <span class="text-muted opacity-25">-</span> @endif
                                    </td>

                                    <td class="pe-4 text-end">
                                        <span class="fw-bold text-dark fs-5">{{ $p->stock_actual + $p->stock_insumo }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">No se encontraron productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 py-3">{{ $listaStock->links() }}</div>
            </div>
            @endif

            {{-- 2. TABLA KARDEX --}}
            @if($tab == 'kardex')
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-list-columns-reverse me-2"></i> Historial de Movimientos</h5>
                        <div class="input-group" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live="search" class="form-control bg-light border-start-0 ps-0" placeholder="Filtrar por producto...">
                        </div>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4 py-3">Fecha / Hora</th>
                                <th class="py-3">Tipo Movimiento</th>
                                <th class="py-3">Producto</th>
                                <th class="py-3">Motivo / Ref.</th>
                                <th class="pe-4 py-3 text-end">Cantidad</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($movimientos as $mov)
                                @php
                                    $color = 'secondary'; $icon = 'bi-circle';
                                    if($mov->cantidad > 0) { $color = 'success'; $icon = 'bi-arrow-down-circle-fill'; } // Entrada
                                    elseif($mov->cantidad < 0) { $color = 'danger'; $icon = 'bi-arrow-up-circle-fill'; } // Salida
                                    else { $color = 'info'; $icon = 'bi-arrow-left-right'; } // Transferencia (neutro)
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $mov->fecha->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $mov->fecha->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }}">
                                            @if($mov->tipo == 'entrada') Compra / Entrada
                                            @elseif($mov->tipo == 'salida_venta') Venta
                                            @elseif($mov->tipo == 'salida_insumo') Consumo Interno
                                            @else Ajuste / Transf.
                                            @endif
                                        </span>
                                    </td>
                                    <td class="fw-bold text-secondary">{{ $mov->producto->nombre ?? 'Eliminado' }}</td>
                                    <td><small class="text-muted">{{ $mov->motivo ?? $mov->referencia }}</small></td>
                                    <td class="pe-4 text-end">
                                        <span class="fs-5 fw-bold text-{{ $color }}">
                                            @if($mov->cantidad > 0) +{{ $mov->cantidad }}
                                            @elseif($mov->cantidad < 0) {{ $mov->cantidad }}
                                            @else 0
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center py-5 text-muted">No hay movimientos recientes.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 py-3">{{ $movimientos->links() }}</div>
            </div>
            @endif

            {{-- 3. AJUSTES MANUALES --}}
            @if($tab == 'ajuste')
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-header bg-white py-3 text-center border-bottom-0">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-sliders text-warning me-2"></i> Ajuste Manual de Inventario</h5>
                            <small class="text-muted">Uso para: Mermas, regalos, corrección de stock, etc.</small>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <form wire:submit.prevent="guardarAjuste">
                                
                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-secondary">SELECCIONAR PRODUCTO</label>
                                    <select wire:model.live="producto_id" class="form-select form-select-lg shadow-sm">
                                        <option value="">-- Buscar --</option>
                                        @foreach($productos as $p) <option value="{{ $p->id }}">{{ $p->nombre }}</option> @endforeach
                                    </select>
                                    @error('producto_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- INFO CARD STOCK --}}
                                @if($producto_seleccionado)
                                    <div class="d-flex justify-content-between bg-light rounded p-3 mb-3 border">
                                        <div class="text-center w-50 border-end">
                                            <small class="text-success fw-bold text-uppercase">Vitrina</small><br>
                                            <span class="fs-4 fw-bold">{{ $producto_seleccionado->stock_actual }}</span>
                                        </div>
                                        <div class="text-center w-50">
                                            <small class="text-warning fw-bold text-dark text-uppercase">Insumo</small><br>
                                            <span class="fs-4 fw-bold">{{ $producto_seleccionado->stock_insumo }}</span>
                                        </div>
                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-secondary">TIPO DE ACCIÓN</label>
                                    <select wire:model="tipo_movimiento" class="form-select shadow-sm">
                                        <option value="salida_insumo">📉 Consumo Interno (Resta de Insumo)</option>
                                        <option value="ajuste_entrada">📈 Entrada Manual (Suma a Venta)</option>
                                        <option value="ajuste_salida">🗑️ Salida Manual (Resta de Venta)</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-3">
                                        <label class="form-label fw-bold small text-secondary">CANTIDAD</label>
                                        <input type="number" wire:model="cantidad" class="form-control text-center fw-bold shadow-sm" min="1">
                                    </div>
                                    <div class="col-8 mb-3">
                                        <label class="form-label fw-bold small text-secondary">MOTIVO</label>
                                        <input type="text" wire:model="motivo" class="form-control shadow-sm" placeholder="Ej: Vencimiento, Rotura...">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning w-100 fw-bold py-2 shadow-sm text-dark">
                                    <i class="bi bi-save me-1"></i> Guardar Ajuste
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- 4. TRANSFERENCIAS --}}
            @if($tab == 'transferencia')
            <div class="row justify-content-center">
                <div class="col-md-7">
                    <div class="card shadow-lg border-0 rounded-4">
                        <div class="card-header bg-gradient bg-info text-white py-3 text-center border-bottom-0">
                            <h5 class="fw-bold mb-0"><i class="bi bi-arrow-left-right me-2"></i> Transferencia Interna</h5>
                            <small class="text-white text-opacity-75">Mover productos entre Vitrina y Almacén Interno</small>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <form wire:submit.prevent="guardarTransferencia">
                                <div class="mb-4">
                                    <label class="form-label fw-bold small text-secondary">1. PRODUCTO A MOVER</label>
                                    <select wire:model.live="prod_transferencia_id" class="form-select form-select-lg shadow-sm">
                                        <option value="">-- Buscar Producto --</option>
                                        @foreach($productos as $p) <option value="{{ $p->id }}">{{ $p->nombre }}</option> @endforeach
                                    </select>
                                    @error('prod_transferencia_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                @if($producto_seleccionado)
                                    <div class="row align-items-center mb-4 g-2">
                                        {{-- ORIGEN --}}
                                        <div class="col-5">
                                            <div class="p-3 border rounded text-center {{ $origen=='venta' ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-10 border-warning' }}">
                                                <small class="text-muted fw-bold">DESDE</small>
                                                <select wire:model.live="origen" class="form-select form-select-sm mt-1 mb-2 fw-bold">
                                                    <option value="venta">Vitrina ({{ $producto_seleccionado->stock_actual }})</option>
                                                    <option value="insumo">Insumo ({{ $producto_seleccionado->stock_insumo }})</option>
                                                </select>
                                            </div>
                                        </div>
                                        {{-- FLECHA --}}
                                        <div class="col-2 text-center">
                                            <i class="bi bi-arrow-right fs-1 text-muted opacity-25"></i>
                                        </div>
                                        {{-- DESTINO --}}
                                        <div class="col-5">
                                            <div class="p-3 border rounded text-center {{ $destino=='venta' ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-10 border-warning' }}">
                                                <small class="text-muted fw-bold">HACIA</small>
                                                <select wire:model.live="destino" class="form-select form-select-sm mt-1 mb-2 fw-bold">
                                                    <option value="insumo">Insumo</option>
                                                    <option value="venta">Vitrina</option>
                                                </select>
                                            </div>
                                        </div>
                                        @if($origen == $destino)
                                            <div class="col-12 text-center text-danger small mt-1">El destino debe ser diferente al origen.</div>
                                        @endif
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-4 mb-3">
                                        <label class="form-label fw-bold small text-secondary">CANTIDAD</label>
                                        <input type="number" wire:model="cant_transferencia" class="form-control text-center fw-bold shadow-sm" min="1">
                                    </div>
                                    <div class="col-8 mb-3">
                                        <label class="form-label fw-bold small text-secondary">OBSERVACIÓN (OPCIONAL)</label>
                                        <input type="text" wire:model="motivo_transferencia" class="form-control shadow-sm" placeholder="Ej: Reposición diaria...">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info text-white w-100 fw-bold py-2 shadow-sm" @if(!$producto_seleccionado || $origen == $destino) disabled @endif>
                                    <i class="bi bi-arrow-left-right me-1"></i> Confirmar Transferencia
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>