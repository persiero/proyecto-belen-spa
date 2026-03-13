<div>
    {{-- HEADER CON TÍTULO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-boxes me-2"></i> Gestión de Inventario</h4>
            <small class="text-muted">Control de stock, movimientos y transferencias</small>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">

            {{-- NAVEGACIÓN MEJORADA --}}
            <div class="card shadow-sm border-0">
                <div class="card-body p-2">
                    <ul class="nav nav-pills nav-fill gap-2 p-1 bg-light rounded-3" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'stock' ? 'active shadow-sm' : 'text-muted' }}"
                               href="#" wire:click.prevent="cambiarTab('stock')" style="border-radius: 8px;">
                                <i class="bi bi-box-seam me-1"></i> Stock Actual
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'kardex' ? 'active shadow-sm' : 'text-muted' }}"
                               href="#" wire:click.prevent="cambiarTab('kardex')" style="border-radius: 8px;">
                                <i class="bi bi-clock-history me-1"></i> Kardex(Movimientos)
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'ajuste' ? 'active shadow-sm' : 'text-muted' }}"
                               href="#" wire:click.prevent="cambiarTab('ajuste')" style="border-radius: 8px;">
                                <i class="bi bi-sliders me-1"></i> Ajustes de Inventario
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link fw-bold {{ $tab == 'transferencia' ? 'active shadow-sm' : 'text-muted' }}"
                               href="#" wire:click.prevent="cambiarTab('transferencia')" style="border-radius: 8px;">
                                <i class="bi bi-arrow-left-right me-1"></i> Transferencias
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
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-boxes me-2"></i> Existencias Actuales</h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar producto...">
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
                                @php
                                    // LÓGICA DINÁMICA: Determinar tipo según dónde está el stock
                                    if ($p->stock_actual > 0 && $p->stock_insumo == 0) {
                                        $tipoDinamico = 'venta';
                                    } elseif ($p->stock_insumo > 0 && $p->stock_actual == 0) {
                                        $tipoDinamico = 'insumo';
                                    } elseif ($p->stock_actual > 0 && $p->stock_insumo > 0) {
                                        $tipoDinamico = 'mixto';
                                    } else {
                                        // Si el stock en ambos es 0, mostramos su tipo original
                                        $tipoDinamico = $p->tipo;
                                    }
                                @endphp

                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $p->nombre }}</div>
                                        <small class="text-muted">{{ $p->codigo_barras ?? '-' }}</small>
                                    </td>

                                    {{-- TIPO DINÁMICO --}}
                                    <td class="text-center">
                                        @if($tipoDinamico == 'venta')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success">Venta</span>
                                        @elseif($tipoDinamico == 'insumo')
                                            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">Insumo</span>
                                        @else
                                            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Mixto</span>
                                        @endif
                                    </td>

                                    {{-- Vitrina (Venta) --}}
                                    <td class="text-center border-start">
                                        @if($p->stock_actual > 0 || $tipoDinamico == 'venta' || $tipoDinamico == 'mixto')
                                            <span class="fw-bold {{ $p->stock_actual <= $p->stock_minimo ? 'text-danger' : 'text-success' }}">
                                                {{ $p->stock_actual }}
                                            </span>
                                            @if($p->stock_actual <= $p->stock_minimo && $p->stock_actual > 0)
                                                <i class="bi bi-exclamation-circle-fill text-danger small"></i>
                                            @endif
                                        @else
                                            <span class="text-muted opacity-25">-</span>
                                        @endif
                                    </td>

                                    {{-- Interno (Insumo) --}}
                                    <td class="text-center border-start">
                                        @if($p->stock_insumo > 0 || $tipoDinamico == 'insumo' || $tipoDinamico == 'mixto')
                                            <span class="fw-bold {{ $p->stock_insumo <= $p->stock_minimo ? 'text-danger' : 'text-warning' }} text-dark">
                                                {{ $p->stock_insumo }}
                                            </span>
                                            @if($p->stock_insumo <= $p->stock_minimo && $p->stock_insumo > 0)
                                                <i class="bi bi-exclamation-circle-fill text-danger small"></i>
                                            @endif
                                        @else
                                            <span class="text-muted opacity-25">-</span>
                                        @endif
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
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" wire:model.live.debounce.300ms="search" class="form-control bg-light border-start-0 ps-0" placeholder="Filtrar por producto...">
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

                                    // NUEVA LÓGICA: Evaluamos la "referencia" o el tipo "ajuste"
                                    if($mov->referencia == 'TRANSFERENCIA' || $mov->tipo == 'ajuste') {
                                        $color = 'purple';
                                        $icon = 'bi-arrow-left-right';
                                    } elseif($mov->cantidad > 0) {
                                        $color = 'success';
                                        $icon = 'bi-arrow-down-circle-fill';
                                    } elseif($mov->cantidad < 0) {
                                        $color = 'danger';
                                        $icon = 'bi-arrow-up-circle-fill';
                                    }
                                @endphp
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $mov->fecha->format('d/m/Y') }}</div>
                                        <small class="text-muted">{{ $mov->fecha->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @if($color == 'purple')
                                            <span class="badge bg-white text-purple border border-purple">
                                                <i class="bi bi-arrow-left-right me-1"></i> Transf. / Ajuste Interno
                                            </span>
                                        @else
                                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} border border-{{ $color }}">
                                                @if($mov->tipo == 'entrada' || $mov->tipo == 'ajuste_entrada') <i class="bi bi-box-arrow-in-down me-1"></i> Entrada
                                                @elseif($mov->tipo == 'salida_venta' || $mov->tipo == 'ajuste_salida') <i class="bi bi-box-arrow-up me-1"></i> Salida / Venta
                                                @elseif($mov->tipo == 'salida_insumo') <i class="bi bi-scissors me-1"></i> Consumo Interno
                                                @else {{ $mov->tipo }} @endif
                                            </span>
                                        @endif
                                    </td>
                                    <td class="fw-bold text-secondary">{{ $mov->producto->nombre ?? 'Eliminado' }}</td>
                                    <td><small class="text-muted">{{ $mov->motivo ?? $mov->referencia }}</small></td>
                                    <td class="pe-4 text-end">
                                        <span class="fs-5 fw-bold text-{{ $color }}">
                                            @if($mov->referencia == 'TRANSFERENCIA')
                                                {{ abs($mov->cantidad) }}
                                            @elseif($mov->cantidad > 0)
                                                +{{ $mov->cantidad }}
                                            @else
                                                {{ $mov->cantidad }}
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
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-sliders text-warning me-2"></i> Registro de Movimientos de Inventario</h5>
                            <small class="text-muted">Consumo interno, vencimientos, regalos, correcciones y entradas manuales</small>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <form wire:submit.prevent="guardarAjuste">

                                <div class="mb-3 position-relative">
                                    <label class="form-label fw-bold small text-secondary">BUSCAR PRODUCTO</label>

                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
                                        <input type="text" wire:model.live.debounce.300ms="buscar_producto_ajuste"
                                            class="form-control border-start-0 ps-0"
                                            placeholder="Escribe el nombre o código de barras..."
                                            autocomplete="off">

                                        {{-- Botón para limpiar (Solo se muestra si hay un producto seleccionado) --}}
                                        @if($producto_id)
                                            <button type="button" class="btn btn-outline-secondary border-start-0 border-top border-bottom" wire:click="limpiarProductoAjuste">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Lista de resultados superpuesta --}}
                                    @if(!empty($productos_encontrados_ajuste))
                                        <div class="position-absolute w-100 bg-white border rounded shadow-lg mt-1" style="z-index: 1000; max-height: 250px; overflow-y: auto;">
                                            <div class="list-group list-group-flush">
                                                @foreach($productos_encontrados_ajuste as $p)
                                                    <button type="button" class="list-group-item list-group-item-action text-start p-3" wire:click="seleccionarProductoAjuste({{ $p->id }})">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="fw-bold d-block text-dark">{{ $p->nombre }}</span>
                                                                <small class="text-muted"><i class="bi bi-upc-scan me-1"></i>{{ $p->codigo_barras ?? 'Sin código' }}</small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success">V: {{ $p->stock_actual }}</span>
                                                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">I: {{ $p->stock_insumo }}</span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @error('producto_id') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>

                                {{-- INFO CARD STOCK CON HIGHLIGHT DINÁMICO --}}
                                @if($producto_seleccionado)
                                    <div class="d-flex justify-content-between bg-light rounded p-2 mb-3 border">

                                        {{-- Caja Ventas --}}
                                        <div class="text-center w-50 py-2 {{ in_array($tipo_movimiento, ['ajuste_entrada', 'ajuste_salida']) ? 'bg-white shadow-sm border-success border-bottom border-3 rounded' : 'opacity-50' }}" style="transition: all 0.3s ease;">
                                            <small class="text-success fw-bold text-uppercase">Ventas</small><br>
                                            <span class="fs-4 fw-bold">{{ $producto_seleccionado->stock_actual }}</span>
                                        </div>

                                        {{-- Caja Insumos --}}
                                        <div class="text-center w-50 py-2 border-start {{ $tipo_movimiento == 'salida_insumo' ? 'bg-white shadow-sm border-warning border-bottom border-3 rounded' : 'opacity-50' }}" style="transition: all 0.3s ease;">
                                            <small class="text-purple fw-bold text-uppercase">Insumos (Uso Interno)</small><br>
                                            <span class="fs-4 fw-bold">{{ $producto_seleccionado->stock_insumo }}</span>
                                        </div>

                                    </div>
                                @endif

                                <div class="mb-3">
                                    <label class="form-label fw-bold small text-secondary">TIPO DE MOVIMIENTO</label>

                                    {{-- Agregamos .live para que actualice la vista al instante --}}
                                    <select wire:model.live="tipo_movimiento" class="form-select shadow-sm" style="font-size: 1.05rem;">
                                        <option value="salida_insumo" style="background-color: #fff3cd; font-weight: bold;">✂️ CONSUMO INTERNO (Descuenta de Insumos)</option>
                                        <option value="ajuste_entrada">📥 Entrada Manual (Suma a Ventas)</option>
                                        <option value="ajuste_salida">🗑️ Salida por Merma/Rotura (Descuenta de Ventas)</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-4 mb-3">
                                        <label class="form-label fw-bold small text-secondary">CANTIDAD</label>
                                        {{-- El color del número cambia a rojo o verde según si es salida o entrada --}}
                                        <input type="number" wire:model="cantidad" class="form-control text-center fw-bold shadow-sm fs-5 text-{{ $tipo_movimiento == 'ajuste_entrada' ? 'success' : 'danger' }}" min="1">
                                    </div>
                                    <div class="col-8 mb-3">
                                        <label class="form-label fw-bold small text-secondary">DETALLE O MOTIVO</label>

                                        {{-- Lógica para el Placeholder Dinámico --}}
                                        @php
                                            $placeholder = 'Ej: Vencimiento, Frasco roto...';
                                            if($tipo_movimiento == 'salida_insumo') $placeholder = 'Ej: Uso en Balayage cliente, lavado diario...';
                                            if($tipo_movimiento == 'ajuste_entrada') $placeholder = 'Ej: Sobrante en conteo, muestra gratis...';
                                        @endphp

                                        <input type="text" wire:model="motivo" class="form-control shadow-sm" placeholder="{{ $placeholder }}" required>
                                    </div>
                                </div>

                                {{-- Botón dinámico --}}
                                <button type="submit" class="btn {{ $tipo_movimiento == 'ajuste_entrada' ? 'btn-success text-white' : 'btn-warning text-dark' }} w-100 fw-bold py-3 shadow-sm">
                                    <i class="bi bi-save me-1"></i> Confirmar Operación
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
                        <div class="card-header bg-white py-3 text-center border-bottom-0">
                            <h5 class="fw-bold text-dark mb-0"><i class="bi bi-arrow-left-right text-purple me-2"></i> Transferencia Interna</h5>
                            <small class="text-muted">Mover productos entre Almacén Ventas y Almacén Interno</small>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <form wire:submit.prevent="guardarTransferencia">
                                <div class="mb-4 position-relative">
                                    <label class="form-label fw-bold small text-secondary">1. BUSCAR PRODUCTO A MOVER</label>

                                    <div class="input-group input-group-lg shadow-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-purple"></i></span>
                                        <input type="text" wire:model.live.debounce.300ms="buscar_producto_transferencia"
                                            class="form-control border-start-0 ps-0"
                                            placeholder="Escribe el nombre o código de barras..."
                                            autocomplete="off">

                                        @if($prod_transferencia_id)
                                            <button type="button" class="btn btn-outline-secondary border-start-0 border-top border-bottom" wire:click="limpiarProductoTransferencia">
                                                <i class="bi bi-x-lg"></i>
                                            </button>
                                        @endif
                                    </div>

                                    {{-- Lista de resultados superpuesta --}}
                                    @if(!empty($productos_encontrados_transferencia))
                                        <div class="position-absolute w-100 bg-white border rounded shadow-lg mt-1" style="z-index: 1000; max-height: 250px; overflow-y: auto;">
                                            <div class="list-group list-group-flush">
                                                @foreach($productos_encontrados_transferencia as $p)
                                                    <button type="button" class="list-group-item list-group-item-action text-start p-3" wire:click="seleccionarProductoTransferencia({{ $p->id }})">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div>
                                                                <span class="fw-bold d-block text-dark">{{ $p->nombre }}</span>
                                                                <small class="text-muted"><i class="bi bi-upc-scan me-1"></i>{{ $p->codigo_barras ?? 'Sin código' }}</small>
                                                            </div>
                                                            <div class="text-end">
                                                                <span class="badge bg-success bg-opacity-10 text-success border border-success">V: {{ $p->stock_actual }}</span>
                                                                <span class="badge bg-warning bg-opacity-10 text-dark border border-warning">I: {{ $p->stock_insumo }}</span>
                                                            </div>
                                                        </div>
                                                    </button>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    @error('prod_transferencia_id') <span class="text-danger small mt-1 d-block">{{ $message }}</span> @enderror
                                </div>

                                @if($producto_seleccionado)
                                    <div class="row align-items-center mb-4 g-2">

                                        {{-- ORIGEN (Editable) --}}
                                        <div class="col-5">
                                            <div class="p-3 border rounded text-center {{ $origen=='venta' ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-10 border-warning' }}" style="transition: all 0.3s ease;">
                                                <small class="text-muted fw-bold">MOVER DESDE</small>
                                                <select wire:model.live="origen" class="form-select mt-2 fw-bold shadow-sm">
                                                    <option value="venta">Ventas ({{ $producto_seleccionado->stock_actual }})</option>
                                                    <option value="insumo">Insumos ({{ $producto_seleccionado->stock_insumo }})</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- FLECHA DIRECCIONAL --}}
                                        <div class="col-2 text-center">
                                            <i class="bi bi-arrow-right fs-2 text-muted opacity-50"></i>
                                        </div>

                                        {{-- DESTINO (Automático / Bloqueado) --}}
                                        <div class="col-5">
                                            <div class="p-3 border rounded text-center {{ $destino=='venta' ? 'bg-success bg-opacity-10 border-success' : 'bg-warning bg-opacity-10 border-warning' }}" style="transition: all 0.3s ease;">
                                                <small class="text-muted fw-bold">HACIA</small>

                                                {{-- Está deshabilitado (disabled) para que el usuario no lo toque, el sistema lo controla --}}
                                                <select wire:model="destino" class="form-select mt-2 fw-bold shadow-sm" disabled style="background-color: #f8f9fa; cursor: not-allowed;">
                                                    <option value="venta">Ventas ({{ $producto_seleccionado->stock_actual }})</option>
                                                    <option value="insumo">Insumos ({{ $producto_seleccionado->stock_insumo }})</option>
                                                </select>
                                            </div>
                                        </div>

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

                                <button type="submit" class="btn bg-purple text-white w-100 fw-bold py-2 shadow-sm" style="border: none;" @if(!$producto_seleccionado) disabled @endif>
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
