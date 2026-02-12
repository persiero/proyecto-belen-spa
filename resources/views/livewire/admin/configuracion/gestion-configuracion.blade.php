<div>
    {{-- HEADER CON TÍTULO --}}
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><i class="bi bi-gear me-2"></i> Configuración del Sistema</h4>
            <small class="text-muted">Administra los datos del negocio y parámetros de facturación</small>
        </div>
    </div>

    <div class="row g-3">
        
        {{-- COLUMNA 1: DATOS NEGOCIO + API TOKEN --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-top border-4" style="border-color: var(--belen-cream) !important;">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-shop me-2"></i> Datos del Negocio</h5>
                </div>
                <div class="card-body p-4">
                    @if (session()->has('message_negocio'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message_negocio') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="guardarNegocio">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">NOMBRE COMERCIAL</label>
                            <input type="text" wire:model="nombre_comercial" class="form-control shadow-sm" placeholder="Ej: Belen Spa">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">RUC (EMISOR)</label>
                            <input type="text" wire:model="ruc" class="form-control shadow-sm" maxlength="11" placeholder="20123456789">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">DIRECCIÓN FISCAL</label>
                            <input type="text" wire:model="direccion" class="form-control shadow-sm" placeholder="Av. Principal 123, Lima">
                        </div>
                        <div class="row g-3">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">TELÉFONO</label>
                                <input type="text" wire:model="telefono" class="form-control shadow-sm" placeholder="987654321">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">EMAIL</label>
                                <input type="email" wire:model="email" class="form-control shadow-sm" placeholder="contacto@empresa.com">
                            </div>
                        </div>

                        {{-- SECCIÓN API TOKEN --}}
                        <div class="bg-light p-3 rounded-3 mb-4 border">
                            <label class="form-label fw-bold text-dark mb-2">
                                <i class="bi bi-key-fill text-warning me-1"></i> Token API (Consultas DNI/RUC)
                            </label>
                            <div class="input-group shadow-sm">
                                <input type="text" wire:model="api_token" class="form-control border-end-0" placeholder="Pegar token aquí...">
                                <span class="input-group-text bg-white border-start-0 text-muted">
                                    <i class="bi bi-shield-lock"></i>
                                </span>
                            </div>
                            <small class="text-muted d-block mt-2" style="font-size: 0.75rem;">Este token permite consultar datos de RENIEC y SUNAT.</small>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm py-2">
                            <i class="bi bi-check-circle-fill me-2"></i> Guardar Cambios
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- COLUMNA 2: FACTURACIÓN --}}
        <div class="col-md-6">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-header bg-white py-3 border-top border-4 border-danger">
                    <h5 class="fw-bold mb-0 text-dark"><i class="bi bi-cloud-arrow-up text-danger me-2"></i> Facturación SUNAT</h5>
                </div>
                <div class="card-body p-4">
                    @if (session()->has('message_tributaria'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 bg-success bg-opacity-10 text-success fw-bold mb-3">
                            <i class="bi bi-check-circle-fill me-2"></i> {{ session('message_tributaria') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif
                    @if (session()->has('error_tributaria'))
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 bg-danger bg-opacity-10 text-danger fw-bold mb-3">
                            <i class="bi bi-x-circle-fill me-2"></i> {{ session('error_tributaria') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form wire:submit.prevent="guardarTributaria">
                        <div class="row g-3">
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">IGV (%)</label>
                                <input type="number" wire:model="igv_porcentaje" class="form-control shadow-sm" placeholder="18">
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label fw-bold small text-secondary">ENTORNO</label>
                                <select wire:model="modo" class="form-select shadow-sm">
                                    <option value="beta">🟡 BETA (Pruebas)</option>
                                    <option value="produccion">🟢 PRODUCCIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3 p-3 border rounded bg-light">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="emision_automatica_cpe" id="autoCpe">
                                <label class="form-check-label fw-bold small" for="autoCpe">Emitir CPE Automáticamente al cobrar</label>
                            </div>
                        </div>

                        <hr class="text-muted my-4">
                        <h6 class="text-dark fw-bold small text-uppercase mb-3"><i class="bi bi-person-badge me-1"></i> Credenciales SOL</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">USUARIO SOL (RUC + Usuario)</label>
                            <input type="text" wire:model="usuario_sol" class="form-control shadow-sm" placeholder="Ej: 20123456789MODDATOS">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">CLAVE SOL</label>
                            <input type="password" wire:model="clave_sol" class="form-control shadow-sm" placeholder="••••••••">
                        </div>
                        
                        <hr class="text-muted my-4">
                        <h6 class="text-dark fw-bold small text-uppercase mb-3"><i class="bi bi-file-earmark-lock me-1"></i> Certificado Digital</h6>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">ARCHIVO (.p12 / .pfx / .pem)</label>
                            <input type="file" wire:model="certificado_file" class="form-control shadow-sm">
                            <div wire:loading wire:target="certificado_file" class="text-primary small mt-2">
                                <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Subiendo...
                            </div>
                            @error('certificado_file') <span class="text-danger small d-block mt-1">{{ $message }}</span> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-secondary">CONTRASEÑA DEL CERTIFICADO</label>
                            <input type="password" wire:model="certificado_password" class="form-control shadow-sm" placeholder="Contraseña del archivo .p12">
                            <small class="text-muted d-block mt-1" style="font-size: 0.75rem;">Solo necesario para archivos .p12 o .pfx protegidos</small>
                        </div>

                        @if($certificado_path)
                            <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success small mb-3">
                                <i class="bi bi-check-circle-fill me-1"></i> Certificado cargado: <strong>{{ basename($certificado_path) }}</strong>
                            </div>
                        @else
                            <div class="alert alert-warning border-0 bg-warning bg-opacity-10 text-dark small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Falta cargar certificado.
                            </div>
                        @endif

                        <button type="submit" class="btn btn-danger w-100 fw-bold shadow-sm py-2 text-white">
                            <i class="bi bi-cloud-upload-fill me-2"></i> Actualizar Configuración SUNAT
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>