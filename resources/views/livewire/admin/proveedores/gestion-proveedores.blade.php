<div>
    {{-- HEADER CON TÍTULO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-truck me-2"></i> Gestión de Proveedores</h4>
            <small class="text-muted">Administra tus proveedores y distribuidores</small>
        </div>
        <button wire:click="create()" class="btn btn-primary shadow-sm fw-bold">
            <i class="bi bi-plus-lg me-1"></i> Nuevo Proveedor
        </button>
    </div>

    {{-- ALERTAS --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold mb-3">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-building me-2"></i> Listado de Proveedores</h5>
                        <div class="input-group" style="width: 300px;">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                            <input wire:model.live.debounce.300ms="search" type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar empresa o RUC...">
                        </div>
                    </div>
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead style="background-color: var(--belen-dark); color: white;">
                                <tr>
                                    <th class="ps-4 py-3">Empresa / Razón Social</th>
                                    <th class="py-3">RUC / DNI</th>
                                    <th class="py-3">Contacto</th>
                                    <th class="py-3">Teléfono</th>
                                    <th class="text-center py-3 pe-4">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($proveedores as $item)
                                    <tr>
                                        <td class="ps-4">
                                            <div class="fw-bold text-dark">{{ $item->nombre_empresa }}</div>
                                            @if($item->direccion)
                                                <small class="text-muted"><i class="bi bi-geo-alt"></i> {{ Str::limit($item->direccion, 40) }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                                {{ $item->ruc_dni ?? '-' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="fw-bold text-secondary">{{ $item->contacto }}</div>
                                            @if($item->email) 
                                                <small class="text-muted"><i class="bi bi-envelope"></i> {{ $item->email }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->telefono)
                                                <span class="text-dark"><i class="bi bi-telephone"></i> {{ $item->telefono }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center pe-4">
                                            <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-outline-primary border-0 bg-light shadow-sm me-1" title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button wire:confirm="¿Eliminar este proveedor?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-outline-danger border-0 bg-light shadow-sm" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted opacity-50">
                                                <i class="bi bi-truck fs-1 d-block mb-2"></i>
                                                No hay proveedores registrados.
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top-0 py-3">{{ $proveedores->links() }}</div>
            </div>
        </div>
    </div>

    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(3px);" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $proveedor_id ? 'Editar Proveedor' : 'Nuevo Proveedor' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <form>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">RAZÓN SOCIAL / EMPRESA <span class="text-danger">*</span></label>
                            <input type="text" wire:model="nombre_empresa" class="form-control shadow-sm @error('nombre_empresa') is-invalid @enderror" placeholder="Ej: Distribuidora ABC S.A.C.">
                            @error('nombre_empresa') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">RUC / DNI</label>
                                <input type="text" wire:model="ruc_dni" class="form-control shadow-sm" placeholder="20123456789">
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold small text-secondary">TELÉFONO</label>
                                <input type="text" wire:model="telefono" class="form-control shadow-sm" placeholder="987654321">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">NOMBRE DE CONTACTO (VENDEDOR)</label>
                            <input type="text" wire:model="contacto" class="form-control shadow-sm" placeholder="Ej: Juan Pérez">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">EMAIL</label>
                            <input type="email" wire:model="email" class="form-control shadow-sm" placeholder="contacto@empresa.com">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">DIRECCIÓN</label>
                            <input type="text" wire:model="direccion" class="form-control shadow-sm" placeholder="Av. Principal 123, Lima">
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-white py-3">
                    <button wire:click="closeModal()" type="button" class="btn btn-light border">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary px-4 shadow-sm fw-bold">
                        <i class="bi bi-check-circle-fill me-2"></i> Guardar
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>