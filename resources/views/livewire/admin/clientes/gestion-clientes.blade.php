<div>
    {{-- ALERTAS --}}
    @if (session()->has('message'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" 
             style="background-color: #d1e7dd; color: #0f5132;">
            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- TABLA LISTADO (Se mantiene igual de bonita que antes, solo pego la estructura principal) --}}
    <div class="card shadow-sm border-0 rounded-3">
        <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title fw-bold mb-0 text-uppercase" style="color: var(--belen-dark); letter-spacing: 1px;">
                    <i class="bi bi-people-fill me-2"></i> Directorio de Clientes
                </h3>
                <div class="card-tools">
                    <div class="input-group" style="width: 280px;">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input wire:model.live="search" type="text" class="form-control bg-light border-start-0 ps-0" placeholder="Buscar por nombre, dni o teléfono...">
                    </div>
                </div>
            </div>
        </div>
        <div class="card-body">
            <button wire:click="create()" class="btn btn-primary mb-4 shadow-sm text-dark">
                <i class="bi bi-person-plus-fill me-1"></i> Nuevo Cliente
            </button>
            <div class="table-responsive rounded-3 border">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color: var(--belen-dark); color: white;">
                        <tr>
                            <th class="py-3 ps-4">Cliente / Razón Social</th>
                            <th class="py-3">Identificación</th>
                            <th class="py-3">Contacto</th>
                            <th class="py-3">Info Marketing</th>
                            <th class="py-3 text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($clientes as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        {{-- LOGO PARA EMPRESAS O AVATAR PARA PERSONAS --}}
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold shadow-sm me-3" 
                                             style="width: 40px; height: 40px; background-color: {{ $item->tipo_documento == 'RUC' ? '#212124' : ($item->genero == 'Masculino' ? '#4a6fa5' : 'var(--belen-grey)') }};">
                                            @if($item->tipo_documento == 'RUC')
                                                <i class="bi bi-building"></i>
                                            @else
                                                {{ substr($item->nombre, 0, 1) }}{{ substr($item->apellido, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="d-flex flex-column">
                                            <span class="fw-bold text-dark">{{ $item->nombre }} {{ $item->apellido }}</span>
                                            @if($item->tipo_documento == 'RUC')
                                                <small class="text-muted"><i class="bi bi-briefcase me-1"></i> Cliente Corporativo</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="badge bg-light text-dark border mb-1" style="width: fit-content;">{{ $item->tipo_documento }}</span>
                                        <span class="small font-monospace">{{ $item->numero_documento }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($item->telefono)
                                        <div class="text-dark small"><i class="bi bi-whatsapp text-success me-1"></i> {{ $item->telefono }}</div>
                                    @endif
                                    @if($item->email)
                                        <small class="text-muted">{{ Str::limit($item->email, 20) }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($item->procedencia)
                                        @if($item->procedencia == 'Cliente Antiguo' || $item->procedencia == 'Sistema Anterior')
                                            <span class="badge bg-warning bg-opacity-10 text-warning border border-warning mb-1">
                                                <i class="bi bi-star-fill me-1"></i>Cliente Antiguo
                                            </span>
                                        @else
                                            @php
                                                $iconos = [
                                                    'Redes Sociales' => 'bi-instagram',
                                                    'Referencia' => 'bi-person-hearts',
                                                    'Volanteo' => 'bi-megaphone',
                                                    'Ubicacion' => 'bi-geo-alt',
                                                    'Google' => 'bi-google'
                                                ];
                                                $icono = $iconos[$item->procedencia] ?? 'bi-info-circle';
                                            @endphp
                                            <small class="d-block text-secondary"><i class="bi {{ $icono }} me-1"></i> {{ $item->procedencia }}</small>
                                        @endif
                                    @endif
                                    @if($item->fecha_nacimiento)<small class="text-muted d-block"><i class="bi bi-cake2 me-1"></i> {{ $item->fecha_nacimiento->format('d/m/Y') }}</small>@endif
                                </td>
                                <td class="text-end pe-4">
                                    <button wire:click="edit({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm me-1" title="Editar cliente"><i class="bi bi-pencil-square text-primary"></i></button>
                                    <button wire:confirm="¿Seguro que desea eliminar este cliente?" wire:click="delete({{ $item->id }})" class="btn btn-sm btn-light border shadow-sm"><i class="bi bi-trash text-danger" title="Eliminar cliente"></i></button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-5 text-muted">No hay clientes registrados.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top-0 py-3">{{ $clientes->links() }}</div>
    </div>

    {{-- MODAL CON BÚSQUEDA API --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(2px);" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header px-4 py-3" style="background-color: var(--belen-dark); color: white;">
                    <h5 class="modal-title fw-light text-uppercase" style="letter-spacing: 1px;">
                        {{ $cliente_id ? 'Editar Cliente' : 'Nuevo Cliente' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>

                <div class="modal-body p-4 bg-light">
                    <form>
                        <div class="row g-3">
                            
                            {{-- SECCIÓN DE BÚSQUEDA --}}
                            <div class="col-12"><label class="form-label fw-bold text-secondary small text-uppercase border-bottom w-100 pb-1">Búsqueda por Documento</label></div>

                            <div class="col-md-3">
                                <label class="form-label">Tipo Doc.</label>
                                <select wire:model.live="tipo_documento" class="form-select shadow-sm bg-white fw-bold text-dark">
                                    <option value="DNI">DNI</option>
                                    <option value="RUC">RUC</option>
                                    <option value="CE">C.E.</option>
                                    <option value="PAS">Pasaporte</option>
                                </select>
                            </div>

                            <div class="col-md-5">
                                <label class="form-label">
                                    Número Documento <span class="text-danger">*</span>
                                </label>
                                <div class="input-group shadow-sm">
                                    <input type="text" wire:model="numero_documento" class="form-control @error('numero_documento') is-invalid @enderror" placeholder="Ingrese número...">
                                    
                                    {{-- BOTÓN BUSCAR CON API --}}
                                    @if($tipo_documento == 'DNI' || $tipo_documento == 'RUC')
                                        <button wire:click="buscarDocumento" wire:loading.attr="disabled" type="button" class="btn btn-primary text-dark fw-bold">
                                            <span wire:loading.remove wire:target="buscarDocumento"><i class="bi bi-search"></i> Buscar</span>
                                            <span wire:loading wire:target="buscarDocumento"><span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span></span>
                                        </button>
                                    @endif
                                </div>
                                @error('numero_documento') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            <div class="col-12"></div> {{-- Salto de línea --}}

                            <div class="col-md-{{ $tipo_documento == 'RUC' ? '12' : '6' }}">
                                <label class="form-label">
                                    {{ $tipo_documento == 'RUC' ? 'Razón Social' : 'Nombres' }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" wire:model="nombre" class="form-control shadow-sm @error('nombre') is-invalid @enderror">
                                @error('nombre') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            @if($tipo_documento != 'RUC')
                                <div class="col-md-6">
                                    <label class="form-label">Apellidos</label>
                                    <input type="text" wire:model="apellido" class="form-control shadow-sm">
                                </div>
                                
                                <div class="col-md-3">
                                    <label class="form-label">Fecha Nac.</label>
                                    <input type="date" wire:model="fecha_nacimiento" class="form-control shadow-sm text-secondary">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">Género</label>
                                    <select wire:model="genero" class="form-select shadow-sm bg-white">
                                        <option value="Femenino">Mujer</option>
                                        <option value="Masculino">Hombre</option>
                                    </select>
                                </div>
                            @endif

                            {{-- CONTACTO --}}
                            <div class="col-12 mt-4">
                                <label class="form-label fw-bold text-secondary small text-uppercase border-bottom w-100 pb-1">Contacto & Ubicación</label>
                            </div>

                            {{-- FILA 1: Teléfono y Email --}}
                            <div class="col-md-6">
                                <label class="form-label">Teléfono</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-whatsapp"></i></span>
                                    <input type="text" wire:model="telefono" class="form-control border-start-0" placeholder="999 999 999">
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Correo Electrónico</label>
                                <div class="input-group shadow-sm">
                                    <span class="input-group-text bg-white text-muted border-end-0"><i class="bi bi-envelope"></i></span>
                                    <input type="email" wire:model="email" class="form-control border-start-0 @error('email') is-invalid @enderror" placeholder="cliente@correo.com">
                                </div>
                                @error('email') <span class="text-danger small">{{ $message }}</span> @enderror
                            </div>

                            {{-- FILA 2: Dirección (Más espacio) y Procedencia --}}
                            <div class="col-md-8">
                                <label class="form-label">Dirección</label>
                                <input type="text" wire:model="direccion" class="form-control shadow-sm" placeholder="{{ $tipo_documento == 'RUC' ? 'Dirección Fiscal...' : 'Dirección...' }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">¿Cómo nos conoció?</label>
                                <select wire:model="procedencia" class="form-select shadow-sm bg-white">
                                    <option value="">-- Seleccionar --</option>
                                    <option value="Cliente Antiguo">⭐ Cliente Antiguo</option>
                                    <option value="Redes Sociales">Redes Sociales</option>
                                    <option value="Referencia">Referido</option>
                                    <option value="Volanteo">Volanteo</option>
                                    <option value="Ubicacion">Pasaba por aquí</option>
                                    <option value="Google">Google / Web</option>
                                </select>
                            </div>

                        </div>
                    </form>
                </div>
                
                <div class="modal-footer bg-white py-3">
                    <button wire:click="closeModal()" type="button" class="btn btn-light border">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary px-4 shadow-sm text-dark fw-bold"><i class="bi bi-save me-1"></i> Guardar</button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>