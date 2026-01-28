<div>
    <div class="row">
        <div class="col-12">
            
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Listado de Servicios</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input wire:model.live="search" type="text" class="form-control float-right" placeholder="Buscar servicio...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default">
                                    <i class="bi bi-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <button wire:click="create()" class="btn btn-primary mb-3">
                        <i class="bi bi-plus-circle"></i> Nuevo Servicio
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Nombre</th>
                                    <th>Categoría</th>
                                    <th>Precio (S/)</th>
                                    <th>Duración</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($servicios as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $item->nombre }}</span><br>
                                            <small class="text-muted">{{ $item->afectacion->descripcion ?? 'S/N' }}</small>
                                        </td>
                                        <td>{{ $item->categoria->nombre ?? '-' }}</td>
                                        <td>S/ {{ number_format($item->precio, 2) }}</td>
                                        <td>{{ $item->duracion_minutos ? $item->duracion_minutos . ' min' : '-' }}</td>
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
                                            <button wire:confirm="¿Seguro que deseas eliminar este servicio?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No se encontraron servicios.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer clearfix">
                    {{ $servicios->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $servicio_id ? 'Editar Servicio' : 'Crear Nuevo Servicio' }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nombre del Servicio <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Categoría</label>
                                <select wire:model="id_categoria" class="form-select">
                                    <option value="">-- Sin Categoría --</option>
                                    @foreach($categorias as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Precio (S/) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" wire:model="precio" class="form-control @error('precio') is-invalid @enderror">
                                @error('precio') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Duración (min)</label>
                                <input type="number" wire:model="duracion_minutos" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Estado</label>
                                <select wire:model="activo" class="form-select">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>

                            <div class="col-12"><hr></div>
                            <h6 class="text-primary"><i class="bi bi-file-earmark-text"></i> Datos para SUNAT</h6>

                            <div class="col-md-6">
                                <label class="form-label">Tipo de Afectación <span class="text-danger">*</span></label>
                                <select wire:model="id_afectacion" class="form-select @error('id_afectacion') is-invalid @enderror">
                                    @foreach($afectaciones as $afec)
                                        <option value="{{ $afec->id }}">{{ $afec->codigo }} - {{ $afec->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('id_afectacion') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Unidad de Medida <span class="text-danger">*</span></label>
                                <select wire:model="id_unidad" class="form-select @error('id_unidad') is-invalid @enderror">
                                    @foreach($unidades as $uni)
                                        <option value="{{ $uni->id }}">{{ $uni->codigo }} - {{ $uni->descripcion }}</option>
                                    @endforeach
                                </select>
                                @error('id_unidad') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>