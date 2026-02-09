<div>
    {{-- HEADER CON FILTROS --}}
    <div class="card mb-4 shadow-sm border-0">
        <div class="card-body">
            <div class="row align-items-end g-3">
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Fecha Inicio</label>
                    <input type="date" class="form-control" wire:model="fechaInicio">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold small text-muted text-uppercase">Fecha Fin</label>
                    <input type="date" class="form-control" wire:model="fechaFin">
                </div>
                <div class="col-md-4 text-end">
                    <p class="text-muted small mb-1">Periodo seleccionado</p>
                    <h6 class="fw-bold mb-0">{{ \Carbon\Carbon::parse($fechaInicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fechaFin)->format('d/m/Y') }}</h6>
                </div>
            </div>
        </div>
    </div>

    {{-- GRID DE REPORTES --}}
    <div class="row g-4">
        
        {{-- REPORTE DIARIO DE CAJA (DESTACADO) --}}
        <div class="col-12">
            <div class="card border-primary border-2 shadow-lg">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-square bg-primary text-white rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                <i class="bi bi-calendar-day fs-3"></i>
                            </div>
                            <h4 class="card-title mb-0 fw-bold text-primary">Reporte Diario de Caja</h4>
                        </div>
                        <button wire:click="descargarCajaDiariaPDF" class="btn btn-primary btn-lg">
                            <i class="bi bi-download me-2"></i> Descargar Hoy
                        </button>
                    </div>
                    <div class="alert alert-info mb-0" role="alert">
                        <i class="bi bi-info-circle me-2"></i>
                        <strong>Uso diario:</strong> Descarga el resumen consolidado del día actual con todas las cajas.
                    </div>
                </div>
            </div>
        </div>

        {{-- REPORTE DE RENTABILIDAD --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-primary bg-opacity-10 text-primary rounded-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-graph-up-arrow fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Reporte de Rentabilidad</h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Análisis completo de ganancias. Incluye: Rentabilidad de servicios y productos, costos, ganancias netas, top 5 servicios y productos.
                    </p>
                    <div class="d-flex gap-2">
                        <button wire:click="descargarRentabilidadPDF" class="btn btn-danger btn-sm flex-fill">
                            <i class="bi bi-file-pdf me-1"></i> Descargar PDF
                        </button>
                        <button wire:click="descargarRentabilidadExcel" class="btn btn-success btn-sm flex-fill" disabled>
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-check-circle me-1"></i> PDF disponible | Excel próximamente
                    </small>
                </div>
            </div>
        </div>

        {{-- REPORTE DE VENTAS --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-success bg-opacity-10 text-success rounded-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-cash-coin fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Reporte de Ventas</h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Detalle completo de todas las transacciones. Incluye: Fecha, cliente, servicios/productos vendidos, total, método de pago y estilista.
                    </p>
                    <div class="d-flex gap-2">
                        <button wire:click="descargarVentasPDF" class="btn btn-danger btn-sm flex-fill">
                            <i class="bi bi-file-pdf me-1"></i> Descargar PDF
                        </button>
                        <button wire:click="descargarVentasExcel" class="btn btn-success btn-sm flex-fill" disabled>
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-check-circle me-1"></i> PDF disponible | Excel próximamente
                    </small>
                </div>
            </div>
        </div>

        {{-- REPORTE DE CLIENTES --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-warning bg-opacity-10 text-warning rounded-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Reporte de Clientes</h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Top 20 clientes más frecuentes. Incluye: Nombre, edad, cantidad de visitas, total gastado, procedencia y última visita.
                    </p>
                    <div class="d-flex gap-2">
                        <button wire:click="descargarClientesPDF" class="btn btn-danger btn-sm flex-fill">
                            <i class="bi bi-file-pdf me-1"></i> Descargar PDF
                        </button>
                        <button wire:click="descargarClientesExcel" class="btn btn-success btn-sm flex-fill" disabled>
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-check-circle me-1"></i> PDF disponible | Excel próximamente
                    </small>
                </div>
            </div>
        </div>

        {{-- REPORTE DE CAJA MENSUAL --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-info bg-opacity-10 text-info rounded-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Reporte Mensual de Caja</h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Detalle completo del periodo seleccionado. Incluye: Aperturas/cierres por cajera, salidas de dinero con descripción y totales del periodo.
                    </p>
                    <div class="d-flex gap-2">
                        <button wire:click="descargarCajaPDF" class="btn btn-danger btn-sm flex-fill">
                            <i class="bi bi-file-pdf me-1"></i> Descargar PDF
                        </button>
                        <button class="btn btn-success btn-sm flex-fill" disabled>
                            <i class="bi bi-file-excel me-1"></i> Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2">
                        <i class="bi bi-check-circle me-1"></i> PDF disponible | Excel próximamente
                    </small>
                </div>
            </div>
        </div>

    </div>

    {{-- NOTA INFORMATIVA --}}
    <div class="alert alert-success border-0 shadow-sm mt-4" role="alert">
        <div class="d-flex align-items-start">
            <i class="bi bi-check-circle-fill fs-4 me-3"></i>
            <div>
                <h6 class="alert-heading fw-bold mb-2">¡Módulo de Reportes Completo!</h6>
                <p class="mb-2 small">
                    <strong>Reporte Diario:</strong> Descarga el resumen del día actual con un solo clic (no requiere seleccionar fechas).
                </p>
                <p class="mb-0 small">
                    <strong>Reportes con Rango:</strong> Selecciona el periodo deseado y descarga: <strong>Rentabilidad</strong>, <strong>Ventas</strong>, 
                    <strong>Clientes</strong> y <strong>Caja Mensual</strong>.
                </p>
            </div>
        </div>
    </div>

</div>
