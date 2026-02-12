@extends('layouts.admin')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0"><i class="bi bi-gear-fill me-2"></i> Diagnóstico del Sistema SUNAT</h4>
            <small>Generado: {{ $diagnostico['timestamp'] }}</small>
        </div>
        
        <div class="card-body">
            
            {{-- CONFIGURACIÓN TRIBUTARIA --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-file-earmark-text me-2"></i> Configuración Tributaria</h5>
                <table class="table table-sm">
                    <tr>
                        <td width="30%" class="fw-bold">Modo:</td>
                        <td>
                            <span class="badge {{ $diagnostico['config_tributaria']['modo'] == 'produccion' ? 'bg-success' : 'bg-warning' }}">
                                {{ strtoupper($diagnostico['config_tributaria']['modo']) }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Usuario SOL:</td>
                        <td>{{ $diagnostico['config_tributaria']['usuario_sol'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Clave SOL:</td>
                        <td>{{ $diagnostico['config_tributaria']['clave_sol'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Certificado:</td>
                        <td>{{ $diagnostico['config_tributaria']['certificado_path'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Contraseña Certificado:</td>
                        <td>{{ $diagnostico['config_tributaria']['certificado_password'] }}</td>
                    </tr>
                </table>
            </div>
            
            {{-- VERIFICACIÓN DE CERTIFICADO --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-shield-lock me-2"></i> Certificado Digital</h5>
                <table class="table table-sm">
                    <tr>
                        <td width="30%" class="fw-bold">Estado:</td>
                        <td>
                            @if($diagnostico['certificado']['existe_en_storage'])
                                <span class="badge bg-success">✅ ENCONTRADO</span>
                            @else
                                <span class="badge bg-danger">❌ NO ENCONTRADO</span>
                            @endif
                        </td>
                    </tr>
                    @if($diagnostico['certificado']['ruta_completa'])
                    <tr>
                        <td class="fw-bold">Ruta:</td>
                        <td><code>{{ $diagnostico['certificado']['ruta_completa'] }}</code></td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Tamaño:</td>
                        <td>{{ $diagnostico['certificado']['tamaño'] }}</td>
                    </tr>
                    @endif
                </table>
            </div>
            
            {{-- DATOS DEL NEGOCIO --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-building me-2"></i> Datos del Negocio</h5>
                <table class="table table-sm">
                    <tr>
                        <td width="30%" class="fw-bold">RUC:</td>
                        <td>{{ $diagnostico['negocio']['ruc'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Razón Social:</td>
                        <td>{{ $diagnostico['negocio']['nombre'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Dirección:</td>
                        <td>{{ $diagnostico['negocio']['direccion'] }}</td>
                    </tr>
                </table>
            </div>
            
            {{-- SERIES DE COMPROBANTES --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-receipt me-2"></i> Series de Comprobantes</h5>
                <table class="table table-sm table-striped">
                    <thead>
                        <tr>
                            <th>Serie</th>
                            <th>Correlativo Actual</th>
                            <th>Tipo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($diagnostico['series'] as $serie)
                        <tr>
                            <td><strong>{{ $serie['serie'] }}</strong></td>
                            <td>{{ $serie['correlativo_actual'] }}</td>
                            <td>
                                @if($serie['tipo'] == 1)
                                    <span class="badge bg-primary">Factura</span>
                                @elseif($serie['tipo'] == 2)
                                    <span class="badge bg-info">Boleta</span>
                                @else
                                    <span class="badge bg-warning">Nota Crédito</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            {{-- EXTENSIONES PHP --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-code-square me-2"></i> Extensiones PHP Requeridas</h5>
                <table class="table table-sm">
                    @foreach($diagnostico['extensiones_php'] as $ext => $estado)
                    <tr>
                        <td width="30%" class="fw-bold">{{ $ext }}:</td>
                        <td>{!! $estado !!}</td>
                    </tr>
                    @endforeach
                </table>
            </div>
            
            {{-- CONEXIÓN SUNAT --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-wifi me-2"></i> Conexión a SUNAT</h5>
                <table class="table table-sm">
                    <tr>
                        <td width="30%" class="fw-bold">Estado:</td>
                        <td>{!! $diagnostico['conexion_sunat'] !!}</td>
                    </tr>
                </table>
            </div>
            
            {{-- INFORMACIÓN DEL SISTEMA --}}
            <div class="mb-4">
                <h5 class="border-bottom pb-2"><i class="bi bi-info-circle me-2"></i> Información del Sistema</h5>
                <table class="table table-sm">
                    <tr>
                        <td width="30%" class="fw-bold">PHP Version:</td>
                        <td>{{ $diagnostico['sistema']['php_version'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Laravel Version:</td>
                        <td>{{ $diagnostico['sistema']['laravel_version'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Timezone:</td>
                        <td>{{ $diagnostico['sistema']['timezone'] }}</td>
                    </tr>
                    <tr>
                        <td class="fw-bold">Debug Mode:</td>
                        <td>
                            <span class="badge {{ $diagnostico['sistema']['debug_mode'] == 'ACTIVADO' ? 'bg-warning' : 'bg-success' }}">
                                {{ $diagnostico['sistema']['debug_mode'] }}
                            </span>
                        </td>
                    </tr>
                </table>
            </div>
            
        </div>
        
        <div class="card-footer bg-light">
            <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">
                <i class="bi bi-arrow-left me-2"></i> Volver al Dashboard
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                <i class="bi bi-printer me-2"></i> Imprimir Diagnóstico
            </button>
        </div>
    </div>
</div>
@endsection
