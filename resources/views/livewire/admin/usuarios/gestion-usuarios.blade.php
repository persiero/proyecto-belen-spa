<div>
    {{-- CABECERA --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="text-uppercase fw-bold text-secondary">Gestión de Usuarios</h4>
        <button wire:click="resetInput" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-primary">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </button>
    </div>

    {{-- ALERTA --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('success') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLA --}}
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3">
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                <input wire:model.live="search" type="text" class="form-control border-start-0" placeholder="Buscar por nombre o email...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Usuario</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Registro</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <div class="rounded-circle bg-secondary d-flex justify-content-center align-items-center text-white fw-bold me-2" style="width: 35px; height: 35px;">
                                            {{ substr($user->nombre, 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="fw-bold text-dark">{{ $user->nombre }}</div>
                                            <div class="small text-muted">{{ $user->email }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if($user->rol)
                                        <span class="badge bg-primary">{{ ucfirst($user->rol->nombre) }}</span>
                                    @else
                                        <span class="badge bg-light text-dark border">Sin Rol</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->activo)
                                        <span class="badge bg-success bg-opacity-10 text-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $user->id }})" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-sm btn-light text-primary border">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    <button wire:confirm="¿Estás seguro de eliminar este usuario?" wire:click="delete({{ $user->id }})" class="btn btn-sm btn-light text-danger border ms-1">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No se encontraron usuarios.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0">
            {{ $users->links() }}
        </div>
    </div>

    {{-- MODAL (CREAR / EDITAR) --}}
    <div wire:ignore.self class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold">
                        {{ $user_id ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body">
                        
                        {{-- Campo: nombre --}}
                        <div class="mb-3">
                            <label class="form-label">Nombre Completo</label>
                            <input wire:model="nombre" type="text" class="form-control @error('nombre') is-invalid @enderror">
                            @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Campo: email --}}
                        <div class="mb-3">
                            <label class="form-label">Correo Electrónico</label>
                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        {{-- Campo: password --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Contraseña
                                @if($user_id) <small class="text-muted fw-normal">(Dejar vacío para mantener actual)</small> @endif
                            </label>
                            <input wire:model="password" type="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="row">
                            {{-- Campo: id_rol --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Rol</label>
                                <select wire:model="id_rol" class="form-select @error('id_rol') is-invalid @enderror">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }}</option>
                                    @endforeach
                                </select>
                                @error('id_rol') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- Campo: activo --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Estado</label>
                                <select wire:model="activo" class="form-select">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <span wire:loading.remove wire:target="save">Guardar Datos</span>
                            <span wire:loading wire:target="save"><i class="bi bi-hourglass-split"></i> Guardando...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Script para cerrar modal --}}
    @script
    <script>
        $wire.on('close-modal', () => {
            var el = document.getElementById('userModal');
            var modal = bootstrap.Modal.getInstance(el);
            if(modal) modal.hide();
        });
    </script>
    @endscript
</div>
