<div>
    <div class="row">
        <div class="col-12">
            
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-outline card-success"> {{-- Cambié el color a verde (success) para diferenciar --}}
                <div class="card-header">
                    <h3 class="card-title">Equipo de Estilistas</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input wire:model.live="search" type="text" class="form-control float-right" placeholder="Buscar estilista...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <button wire:click="create()" class="btn btn-success mb-3">
                        <i class="bi bi-person-plus"></i> Nuevo Estilista
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Especialidad</th>
                                    <th>Teléfono</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($estilistas as $item)
                                    <tr>
                                        <td class="fw-bold">{{ $item->nombre }}</td>
                                        <td>{{ $item->especialidad ?? '-' }}</td>
                                        <td>{{ $item->telefono ?? '-' }}</td>
                                        <td>
                                            @if($item->activo)
                                                <span class="badge text-bg-success">Activo</span>
                                            @else
                                                <span class="badge text-bg-secondary">Inactivo</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:confirm="¿Deseas eliminar a este estilista?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay estilistas registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer clearfix">
                    {{ $estilistas->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $estilista_id ? 'Editar Estilista' : 'Registrar Estilista' }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                            @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Especialidad (Opcional)</label>
                            <input type="text" wire:model="especialidad" class="form-control" placeholder="Ej: Colorimetría, Corte Caballero">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Teléfono (Opcional)</label>
                            <input type="text" wire:model="telefono" class="form-control">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select wire:model="activo" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo (Vacaciones/Baja)</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-success">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>