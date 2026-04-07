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
            <div class="card border-2 shadow-lg" style="border-color: var(--belen-cream) !important;">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="d-flex align-items-center">
                            <div class="icon-square text-white rounded-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center; background-color: var(--belen-dark);">
                                <i class="bi bi-calendar-day fs-3"></i>
                            </div>
                            <h4 class="card-title mb-0 fw-bold" style="color: var(--belen-dark);">Reporte Diario de Caja</h4>
                        </div>
                        <button wire:click="descargarCajaDiariaPDF" class="btn btn-primary btn-lg text-dark fw-bold">
                            <i class="bi bi-download me-2"></i> Descargar Hoy
                        </button>
                    </div>
                    <div class="alert alert-light border mb-0" role="alert">
                        <i class="bi bi-info-circle me-2 text-dark"></i>
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
                        Análisis de ganancias netas. Compara ingresos de servicios vs costo de insumos, y ventas de productos vs costo de compra.
                    </p>

                    <div class="d-flex gap-2">
                        {{-- BOTÓN PDF --}}
                        <button wire:click="descargarRentabilidadPDF"
                                wire:loading.attr="disabled"
                                wire:target="descargarRentabilidadPDF"
                                class="btn btn-danger btn-sm flex-fill d-flex align-items-center justify-content-center">
                            <i wire:loading.remove wire:target="descargarRentabilidadPDF" class="bi bi-file-pdf me-2"></i>
                            <span wire:loading wire:target="descargarRentabilidadPDF" class="spinner-border spinner-border-sm me-2"></span>
                            PDF
                        </button>

                        {{-- BOTÓN EXCEL (CSV) --}}
                        <button wire:click="descargarRentabilidadCSV"
                                wire:loading.attr="disabled"
                                wire:target="descargarRentabilidadCSV"
                                class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center">
                            <i wire:loading.remove wire:target="descargarRentabilidadCSV" class="bi bi-file-excel me-2"></i>
                            <span wire:loading wire:target="descargarRentabilidadCSV" class="spinner-border spinner-border-sm me-2"></span>
                            Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle me-1"></i> Selecciona el formato de tu preferencia
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
                        {{-- BOTÓN PDF --}}
                        <button wire:click="descargarVentasPDF"
                                wire:loading.attr="disabled"
                                wire:target="descargarVentasPDF"
                                class="btn btn-danger btn-sm flex-fill d-flex align-items-center justify-content-center">

                            {{-- Ícono normal (se oculta al cargar) --}}
                            <i wire:loading.remove wire:target="descargarVentasPDF" class="bi bi-file-pdf me-2"></i>

                            {{-- Spinner de carga (aparece al cargar) --}}
                            <span wire:loading wire:target="descargarVentasPDF" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>

                            Descargar PDF
                        </button>

                        {{-- BOTÓN EXCEL (CSV) --}}
                        <button wire:click="descargarVentasCSV"
                                wire:loading.attr="disabled"
                                wire:target="descargarVentasCSV"
                                class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center">

                            {{-- Ícono normal (se oculta al cargar) --}}
                            <i wire:loading.remove wire:target="descargarVentasCSV" class="bi bi-file-excel me-2"></i>

                            {{-- Spinner de carga (aparece al cargar) --}}
                            <span wire:loading wire:target="descargarVentasCSV" class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>

                            Descargar Excel
                        </button>
                    </div>

                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle me-1"></i> Selecciona el formato de tu preferencia
                    </small>
                </div>
            </div>
        </div>

        {{-- REPORTE DE CLIENTES --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-info bg-opacity-10 text-info rounded-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-people fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Reporte de Clientes</h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Estadísticas de captación y retención. Incluye: Top clientes frecuentes, canales de adquisición y promedios de visita.
                    </p>

                    <div class="d-flex gap-2">
                        {{-- BOTÓN PDF --}}
                        <button wire:click="descargarClientesPDF"
                                wire:loading.attr="disabled"
                                wire:target="descargarClientesPDF"
                                class="btn btn-danger btn-sm flex-fill d-flex align-items-center justify-content-center">
                            <i wire:loading.remove wire:target="descargarClientesPDF" class="bi bi-file-pdf me-2"></i>
                            <span wire:loading wire:target="descargarClientesPDF" class="spinner-border spinner-border-sm me-2"></span>
                            PDF
                        </button>

                        {{-- BOTÓN EXCEL (CSV) --}}
                        <button wire:click="descargarClientesCSV"
                                wire:loading.attr="disabled"
                                wire:target="descargarClientesCSV"
                                class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center">
                            <i wire:loading.remove wire:target="descargarClientesCSV" class="bi bi-file-excel me-2"></i>
                            <span wire:loading wire:target="descargarClientesCSV" class="spinner-border spinner-border-sm me-2"></span>
                            Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle me-1"></i> Selecciona el formato de tu preferencia
                    </small>
                </div>
            </div>
        </div>

        {{-- REPORTE DE CAJA --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="icon-square bg-warning bg-opacity-10 text-warning rounded-3 me-3" style="width: 48px; height: 48px; display: flex; align-items: center; justify-content: center;">
                            <i class="bi bi-safe fs-4"></i>
                        </div>
                        <h5 class="card-title mb-0 fw-bold">Reporte de Caja Mensual</h5>
                    </div>
                    <p class="text-muted small mb-3">
                        Auditoría de movimientos de dinero. Incluye: Aperturas, cierres, arqueos (monto real vs sistema), diferencias y egresos detallados.
                    </p>

                    <div class="d-flex gap-2">
                        {{-- BOTÓN PDF --}}
                        <button wire:click="descargarCajaPDF"
                                wire:loading.attr="disabled"
                                wire:target="descargarCajaPDF"
                                class="btn btn-danger btn-sm flex-fill d-flex align-items-center justify-content-center">
                            <i wire:loading.remove wire:target="descargarCajaPDF" class="bi bi-file-pdf me-2"></i>
                            <span wire:loading wire:target="descargarCajaPDF" class="spinner-border spinner-border-sm me-2"></span>
                            PDF
                        </button>

                        {{-- BOTÓN EXCEL (CSV) --}}
                        <button wire:click="descargarCajaCSV"
                                wire:loading.attr="disabled"
                                wire:target="descargarCajaCSV"
                                class="btn btn-success btn-sm flex-fill d-flex align-items-center justify-content-center">
                            <i wire:loading.remove wire:target="descargarCajaCSV" class="bi bi-file-excel me-2"></i>
                            <span wire:loading wire:target="descargarCajaCSV" class="spinner-border spinner-border-sm me-2"></span>
                            Excel
                        </button>
                    </div>
                    <small class="text-muted d-block mt-2 text-center">
                        <i class="bi bi-info-circle me-1"></i> Selecciona el formato de tu preferencia
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
