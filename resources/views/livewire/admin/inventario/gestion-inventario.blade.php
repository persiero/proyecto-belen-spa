<div>
    <div class="row">
        <div class="col-12 mb-3">
            <h3 class="mb-3">📡 Centro de Control de Stock</h3>
            
            {{-- MENÚ DE PESTAÑAS --}}
            <ul class="nav nav-pills p-2 bg-white rounded shadow-sm">
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'stock' ? 'active' : '' }}" href="#" wire:click.prevent="cambiarTab('stock')">
                        <i class="bi bi-box-seam"></i> Stock Actual
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'kardex' ? 'active' : '' }}" href="#" wire:click.prevent="cambiarTab('kardex')">
                        <i class="bi bi-list-columns-reverse"></i> Movimientos (Kardex)
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'ajuste' ? 'active' : '' }}" href="#" wire:click.prevent="cambiarTab('ajuste')">
                        <i class="bi bi-sliders"></i> Ajustes Manuales
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $tab == 'transferencia' ? 'active' : '' }}" href="#" wire:click.prevent="cambiarTab('transferencia')">
                        <i class="bi bi-arrow-left-right"></i> Transferencias
                    </a>
                </li>
            </ul>
        </div>

        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- PESTAÑA 1: STOCK ACTUAL (MEJORADA) --}}
            @if($tab == 'stock')
            <div class="card card-outline card-success shadow-sm">
                <div class="card-header bg-white border-bottom-0 mt-2">
                    <h3 class="card-title text-secondary fw-bold"><i class="bi bi-boxes"></i> Existencias por Almacén</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" wire:model.live="search" class="form-control border-start-0" placeholder="Buscar producto...">
                        </div>
                    </div>
                </div>
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover align-middle">
                        <thead class="bg-light text-secondary small text-uppercase">
                            <tr>
                                <th class="ps-4">Producto</th>
                                <th>Estado Operativo</th> {{-- Antes "Tipo" --}}
                                <th class="text-center">Vitrina (Venta)</th>
                                <th class="text-center">Interno (Insumo)</th>
                                <th class="text-center pe-4">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($listaStock as $p)
                                {{-- LÓGICA DE ESTADO DINÁMICO --}}
                                @php
                                    $estadoVisual = '';
                                    $claseBadge = '';
                                    $icono = '';

                                    if ($p->stock_actual > 0 && $p->stock_insumo > 0) {
                                        // Tiene en los dos lados -> Es MIXTO
                                        $estadoVisual = 'Mixto (Activo)';
                                        $claseBadge = 'bg-primary bg-opacity-10 text-primary border border-primary'; 
                                        $icono = 'bi-arrow-left-right';
                                    } elseif ($p->stock_insumo > 0 && $p->stock_actual == 0) {
                                        // Solo tiene insumo -> Es INSUMO
                                        $estadoVisual = 'Solo Uso Interno';
                                        $claseBadge = 'bg-warning bg-opacity-10 text-dark border border-warning';
                                        $icono = 'bi-bucket';
                                    } elseif ($p->stock_actual > 0 && $p->stock_insumo == 0) {
                                        // Solo tiene venta -> Es VENTA
                                        $estadoVisual = 'Solo Venta';
                                        $claseBadge = 'bg-success bg-opacity-10 text-success border border-success';
                                        $icono = 'bi-shop';
                                    } else {
                                        // Sin stock -> Usamos el de la BD por defecto
                                        $estadoVisual = 'Sin Stock (' . ucfirst($p->tipo) . ')';
                                        $claseBadge = 'bg-secondary bg-opacity-10 text-secondary';
                                        $icono = 'bi-dash-circle';
                                    }
                                @endphp

                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-dark">{{ $p->nombre }}</div>
                                        <small class="text-muted">{{ $p->codigo_barras ?? 'Sin código' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill {{ $claseBadge }} fw-normal px-3 py-2">
                                            <i class="bi {{ $icono }} me-1"></i> {{ $estadoVisual }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($p->stock_actual > 0)
                                            <span class="fw-bold text-success fs-5">{{ $p->stock_actual }}</span>
                                        @else
                                            <span class="text-muted text-opacity-25">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($p->stock_insumo > 0)
                                            <span class="fw-bold text-warning text-dark fs-5">{{ $p->stock_insumo }}</span>
                                        @else
                                            <span class="text-muted text-opacity-25">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-4">
                                        <span class="fw-bold text-dark">{{ $p->stock_actual + $p->stock_insumo }}</span>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">No se encontraron productos.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0">{{ $listaStock->links() }}</div>
            </div>
            @endif

            {{-- PESTAÑA 2: KARDEX (REDISEÑO VISUAL) --}}
            @if($tab == 'kardex')
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 text-primary fw-bold">
                            <i class="bi bi-clock-history me-2"></i> Historial de Movimientos
                        </h5>
                        <div class="input-group input-group-sm" style="width: 200px;">
                            <input type="text" wire:model.live="search" class="form-control" placeholder="Filtrar historial...">
                            <span class="input-group-text bg-white"><i class="bi bi-search"></i></span>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-muted small text-uppercase">
                                <tr>
                                    <th class="ps-4" width="15%">Fecha</th>
                                    <th width="5%">Tipo</th>
                                    <th width="30%">Detalle del Movimiento</th>
                                    <th width="25%">Producto</th>
                                    <th class="text-end pe-4" width="15%">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($movimientos as $mov)
                                    {{-- Lógica de Iconos y Colores --}}
                                    @php
                                        $color = 'secondary';
                                        $icono = 'bi-circle';
                                        $titulo = 'Movimiento';
                                        
                                        switch($mov->tipo) {
                                            case 'entrada': 
                                                $color = 'success'; $icono = 'bi-truck'; $titulo = 'Compra / Entrada'; break;
                                            case 'salida_venta': 
                                                $color = 'primary'; $icono = 'bi-bag-check'; $titulo = 'Venta POS'; break;
                                            case 'salida_insumo': 
                                                $color = 'warning'; $icono = 'bi-droplet-half'; $titulo = 'Consumo Interno'; break;
                                            case 'ajuste': 
                                                // Detectamos si es transferencia por la referencia
                                                if(str_contains($mov->referencia, 'TRANSFERENCIA')) {
                                                    $color = 'info'; $icono = 'bi-arrow-left-right'; $titulo = 'Transferencia';
                                                } else {
                                                    $color = 'secondary'; $icono = 'bi-sliders'; $titulo = 'Ajuste Manual';
                                                }
                                                break;
                                        }
                                    @endphp

                                    <tr class="border-bottom-0">
                                        <td class="ps-4 text-muted small">
                                            <div class="fw-bold text-dark">{{ $mov->fecha->format('d M Y') }}</div>
                                            {{ $mov->fecha->format('H:i A') }}
                                        </td>
                                        <td class="text-center">
                                            <div class="rounded-circle bg-{{ $color }} bg-opacity-10 d-inline-flex align-items-center justify-content-center text-{{ $color }}" 
                                                style="width: 40px; height: 40px;">
                                                <i class="bi {{ $icono }} fs-5"></i>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $color }} bg-opacity-10 text-{{ $color }} mb-1 border border-{{ $color }} border-opacity-25">
                                                {{ $titulo }}
                                            </span>
                                            <div class="small text-muted text-truncate" style="max-width: 250px;">
                                                {{ $mov->motivo ?? $mov->referencia }}
                                            </div>
                                        </td>
                                        <td class="fw-bold text-secondary">
                                            {{ $mov->producto->nombre }}
                                        </td>
                                        <td class="text-end pe-4">
                                            @if($mov->cantidad > 0)
                                                <span class="fs-5 fw-bold text-success">+{{ $mov->cantidad }}</span>
                                            @elseif($mov->cantidad < 0)
                                                <span class="fs-5 fw-bold text-danger">{{ $mov->cantidad }}</span>
                                            @else
                                                <span class="fs-5 fw-bold text-muted">0</span>
                                            @endif
                                            <small class="text-muted d-block" style="font-size: 0.7rem;">unid.</small>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="bi bi-journal-x display-4"></i><br>
                                                <span class="mt-2 d-block">No hay movimientos registrados aún.</span>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3">
                    {{ $movimientos->links() }}
                </div>
            </div>
            @endif

            {{-- PESTAÑA 3: AJUSTES (MEJORADA) --}}
            @if($tab == 'ajuste')
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-warning text-dark">
                            <h5 class="mb-0">Registrar Movimiento Manual</h5>
                        </div>
                        <div class="card-body">
                            <form wire:submit.prevent="guardarAjuste">
                                <div class="mb-3">
                                    <label class="form-label">Producto</label>
                                    {{-- Usamos wire:model.live para que se actualice la info al seleccionar --}}
                                    <select wire:model.live="producto_id" class="form-select">
                                        <option value="">-- Seleccionar --</option>
                                        @foreach($productos as $p)
                                            <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('producto_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    
                                    {{-- INFO CARD: ESTO ES LO NUEVO --}}
                                    @if($producto_seleccionado)
                                        <div class="alert alert-info mt-2 mb-0 py-2 d-flex justify-content-around">
                                            <div class="text-center">
                                                <small>Stock Venta</small><br>
                                                <strong class="fs-5">{{ $producto_seleccionado->stock_actual }}</strong>
                                            </div>
                                            <div class="border-end border-secondary mx-2"></div>
                                            <div class="text-center">
                                                <small>Stock Insumo</small><br>
                                                <strong class="fs-5">{{ $producto_seleccionado->stock_insumo }}</strong>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Tipo de Acción</label>
                                    <select wire:model="tipo_movimiento" class="form-select">
                                        <option value="salida_insumo">📉 Consumo Interno (Descuenta Stock Insumo)</option>
                                        <option value="ajuste_entrada">📈 Ajuste Entrada (Suma a Venta)</option>
                                        <option value="ajuste_salida">🗑️ Ajuste Salida (Descuenta de Venta)</option>
                                    </select>
                                    <div class="form-text small text-muted">
                                        Si eliges "Consumo Interno", se restará del stock de insumos.
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Cantidad</label>
                                        <input type="number" wire:model="cantidad" class="form-control" min="1">
                                        @error('cantidad') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label class="form-label">Motivo</label>
                                        <input type="text" wire:model="motivo" class="form-control" placeholder="Motivo...">
                                        @error('motivo') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning w-100 fw-bold">Guardar Movimiento</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- PESTAÑA 4: TRANSFERENCIAS --}}
            @if($tab == 'transferencia')
            <div class="row justify-content-center">
                <div class="col-md-8"> {{-- Hacemos la tarjeta un poco más ancha para mejor diseño --}}
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-gradient bg-info text-white">
                            <h5 class="mb-0"><i class="bi bi-arrow-left-right me-2"></i> Transferencia Interna</h5>
                        </div>
                        <div class="card-body">
                            
                            {{-- Alerta informativa --}}
                            <div class="alert alert-light border mb-4 d-flex align-items-center">
                                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                                <small class="text-muted">
                                    Utiliza esta herramienta para mover productos físicos entre tu <strong>Vitrina (Venta)</strong> y tu <strong>Almacén Interno (Insumos)</strong> sin alterar el stock total.
                                </small>
                            </div>

                            <form wire:submit.prevent="guardarTransferencia">
                                
                                {{-- 1. SELECCIÓN DE PRODUCTO --}}
                                <div class="mb-4">
                                    <label class="form-label fw-bold text-uppercase small text-secondary">1. Seleccionar Producto</label>
                                    <select wire:model.live="prod_transferencia_id" class="form-select form-select-lg">
                                        <option value="">-- Buscar Producto --</option>
                                        @foreach($productos as $p)
                                            <option value="{{ $p->id }}">{{ $p->nombre }}</option>
                                        @endforeach
                                    </select>
                                    @error('prod_transferencia_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                </div>

                                {{-- TARJETA DE STOCK (VISUALIZACIÓN) --}}
                                @if($producto_seleccionado)
                                <div class="row g-2 mb-4">
                                    <div class="col-6">
                                        <div class="p-3 rounded border text-center {{ $origen == 'venta' ? 'bg-success bg-opacity-10 border-success' : 'bg-light' }}">
                                            <small class="text-muted text-uppercase fw-bold">Vitrina (Venta)</small>
                                            <div class="fs-4 fw-bold {{ $origen == 'venta' ? 'text-success' : 'text-dark' }}">
                                                {{ $producto_seleccionado->stock_actual }}
                                            </div>
                                            <span class="badge bg-success">Disponible</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="p-3 rounded border text-center {{ $origen == 'insumo' ? 'bg-warning bg-opacity-10 border-warning' : 'bg-light' }}">
                                            <small class="text-muted text-uppercase fw-bold">Lavadero (Insumo)</small>
                                            <div class="fs-4 fw-bold {{ $origen == 'insumo' ? 'text-warning' : 'text-dark' }}">
                                                {{ $producto_seleccionado->stock_insumo }}
                                            </div>
                                            <span class="badge bg-warning text-dark">Uso Interno</span>
                                        </div>
                                    </div>
                                </div>
                                @endif

                                {{-- 2. CONFIGURACIÓN DEL MOVIMIENTO --}}
                                <div class="row align-items-center mb-4 bg-light p-3 rounded mx-0">
                                    <div class="col-md-5 text-center">
                                        <label class="form-label fw-bold small text-muted">DESDE (Origen)</label>
                                        <select wire:model.live="origen" class="form-select border-2 {{ $origen == 'venta' ? 'border-success text-success fw-bold' : 'border-warning text-warning fw-bold' }}">
                                            <option value="venta" class="text-success fw-bold">🏪 Venta (Vitrina)</option>
                                            <option value="insumo" class="text-warning fw-bold">🧴 Insumo (Interno)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-2 text-center py-2">
                                        <i class="bi bi-arrow-right-circle-fill fs-1 text-muted opacity-25"></i>
                                    </div>

                                    <div class="col-md-5 text-center">
                                        <label class="form-label fw-bold small text-muted">HACIA (Destino)</label>
                                        <select wire:model.live="destino" class="form-select border-2 {{ $destino == 'venta' ? 'border-success text-success fw-bold' : 'border-warning text-warning fw-bold' }}">
                                            <option value="insumo" class="text-warning fw-bold">🧴 Insumo (Interno)</option>
                                            <option value="venta" class="text-success fw-bold">🏪 Venta (Vitrina)</option>
                                        </select>
                                        {{-- Validación visual de lógica --}}
                                        @if($origen == $destino)
                                            <small class="text-danger d-block mt-1"><i class="bi bi-x-circle"></i> Destino debe ser diferente</small>
                                        @endif
                                    </div>
                                </div>

                                {{-- 3. CANTIDAD Y CONFIRMACIÓN --}}
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label fw-bold small text-secondary">CANTIDAD</label>
                                        <input type="number" wire:model="cant_transferencia" class="form-control form-control-lg text-center fw-bold" min="1">
                                        @error('cant_transferencia') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                    <div class="col-md-8 mb-3">
                                        <label class="form-label fw-bold small text-secondary">MOTIVO (Opcional)</label>
                                        <input type="text" wire:model="motivo_transferencia" class="form-control form-control-lg" placeholder="Ej: Reposición, Uso urgente...">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-info text-white w-100 py-3 fw-bold fs-5 shadow-sm" 
                                    @if(!$producto_seleccionado || $origen == $destino) disabled @endif>
                                    <i class="bi bi-save me-2"></i> Confirmar Transferencia
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