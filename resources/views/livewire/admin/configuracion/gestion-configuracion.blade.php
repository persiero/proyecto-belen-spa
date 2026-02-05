<div>
    <div class="row">
        
        {{-- COLUMNA 1: DATOS NEGOCIO + API TOKEN --}}
        <div class="col-md-6">
            <div class="card card-primary card-outline shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title fw-bold text-dark"><i class="bi bi-shop text-primary"></i> Datos del Negocio</h3>
                </div>
                <div class="card-body">
                    @if (session()->has('message_negocio'))
                        <div class="alert alert-success py-2 shadow-sm border-0 mb-3"><i class="bi bi-check-circle"></i> {{ session('message_negocio') }}</div>
                    @endif

                    <form wire:submit.prevent="guardarNegocio">
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Nombre Comercial</label>
                            <input type="text" wire:model="nombre_comercial" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">RUC (Emisor)</label>
                            <input type="text" wire:model="ruc" class="form-control" maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Dirección Fiscal</label>
                            <input type="text" wire:model="direccion" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small text-secondary">Teléfono</label>
                                <input type="text" wire:model="telefono" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small text-secondary">Email</label>
                                <input type="email" wire:model="email" class="form-control">
                            </div>
                        </div>

                        {{-- SECCIÓN API TOKEN --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <label class="form-label fw-bold text-dark mb-1">
                                <i class="bi bi-key-fill text-warning"></i> Token API (Consultas DNI/RUC)
                            </label>
                            <div class="input-group">
                                <input type="text" wire:model="api_token" class="form-control border-end-0" placeholder="Pegar token aquí...">
                                <span class="input-group-text bg-white border-start-0 text-muted">
                                    <i class="bi bi-shield-lock"></i>
                                </span>
                            </div>
                            <small class="text-muted" style="font-size: 0.75rem;">Este token permite consultar datos de RENIEC y SUNAT.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">
                            <i class="bi bi-save me-1"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- COLUMNA 2: FACTURACIÓN (Sin cambios funcionales, solo estilo visual mejorado si gustas pegar este) --}}
        <div class="col-md-6">
            <div class="card card-danger card-outline shadow-sm border-0">
                <div class="card-header bg-white">
                    <h3 class="card-title fw-bold text-dark"><i class="bi bi-cloud-arrow-up text-danger"></i> Facturación SUNAT</h3>
                </div>
                <div class="card-body">
                    @if (session()->has('message_tributaria'))
                        <div class="alert alert-success py-2 shadow-sm border-0 mb-3"><i class="bi bi-check-circle"></i> {{ session('message_tributaria') }}</div>
                    @endif
                    @if (session()->has('error_tributaria'))
                        <div class="alert alert-danger py-2 shadow-sm border-0 mb-3"><i class="bi bi-x-circle"></i> {{ session('error_tributaria') }}</div>
                    @endif

                    <form wire:submit.prevent="guardarTributaria">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small text-secondary">IGV (%)</label>
                                <input type="number" wire:model="igv_porcentaje" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small text-secondary">Entorno</label>
                                <select wire:model="modo" class="form-select">
                                    <option value="beta">🟡 BETA (Pruebas)</option>
                                    <option value="produccion">🟢 PRODUCCIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 p-2 border rounded bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="emision_automatica_cpe" id="autoCpe">
                                <label class="form-check-label fw-bold small" for="autoCpe">Emitir CPE Automáticamente al cobrar</label>
                            </div>
                        </div>

                        <hr class="text-muted">
                        <h6 class="text-dark fw-bold small text-uppercase">Credenciales SOL</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Usuario SOL (RUC + Usuario)</label>
                            <input type="text" wire:model="usuario_sol" class="form-control" placeholder="Ej: 20123456789MODDATOS">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Clave SOL</label>
                            <input type="password" wire:model="clave_sol" class="form-control">
                        </div>
                        
                        <hr class="text-muted">
                        <h6 class="text-dark fw-bold small text-uppercase">Certificado Digital</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small text-secondary">Archivo (.pem / .pfx)</label>
                            <input type="file" wire:model="certificado_file" class="form-control">
                            <div wire:loading wire:target="certificado_file" class="text-primary small mt-1">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subiendo...
                            </div>
                            @error('certificado_file') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($certificado_path)
                            <div class="alert alert-success border-0 bg-opacity-10 bg-success small mb-3">
                                <i class="bi bi-check-circle-fill"></i> Certificado cargado: <strong>{{ basename($certificado_path) }}</strong>
                            </div>
                        @else
                            <div class="alert alert-warning border-0 bg-opacity-10 bg-warning small mb-3">
                                <i class="bi bi-exclamation-triangle-fill"></i> Falta cargar certificado.
                            </div>
                        @endif

                        <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm">
                            <i class="bi bi-cloud-upload me-1"></i> Actualizar Configuración SUNAT
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>