<div>
    <div class="row">
        <div class="col-12">
            
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Proveedores y Distribuidores</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input wire:model.live="search" type="text" class="form-control float-right" placeholder="Empresa o RUC...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <button wire:click="create()" class="btn btn-secondary mb-3">
                        <i class="bi bi-truck"></i> Nuevo Proveedor
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Empresa / Razón Social</th>
                                    <th>RUC / DNI</th>
                                    <th>Contacto</th>
                                    <th>Teléfono</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proveedores as $item)
                                    <tr>
                                        <td class="fw-bold">{{ $item->nombre_empresa }}</td>
                                        <td>{{ $item->ruc_dni ?? '-' }}</td>
                                        <td>
                                            {{ $item->contacto }}
                                            @if($item->email) <br><small class="text-muted">{{ $item->email }}</small> @endif
                                        </td>
                                        <td>{{ $item->telefono ?? '-' }}</td>
                                        <td class="text-center">
                                            <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></button>
                                            <button wire:confirm="¿Eliminar?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No hay proveedores registrados.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer clearfix">{{ $proveedores->links() }}</div>
            </div>
        </div>
    </div>

    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $proveedor_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label">Razón Social / Empresa <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre_empresa" class="form-control @error('nombre_empresa') is-invalid @enderror">
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label">RUC / DNI</label>
                                <input type="text" wire:model="ruc_dni" class="form-control">
                            </div>
                            <div class="col-6">
                                <label class="form-label">Teléfono</label>
                                <input type="text" wire:model="telefono" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nombre de Contacto (Vendedor)</label>
                            <input type="text" wire:model="contacto" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" wire:model="email" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Dirección</label>
                            <input type="text" wire:model="direccion" class="form-control">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>