<div>
    {{-- HEADER CON TÍTULO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-shield-lock me-2"></i> Control de Acceso</h4>
            <small class="text-muted">Administra usuarios y permisos del sistema</small>
        </div>
        <button wire:click="resetInput" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-person-plus-fill me-1"></i> Nuevo Usuario
        </button>
    </div>

    {{-- ALERTAS --}}
    @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold mb-3">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger bg-opacity-10 text-danger fw-bold mb-3">
            <i class="bi bi-x-circle-fill me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- TABLA --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-people me-2"></i> Usuarios del Sistema</h5>
                <div class="input-group" style="width: 300px;">
                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                    <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar por nombre o email...">
                </div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: var(--belen-dark); color: white;">
                        <tr>
                            <th class="ps-4 py-3">Usuario</th>
                            <th class="py-3">Rol</th>
                            <th class="py-3">Estado</th>
                            <th class="py-3">Registro</th>
                            <th class="text-end pe-4 py-3">Acciones</th>
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
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">{{ ucfirst($user->rol->nombre) }}</span>
                                    @else
                                        <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Sin Rol</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->activo)
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success">Activo</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-muted small">
                                    {{ $user->created_at->format('d/m/Y') }}
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $user->id }})" data-bs-toggle="modal" data-bs-target="#userModal" class="btn btn-sm btn-outline-primary border-0 bg-light shadow-sm me-1" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    <button wire:confirm="¿Estás seguro de eliminar este usuario?" wire:click="delete({{ $user->id }})" class="btn btn-sm btn-outline-danger border-0 bg-light shadow-sm" title="Eliminar">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="text-muted opacity-50">
                                        <i class="bi bi-people fs-1 d-block mb-2"></i>
                                        No se encontraron usuarios.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">
            {{ $users->links() }}
        </div>
    </div>

    {{-- MODAL (CREAR / EDITAR) --}}
    <div wire:ignore.self class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $user_id ? 'Editar Usuario' : 'Nuevo Usuario' }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form wire:submit.prevent="save">
                    <div class="modal-body p-4 bg-light">
                        
                        {{-- Campo: nombre --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">NOMBRE COMPLETO</label>
                            <input wire:model="nombre" type="text" class="form-control shadow-sm @error('nombre') is-invalid @enderror" placeholder="Ej: Juan Pérez">
                            @error('nombre') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Campo: email --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">CORREO ELECTRÓNICO</label>
                            <input wire:model="email" type="email" class="form-control shadow-sm @error('email') is-invalid @enderror" placeholder="usuario@ejemplo.com">
                            @error('email') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        {{-- Campo: password --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">
                                CONTRASEÑA
                                @if($user_id) <small class="text-muted fw-normal">(Dejar vacío para mantener actual)</small> @endif
                            </label>
                            <input wire:model="password" type="password" class="form-control shadow-sm @error('password') is-invalid @enderror" placeholder="••••••••">
                            @error('password') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="row g-3">
                            {{-- Campo: id_rol --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">ROL</label>
                                <select wire:model="id_rol" class="form-select shadow-sm @error('id_rol') is-invalid @enderror">
                                    <option value="">-- Seleccionar --</option>
                                    @foreach($roles as $rol)
                                        <option value="{{ $rol->id }}">{{ ucfirst($rol->nombre) }}</option>
                                    @endforeach
                                </select>
                                @error('id_rol') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                            </div>

                            {{-- Campo: activo --}}
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">ESTADO</label>
                                <select wire:model="activo" class="form-select shadow-sm">
                                    <option value="1">Activo</option>
                                    <option value="0">Inactivo</option>
                                </select>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-white py-3">
                        <button type="button" class="btn btn-light border" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                            <span wire:loading.remove wire:target="save"><i class="bi bi-check-circle-fill me-2"></i>Guardar</span>
                            <span wire:loading wire:target="save"><i class="bi bi-hourglass-split me-2"></i>Guardando...</span>
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
