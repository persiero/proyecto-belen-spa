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
        {{-- HEADER DE LA TARJETA --}}
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold mb-0 text-uppercase" style="color: var(--belen-dark); letter-spacing: 1px;">
                    <i class="bi bi-person-badge-fill me-2"></i> Equipo de Estilistas
                </h3>
                
                {{-- BUSCADOR --}}
                <div class="card-tools">
                    <div class="input-group" style="width: 250px;">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="bi bi-search text-muted"></i>
                        </span>
                        <input wire:model.live="search" type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar estilista...">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body">
            {{-- BOTÓN NUEVO --}}
            <button wire:click="create()" class="btn btn-primary mb-4 shadow-sm text-dark">
                <i class="bi bi-person-plus-fill me-1"></i> Registrar Estilista
            </button>

            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: var(--belen-dark); color: white;">
                        <tr>
                            <th class="py-3 ps-4">Nombre del Profesional</th>
                            <th class="py-3">Especialidad</th>
                            <th class="py-3">Contacto</th>
                            <th class="py-3 text-center">Estado</th>
                            <th class="py-3 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($estilistas as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        {{-- AVATAR GENERADO (Inicial del Nombre) --}}
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm me-3" 
                                             style="width: 40px; height: 40px; background-color: var(--belen-dark); border: 2px solid var(--belen-cream);">
                                            {{ substr($item->nombre, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $item->nombre }}</div>
                                            <small class="text-muted" style="font-size: 0.75rem;">ID: {{ str_pad($item->id, 3, '0', STR_PAD_LEFT) }}</small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($item->especialidad)
                                        <span class="badge bg-light text-dark border fw-normal">
                                            <i class="bi bi-stars text-warning me-1"></i> {{ $item->especialidad }}
                                        </span>
                                    @else
                                        <span class="text-muted small">- General -</span>
                                    @endif
                                </td>
                                <td>
                                    @if($item->telefono)
                                        <a href="tel:{{ $item->telefono }}" class="text-decoration-none text-secondary">
                                            <i class="bi bi-whatsapp text-success me-1"></i> {{ $item->telefono }}
                                        </a>
                                    @else
                                        <span class="text-muted small">-</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->activo)
                                        <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                            <i class="bi bi-check-circle me-1"></i> Activo
                                        </span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">
                                            <i class="bi bi-dash-circle me-1"></i> Inactivo
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm me-1" title="Editar">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </button>
                                    <button wire:confirm="¿Seguro que deseas eliminar a este estilista?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm" title="Eliminar">
                                        <i class="bi bi-trash text-danger"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        No hay estilistas registrados en el sistema.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $estilistas->links() }}
        </div>
    </div>

    {{-- MODAL (Estilo High-End) --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                
                {{-- HEADER MODAL --}}
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $estilista_id ? 'Editar Perfil' : 'Nuevo Estilista' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form>
                        <div class="row g-3">
                            
                            {{-- NOMBRE --}}
                            <div class="col-12">
                                <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-person"></i></span>
                                    <input type="text" wire:model="nombre" class="form-control border-start-0 @error('nombre') is-invalid @enderror" placeholder="Ej: Maria Perez">
                                </div>
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- ESPECIALIDAD --}}
                            <div class="col-md-12">
                                <label class="form-label">Especialidad <small class="text-muted">(Opcional)</small></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-stars"></i></span>
                                    <input type="text" wire:model="especialidad" class="form-control border-start-0" placeholder="Ej: Colorimetría, Cortes, Manicure">
                                </div>
                            </div>

                            {{-- TELÉFONO --}}
                            <div class="col-md-7">
                                <label class="form-label">Teléfono <small class="text-muted">(Opcional)</small></label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-whatsapp"></i></span>
                                    <input type="text" wire:model="telefono" class="form-control border-start-0" placeholder="Ej: 999 888 777">
                                </div>
                            </div>

                            {{-- ESTADO --}}
                            <div class="col-md-5">
                                <label class="form-label d-block">Estado Actual</label>
                                <div class="btn-group w-100 shadow-sm" role="group">
                                    <input type="radio" class="btn-check" wire:model="activo" value="1" id="activo_si" autocomplete="off">
                                    <label class="btn btn-outline-success" for="activo_si">
                                        <i class="bi bi-check-circle"></i> Activo
                                    </label>
                                    
                                    <input type="radio" class="btn-check" wire:model="activo" value="0" id="activo_no" autocomplete="off">
                                    <label class="btn btn-outline-secondary" for="activo_no">
                                        <i class="bi bi-dash-circle"></i> Inactivo
                                    </label>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    @if($activo)
                                        <i class="bi bi-info-circle"></i> Disponible para asignar servicios
                                    @else
                                        <i class="bi bi-info-circle"></i> No aparecerá en el sistema
                                    @endif
                                </small>
                            </div>

                        </div>
                    </form>
                </div>
                
                {{-- FOOTER MODAL --}}
                <div class="modal-footer bg-white py-3">
                    <button wire:click="closeModal()" type="button" class="btn btn-light border">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary px-4 shadow-sm text-dark fw-bold">
                        <i class="bi bi-save me-1"></i> Guardar Ficha
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>