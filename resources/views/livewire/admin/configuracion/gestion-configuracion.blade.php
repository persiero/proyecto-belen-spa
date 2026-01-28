<div>
    <div class="row">
        
        <div class="col-md-6">
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-shop"></i> Datos del Negocio</h3>
                </div>
                <div class="card-body">
                    @if (session()->has('message_negocio'))
                        <div class="alert alert-success py-2">{{ session('message_negocio') }}</div>
                    @endif

                    <form wire:submit.prevent="guardarNegocio">
                        <div class="mb-3">
                            <label>Nombre Comercial</label>
                            <input type="text" wire:model="nombre_comercial" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label>RUC (Emisor)</label>
                            <input type="text" wire:model="ruc" class="form-control" maxlength="11">
                        </div>
                        <div class="mb-3">
                            <label>Dirección Fiscal</label>
                            <input type="text" wire:model="direccion" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>Teléfono</label>
                                <input type="text" wire:model="telefono" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Email</label>
                                <input type="email" wire:model="email" class="form-control">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card-danger card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="bi bi-cloud-arrow-up"></i> Facturación SUNAT</h3>
                </div>
                <div class="card-body">
                    @if (session()->has('message_tributaria'))
                        <div class="alert alert-success py-2">{{ session('message_tributaria') }}</div>
                    @endif

                    <form wire:submit.prevent="guardarTributaria">
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label>IGV (%)</label>
                                <input type="number" wire:model="igv_porcentaje" class="form-control">
                            </div>
                            <div class="col-6 mb-3">
                                <label>Entorno</label>
                                <select wire:model="modo" class="form-select">
                                    <option value="beta">BETA (Pruebas)</option>
                                    <option value="produccion">PRODUCCIÓN</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" wire:model="emision_automatica_cpe" id="autoCpe">
                                <label class="form-check-label" for="autoCpe">Emitir CPE Automáticamente al cobrar</label>
                            </div>
                            <small class="text-muted">Si está desactivado, deberás enviar los comprobantes manualmente al final del día.</small>
                        </div>

                        <hr>
                        <h6 class="text-secondary">Credenciales SOL (Clave Sol)</h6>
                        
                        <div class="mb-3">
                            <label>Usuario SOL (RUC + Usuario)</label>
                            <input type="text" wire:model="usuario_sol" class="form-control" placeholder="Ej: 20123456789MODDATOS">
                        </div>
                        <div class="mb-3">
                            <label>Clave SOL</label>
                            <input type="password" wire:model="clave_sol" class="form-control">
                        </div>
                        
                        <hr>
                        <h6 class="text-secondary">Certificado Digital</h6>
                        
                        <div class="mb-3">
                            <label class="form-label">Archivo del Certificado (.pem)</label>
                            <input type="file" wire:model="certificado_file" class="form-control">
                            <div wire:loading wire:target="certificado_file" class="text-primary small mt-1">
                                Subiendo archivo...
                            </div>
                            @error('certificado_file') <span class="text-danger small">{{ $message }}</span> @enderror
                        </div>

                        @if($certificado_path)
                            <div class="alert alert-success border small mb-3">
                                <i class="bi bi-check-circle"></i> Certificado actual cargado: 
                                <strong>{{ $certificado_path }}</strong>
                            </div>
                        @else
                            <div class="alert alert-warning border small mb-3">
                                <i class="bi bi-exclamation-triangle"></i> No hay certificado cargado.
                            </div>
                        @endif

                        <button type="submit" class="btn btn-danger w-100">Actualizar Configuración SUNAT</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>