<div>
    <div class="row">
        <div class="col-12">
            
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title">Directorio de Clientes</h3>
                    <div class="card-tools">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <input wire:model.live="search" type="text" class="form-control float-right" placeholder="Nombre o DNI...">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-default"><i class="bi bi-search"></i></button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <button wire:click="create()" class="btn btn-info mb-3 text-white">
                        <i class="bi bi-person-plus-fill"></i> Nuevo Cliente
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered table-striped">
                            <thead class="table-light">
                                <tr>
                                    <th>Cliente</th>
                                    <th>Documento</th>
                                    <th>Contacto</th>
                                    <th>Dirección</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($clientes as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-bold">{{ $item->nombre }} {{ $item->apellido }}</span>
                                        </td>
                                        <td>
                                            @if($item->tipo_documento)
                                                <span class="badge bg-secondary">{{ $item->tipo_documento }}</span> 
                                                {{ $item->numero_documento }}
                                            @else
                                                <span class="text-muted fst-italic">--</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->telefono) <i class="bi bi-whatsapp text-success"></i> {{ $item->telefono }} <br> @endif
                                            @if($item->email) <small class="text-muted">{{ $item->email }}</small> @endif
                                        </td>
                                        <td><small>{{ Str::limit($item->direccion, 30) }}</small></td>
                                        <td class="text-center">
                                            <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-warning">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button wire:confirm="¿Eliminar cliente?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No hay clientes registrados.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card-footer clearfix">
                    {{ $clientes->links() }}
                </div>
            </div>
        </div>
    </div>

    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $cliente_id ? 'Editar Cliente' : 'Nuevo Cliente' }}</h5>
                    <button wire:click="closeModal()" type="button" class="btn-close"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Nombre <span class="text-danger">*</span></label>
                                <input type="text" wire:model="nombre" class="form-control @error('nombre') is-invalid @enderror">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Apellido</label>
                                <input type="text" wire:model="apellido" class="form-control">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Tipo Doc.</label>
                                <select wire:model="tipo_documento" class="form-select">
                                    <option value="">-- Ninguno --</option>
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                    <option value="CE">C.E.</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label">Número Documento</label>
                                <input type="text" wire:model="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror">
                                @error('numero_documento') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Teléfono / WhatsApp</label>
                                <input type="text" wire:model="telefono" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                            </div>

                            <div class="col-12">
                                <label class="form-label">Dirección</label>
                                <input type="text" wire:model="direccion" class="form-control" placeholder="Av. Principal 123...">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-info text-white">
                        <i class="bi bi-save"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>