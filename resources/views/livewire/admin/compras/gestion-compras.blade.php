<div>
    <div class="row">
        <div class="col-12">
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">📦 Catálogo</h3>
                </div>
                <div class="card-body">
                    <input type="text" wire:model.live="searchProducto" class="form-control mb-3" placeholder="Buscar insumo o producto...">
                    
                    <div class="list-group">
                        @foreach($productos as $p)
                            <button wire:click="addProducto({{ $p->id }})" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>{{ $p->nombre }}</strong>
                                    <br><small class="text-muted">Stock actual: {{ $p->stock_actual }}</small>
                                </div>
                                <span class="badge bg-secondary">Costo: S/ {{ $p->costo_compra }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Ingreso de Mercadería</h3>
                </div>
                <div class="card-body">
                    
                    <div class="row g-2 mb-3">
                        <div class="col-md-4">
                            <label class="small">Fecha</label>
                            <input type="date" wire:model="fecha_compra" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label class="small">Proveedor (Opcional)</label>
                            <select wire:model="id_proveedor" class="form-select form-select-sm">
                                <option value="">-- Sin Proveedor --</option>
                                @foreach($proveedores as $prov)
                                    <option value="{{ $prov->id }}">{{ $prov->nombre_empresa }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small">Nro. Documento / Nota</label>
                            <input type="text" wire:model="numero_documento" class="form-control form-control-sm" placeholder="Ej: F001-456">
                        </div>
                    </div>

                    <table class="table table-bordered table-sm text-sm">
                        <thead class="table-light">
                            <tr>
                                <th>Producto</th>
                                <th width="15%">Cantidad</th>
                                <th width="20%">Costo Unit. (S/)</th>
                                <th width="20%" class="text-end">Subtotal</th>
                                <th width="5%"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($cart as $index => $item)
                                <tr>
                                    <td class="align-middle">
                                        {{ $item['nombre'] }}
                                        {{-- NUEVO: INDICADOR DE DESTINO --}}
                                        <br>
                                        @if($item['tipo'] == 'insumo')
                                            <span class="badge bg-warning text-dark" style="font-size: 0.7rem;">
                                                <i class="bi bi-box-seam"></i> Destino: Insumos
                                            </span>
                                        @else
                                            <span class="badge bg-success" style="font-size: 0.7rem;">
                                                <i class="bi bi-shop"></i> Destino: Venta
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <input type="number" 
                                            wire:change="updateItem({{ $index }}, 'cantidad', $event.target.value)"
                                            value="{{ $item['cantidad'] }}" 
                                            class="form-control form-control-sm text-center">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" 
                                            wire:change="updateItem({{ $index }}, 'costo', $event.target.value)"
                                            value="{{ $item['costo'] }}" 
                                            class="form-control form-control-sm text-end">
                                    </td>
                                    <td class="align-middle text-end fw-bold">
                                        S/ {{ number_format($item['subtotal'], 2) }}
                                    </td>
                                    <td class="text-center align-middle">
                                        <button wire:click="removeItem({{ $index }})" class="btn btn-xs btn-danger"><i class="bi bi-x"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted p-3">Agrega productos del catálogo</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold fs-5">TOTAL COMPRA:</td>
                                <td class="text-end fw-bold fs-5 text-primary">S/ {{ number_format($total, 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>

                    <div class="mt-3 text-end">
                        <button wire:confirm="¿Confirmar ingreso de stock? Esto actualizará las cantidades." 
                                wire:click="guardarCompra" 
                                class="btn btn-primary" 
                                @if(empty($cart)) disabled @endif>
                            <i class="bi bi-save"></i> Guardar Ingreso
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>