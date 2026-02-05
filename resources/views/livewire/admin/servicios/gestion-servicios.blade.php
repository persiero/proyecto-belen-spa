<div>
    {{-- MENSJAE DE ÉXITO ESTILIZADO --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" 
             style="background-color: #d1e7dd; color: #0f5132;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0 rounded-3">
        {{-- HEADER DE LA TARJETA --}}
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold mb-0 text-uppercase" style="color: var(--belen-dark); letter-spacing: 1px;">
                    <i class="bi bi-scissors me-2"></i> Catálogo de Servicios
                </h3>
                
                {{-- BUSCADOR --}}
                <div class="card-tools">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input wire:model.live="search" type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar servicio...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- BOTÓN NUEVO --}}
            <button wire:click="create()" class="btn btn-primary mb-4 shadow-sm text-dark">
                <i class="bi bi-plus-lg me-1"></i> Nuevo Servicio
            </button>

            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: var(--belen-dark); color: white;">
                        <tr>
                            <th class="py-3 ps-4">Nombre del Servicio</th>
                            <th class="py-3">Categoría</th>
                            <th class="py-3 text-center">Precio</th>
                            <th class="py-3 text-center">Duración</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicios as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark">{{ $item->nombre }}</div>
                                    <small class="text-muted" style="font-size: 0.75rem;">
                                        <i class="bi bi-tag-fill me-1"></i> {{ $item->afectacion->descripcion ?? 'S/N' }}
                                    </small>
                                </td>
                                <td>
                                    @if($item->categoria)
                                        <span class="badge bg-light text-dark border">{{ $item->categoria->nombre }}</span>
                                    @else
                                        <span class="text-muted fst-italic small">- Sin Categoría -</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-success">
                                    S/ {{ number_format($item->precio, 2) }}
                                </td>
                                <td class="text-center text-muted">
                                    @if($item->duracion_minutos)
                                        <i class="bi bi-clock me-1"></i> {{ $item->duracion_minutos }} min
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->activo)
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Activo</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm me-1" title="Editar">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    <button wire:confirm="¿Seguro que deseas eliminar este servicio?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm" title="Eliminar">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                        No se encontraron servicios registrados.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $servicios->links() }}
        </div>
    </div>

    {{-- MODAL (Estilo Personalizado) --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                
                {{-- HEADER MODAL --}}
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $servicio_id ? 'Editar Servicio' : 'Nuevo Servicio' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form>
                        <div class="row g-3">
                            
                            {{-- SECCIÓN PRINCIPAL --}}
                            <div class="col-12">
                                <label class="form-label fw-bold text-secondary small text-uppercase">Información del Servicio</label>
                            </div>

                            <div class="col-md-8">
                                <label class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre" class="form-control shadow-sm @error('nombre') is-invalid @enderror" placeholder="Ej: Corte de Cabello">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Categoría <small class="text-muted">(Opcional)</small></label>
                                <select wire:model="id_categoria" class="form-select shadow-sm">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Precio <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0">S/.</span>
                                    <input type="number" step="0.01" wire:model="precio" class="form-control border-start-0 @error('precio') is-invalid @enderror" placeholder="0.00">
                                </div>
                                @error('precio') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Duración Estimada <small class="text-muted">(Opcional)</small></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-clock"></i></span>
                                    <input type="number" wire:model="duracion_minutos" class="form-control border-start-0 @error('duracion_minutos') is-invalid @enderror" placeholder="30">
                                    <span class="input-group-text bg-white text-muted">min</span>
                                </div>
                                @error('duracion_minutos') <span class="text-danger small d-block mt-1"><i class="bi bi-exclamation-circle"></i> {{ $message }}</span> @enderror
                                <small class="text-muted d-block mt-1">Si no se especifica, se usarán 30 minutos por defecto</small>
                            </div>

                            <div class="col-12 mt-4">
                                <hr class="text-muted">
                                <label class="form-label fw-bold text-secondary small text-uppercase d-flex justify-content-between">
                                    <span>Configuración Avanzada (SUNAT)</span>
                                    <span class="badge bg-secondary fw-normal">Requerido</span>
                                </label>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label small">Tipo de Afectación IGV</label>
                                <select wire:model="id_afectacion" class="form-select form-select-sm bg-white @error('id_afectacion') is-invalid @enderror">
                                    @foreach($afectaciones as $afec)
                                        <option value="{{ $afec->id }}">{{ $afec->codigo }} - {{ Str::limit($afec->descripcion, 40) }}</option>
                                    @endforeach
                                </select>
                                @error('id_afectacion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label small">Unidad de Medida</label>
                                <select wire:model="id_unidad" class="form-select form-select-sm bg-white @error('id_unidad') is-invalid @enderror">
                                    @foreach($unidades as $uni)
                                        <option value="{{ $uni->id }}">{{ $uni->codigo }} - {{ $uni->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('id_unidad') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-2">
                                <label class="form-label small">Estado</label>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" wire:model="activo" id="checkActivo">
                                    <label class="form-check-label" for="checkActivo">Activo</label>
                                </div>
                            </div>

                        </div>
                    </form>
                </div>
                
                {{-- FOOTER MODAL --}}
                <div class="modal-footer bg-white py-3">
                    <button wire:click="closeModal()" type="button" class="btn btn-light border">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary px-4 shadow-sm text-dark fw-bold">
                        <i class="bi bi-save me-1"></i> Guardar Servicio
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>