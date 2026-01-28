<div>
    <div class="row">
        <div class="col-12">
            
            {{-- 1. MENSAJES DE ALERTA --}}
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show">
                    {{ session('message') }} <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            {{-- 2. MONITOR DE ESTILISTAS (NUEVO CÓDIGO AQUÍ) --}}
            {{-- Agregamos wire:poll.30s para que los minutos "hace X min" se actualicen solos cada 30 seg --}}
            <div class="row mb-4" wire:poll.30s>
                <div class="col-12">
                    <h5 class="mb-3 text-secondary"><i class="bi bi-person-workspace"></i> Monitor de Personal</h5>
                </div>
            
                @foreach($monitorEstilistas as $estilista)
                    @php
                        // Verificamos si tiene atenciones activas
                        $ocupado = $estilista->atencionesEnCurso->count() > 0;
                        $atencion = $ocupado ? $estilista->atencionesEnCurso->first() : null;
                    @endphp
            
                    <div class="col-md-3 col-sm-6 mb-3">
                        <div class="card h-100 shadow-sm {{ $ocupado ? 'border-warning' : 'border-success' }}" style="border-top-width: 4px;">
                            <div class="card-body p-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 text-truncate" title="{{ $estilista->nombre }}">
                                        {{ $estilista->nombre }}
                                    </h6>
                                    @if($ocupado)
                                        <span class="badge bg-warning text-dark blink_me">
                                            <i class="bi bi-scissors"></i> OCUPADO
                                        </span>
                                    @else
                                        <span class="badge bg-success">
                                            <i class="bi bi-check-circle"></i> LIBRE
                                        </span>
                                    @endif
                                </div>
            
                                @if($ocupado && $atencion)
                                    <div class="small mt-2 bg-light p-2 rounded">
                                        <strong class="d-block text-primary text-truncate">
                                            {{ $atencion->servicio->nombre ?? 'Servicio' }}
                                        </strong>
                                        <span class="text-muted d-block text-truncate">
                                            <i class="bi bi-person"></i> {{ $atencion->turno->cliente->nombre ?? 'Cliente' }}
                                        </span>
                                        
                                        <div class="mt-2 pt-2 border-top text-muted d-flex justify-content-between align-items-center" style="font-size: 0.85rem;">
                                            <span><i class="bi bi-clock"></i> {{ $atencion->created_at->format('H:i') }}</span>
                                            {{-- CÓDIGO CORREGIDO (Sin decimales) --}}
                                            <span class="fw-bold text-dark bg-white px-1 border rounded">
                                                {{ $atencion->created_at->diffForHumans(null, true) }}
                                            </span>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-3 opacity-50">
                                        <i class="bi bi-emoji-smile fs-4"></i><br>
                                        <small>Disponible</small>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            {{-- FIN MONITOR --}}

            {{-- 3. TARJETA PRINCIPAL (TU CÓDIGO ORIGINAL) --}}
            <div class="card card-outline card-primary">
                <div class="card-header">
                    <h3 class="card-title">Turnos Activos (En Atención)</h3>
                    <div class="card-tools">
                         {{-- Aquí podrías poner un filtro de fecha --}}
                    </div>
                </div>

                <div class="card-body">
                    <button wire:click="create()" class="btn btn-primary mb-3">
                        <i class="bi bi-calendar-plus"></i> Nueva Atención
                    </button>

                    <div class="table-responsive">
                        <table class="table table-hover table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th># Turno</th>
                                    <th>Cliente</th>
                                    <th>Inicio</th>
                                    <th>Servicios / Estilistas</th>
                                    <th>Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($turnos as $turno)
                                    <tr>
                                        <td>{{ str_pad($turno->id, 5, '0', STR_PAD_LEFT) }}</td>
                                        <td class="fw-bold">{{ $turno->cliente->nombre }} {{ $turno->cliente->apellido }}</td>
                                        <td>{{ $turno->hora_inicio->format('h:i a') }}</td>
                                        <td>
                                            <ul class="mb-0 ps-3">
                                                @foreach($turno->servicios as $detalle)
                                                    <li>
                                                        {{ $detalle->servicio->nombre }} 
                                                        <span class="badge bg-secondary fw-normal">{{ $detalle->estilista->nombre }}</span>
                                                    </li>
                                                @endforeach
                                            </ul>
                                        </td>
                                        <td><span class="badge bg-success">En Proceso</span></td>
                                        <td class="text-center">
                                            {{-- 1. BOTÓN EDITAR / AGREGAR SERVICIO (NUEVO) --}}
                                            <button wire:click="edit({{ $turno->id }})" class="btn btn-sm btn-info text-white me-1" title="Editar / Agregar Servicios">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>

                                            {{-- 2. BOTÓN COBRAR (Ahora redirigiremos al POS más adelante, por ahora link simple) --}}
                                            <a href="{{ route('admin.pos', $turno->id) }}" class="btn btn-sm btn-success me-1" title="Ir a Caja">
                                                <i class="bi bi-cash-coin"></i> Cobrar
                                            </a>
                                            
                                            {{-- 3. BOTÓN CANCELAR --}}
                                            <button wire:confirm="¿Cancelar este turno?" wire:click="cancelar({{ $turno->id }})" class="btn btn-sm btn-danger" title="Cancelar">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No hay clientes atendiéndose en este momento.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    
                    {{-- Paginación --}}
                    <div class="mt-3">
                        {{ $turnos->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. MODAL (TU CÓDIGO ORIGINAL) --}}
    @if($isOpen)
    <div class="modal fade show d-block" style="background: rgba(0,0,0,0.5);" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    {{-- TÍTULO DINÁMICO --}}
                    <h5 class="modal-title">
                        {{ $turno_id ? '✏️ Editar Atención / Agregar Servicios' : '✨ Registrar Entrada de Cliente' }}
                    </h5>
                    <button wire:click="closeModal()" type="button" class="btn-close btn-close-white"></button>
                </div>
                <div class="modal-body">
                    <form>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Cliente</label>
                            <select wire:model="id_cliente" class="form-select form-select-lg">
                                <option value="">-- Seleccionar Cliente --</option>
                                @foreach($clientes as $c)
                                    <option value="{{ $c->id }}">{{ $c->nombre }} {{ $c->apellido }}</option>
                                @endforeach
                            </select>
                            @error('id_cliente') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        <div class="p-3 bg-light rounded border mb-3">
                            <label class="form-label text-primary fw-bold mb-3"><i class="bi bi-list-check"></i> Servicios a realizar</label>

                            <table class="table table-sm table-borderless">
                                <thead class="text-muted small text-uppercase">
                                    <tr>
                                        <th width="40%">Servicio</th>
                                        <th width="35%">Estilista</th>
                                        <th width="20%">Precio (S/)</th>
                                        <th width="5%"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($items as $index => $item)
                                    <tr>
                                        <td>
                                            <select wire:model.live="items.{{ $index }}.servicio_id" class="form-select form-select-sm">
                                                <option value="">-- Servicio --</option>
                                                @foreach($servicios as $s)
                                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <select wire:model="items.{{ $index }}.estilista_id" class="form-select form-select-sm">
                                                <option value="">-- Estilista --</option>
                                                
                                                @foreach($estilistas as $e)
                                                    @php
                                                        // 1. ¿Está ocupado en general?
                                                        $isBusy = $e->atencionesEnCurso->count() > 0;
                                                        
                                                        // 2. ¿Está ocupado CONMIGO (en este mismo turno)?
                                                        // Si estoy editando el turno 100, y él está en el turno 100, para mí NO está ocupado, es mi estilista.
                                                        $busyWithMe = false;
                                                        if($turno_id && $isBusy) {
                                                            // Verificamos si alguna de sus atenciones pertenece a este turno actual
                                                            $busyWithMe = $e->atencionesEnCurso->where('id_turno', $turno_id)->count() > 0;
                                                        }

                                                        // Lógica de Texto Visual
                                                        $estadoTexto = '';
                                                        $style = '';

                                                        if ($isBusy) {
                                                            if ($busyWithMe) {
                                                                $estadoTexto = '(Asignado a este turno)';
                                                                $style = 'font-weight: bold; color: green;';
                                                            } else {
                                                                $estadoTexto = '(🔴 OCUPADO)';
                                                                $style = 'color: red;';
                                                            }
                                                        } else {
                                                            $estadoTexto = '(Libre)';
                                                        }
                                                    @endphp

                                                    <option value="{{ $e->id }}" style="{{ $style }}">
                                                        {{ $e->nombre }} {{ $estadoTexto }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" wire:model="items.{{ $index }}.precio" class="form-control form-control-sm">
                                        </td>
                                        <td class="text-center">
                                            <button wire:click="removeItem({{ $index }})" type="button" class="btn btn-xs btn-outline-danger border-0">
                                                <i class="bi bi-trash-fill"></i>
                                            </button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            
                            <button wire:click="addItem()" type="button" class="btn btn-sm btn-link text-decoration-none">
                                <i class="bi bi-plus-circle"></i> Agregar otro servicio
                            </button>
                            
                            @error('items') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Observaciones (Opcional)</label>
                            <textarea wire:model="observaciones" class="form-control" rows="2" placeholder="Ej: Cliente trae su propio tinte..."></textarea>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button wire:click="closeModal()" type="button" class="btn btn-secondary">Cancelar</button>
                    <button wire:click="store()" type="button" class="btn btn-primary">
                        <i class="bi bi-check-circle"></i> Registrar Atención
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>