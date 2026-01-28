<div>
    <div class="card card-outline card-primary no-print">
        <div class="card-header">
            <h3 class="card-title">Filtros de Búsqueda</h3>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Fecha Inicio</label>
                    <input type="date" wire:model.live="fecha_inicio" class="form-control">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Fecha Fin</label>
                    <input type="date" wire:model.live="fecha_fin" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estilista</label>
                    <select wire:model.live="estilista_id" class="form-select">
                        <option value="">-- TODOS --</option>
                        @foreach($estilistas as $e)
                            <option value="{{ $e->id }}">{{ $e->nombre }} ({{ $e->porcentaje_comision }}%)</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button onclick="window.print()" class="btn btn-secondary w-100">
                        <i class="bi bi-printer"></i> Imprimir
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <h4>REPORTE DE COMISIONES</h4>
                <small class="text-muted">Del {{ $fecha_inicio }} al {{ $fecha_fin }}</small>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Estilista</th>
                            <th class="text-center">Servicios Realizados</th>
                            <th class="text-end">Total Vendido (S/)</th>
                            <th class="text-center">% Pactado</th>
                            <th class="text-end bg-warning text-dark">A PAGAR (Comisión)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($reporte as $row)
                            <tr>
                                <td class="fw-bold">{{ $row['nombre'] }}</td>
                                <td class="text-center">{{ $row['servicios_count'] }}</td>
                                <td class="text-end">{{ number_format($row['total_vendido'], 2) }}</td>
                                <td class="text-center">{{ $row['porcentaje_base'] }}%</td>
                                <td class="text-end fw-bold bg-light">S/ {{ number_format($row['total_comision'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-3">No se encontraron servicios en este periodo.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="fs-5">
                            <td colspan="2" class="text-end fw-bold">TOTALES:</td>
                            <td class="text-end fw-bold">S/ {{ number_format($totalGeneral, 2) }}</td>
                            <td></td>
                            <td class="text-end fw-bold text-success">S/ {{ number_format($comisionGeneral, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>