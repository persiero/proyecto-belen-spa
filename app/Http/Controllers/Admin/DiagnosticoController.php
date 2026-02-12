<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ConfigTributaria;
use App\Models\ConfigNegocio;
use App\Models\SerieComprobante;
use Illuminate\Support\Facades\Storage;

class DiagnosticoController extends Controller
{
    public function index()
    {
        $config = ConfigTributaria::first();
        $negocio = ConfigNegocio::first();
        
        $diagnostico = [
            'timestamp' => now()->format('Y-m-d H:i:s'),
            
            // Configuración Tributaria
            'config_tributaria' => [
                'modo' => $config->modo ?? 'NO CONFIGURADO',
                'usuario_sol' => $config->usuario_sol ? 'CONFIGURADO (' . strlen($config->usuario_sol) . ' caracteres)' : 'NO CONFIGURADO',
                'clave_sol' => $config->clave_sol ? 'CONFIGURADA' : 'NO CONFIGURADA',
                'certificado_path' => $config->certificado_path ?? 'NO CONFIGURADO',
                'certificado_password' => $config->certificado_password ? 'CONFIGURADA' : 'NO CONFIGURADA',
            ],
            
            // Verificación de Certificado
            'certificado' => [
                'existe_en_storage' => false,
                'ruta_completa' => null,
                'tamaño' => null,
            ],
            
            // Configuración de Negocio
            'negocio' => [
                'ruc' => $negocio->ruc ?? 'NO CONFIGURADO',
                'nombre' => $negocio->nombre_comercial ?? 'NO CONFIGURADO',
                'direccion' => $negocio->direccion ?? 'NO CONFIGURADO',
            ],
            
            // Series de Comprobantes
            'series' => SerieComprobante::where('activo', true)->get()->map(function($s) {
                return [
                    'serie' => $s->serie,
                    'correlativo_actual' => $s->correlativo_actual,
                    'tipo' => $s->id_tipo_comprobante
                ];
            }),
            
            // Verificaciones del Sistema
            'sistema' => [
                'php_version' => PHP_VERSION,
                'laravel_version' => app()->version(),
                'timezone' => config('app.timezone'),
                'debug_mode' => config('app.debug') ? 'ACTIVADO' : 'DESACTIVADO',
            ],
            
            // Extensiones PHP necesarias
            'extensiones_php' => [
                'openssl' => extension_loaded('openssl') ? '✅ Instalada' : '❌ NO Instalada',
                'soap' => extension_loaded('soap') ? '✅ Instalada' : '❌ NO Instalada',
                'curl' => extension_loaded('curl') ? '✅ Instalada' : '❌ NO Instalada',
                'zip' => extension_loaded('zip') ? '✅ Instalada' : '❌ NO Instalada',
            ],
        ];
        
        // Verificar certificado
        if ($config->certificado_path) {
            if (Storage::exists('certificados/' . $config->certificado_path)) {
                $path = Storage::path('certificados/' . $config->certificado_path);
                $diagnostico['certificado']['existe_en_storage'] = true;
                $diagnostico['certificado']['ruta_completa'] = $path;
                $diagnostico['certificado']['tamaño'] = filesize($path) . ' bytes';
            } else {
                $path = storage_path('app/certificados/' . $config->certificado_path);
                if (file_exists($path)) {
                    $diagnostico['certificado']['existe_en_storage'] = true;
                    $diagnostico['certificado']['ruta_completa'] = $path;
                    $diagnostico['certificado']['tamaño'] = filesize($path) . ' bytes';
                }
            }
        }
        
        // Test de conexión a SUNAT
        $diagnostico['conexion_sunat'] = $this->testConexionSunat($config->modo);
        
        return view('admin.diagnostico', compact('diagnostico'));
    }
    
    private function testConexionSunat($modo)
    {
        $url = $modo == 'produccion' 
            ? 'https://e-factura.sunat.gob.pe/ol-ti-itcpfegem/billService'
            : 'https://e-beta.sunat.gob.pe/ol-ti-itcpfegem-beta/billService';
        
        try {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 || $httpCode == 405) {
                return '✅ Conexión exitosa (HTTP ' . $httpCode . ')';
            } else {
                return '⚠️ Respuesta inesperada (HTTP ' . $httpCode . ')';
            }
        } catch (\Exception $e) {
            return '❌ Error: ' . $e->getMessage();
        }
    }
}
