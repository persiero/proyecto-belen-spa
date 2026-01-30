<?php

namespace App\Livewire\Admin\Configuracion;

use Livewire\Component;
use Livewire\WithFileUploads; // <--- 1. IMPORTAR
use Livewire\Attributes\Layout;
use App\Models\ConfigNegocio;
use App\Models\ConfigTributaria;

class GestionConfiguracion extends Component
{
    use WithFileUploads; // <--- 2. USAR TRAIT

    // Datos Negocio
    public $nombre_comercial, $direccion, $telefono, $email, $ruc;
    public $api_token;
    
    // Datos Tributarios
    public $igv_porcentaje, $modo, $usuario_sol, $clave_sol;
    public $emision_automatica_cpe = false;

    public $certificado_path; // Ruta string (BD)
    public $certificado_file; // Archivo temporal (Input) <--- NUEVO
    
    public function mount()
    {
        // Cargar Negocio
        $negocio = ConfigNegocio::first();
        if($negocio) {
            $this->nombre_comercial = $negocio->nombre_comercial;
            $this->direccion = $negocio->direccion;
            $this->telefono = $negocio->telefono;
            $this->email = $negocio->email;
            $this->ruc = $negocio->ruc;
            $this->api_token = $negocio->api_token;
        }

        // Cargar Tributaria
        $tributaria = ConfigTributaria::first();
        if($tributaria) {
            $this->igv_porcentaje = $tributaria->igv_porcentaje;
            $this->emision_automatica_cpe = (bool) $tributaria->emision_automatica_cpe;
            $this->modo = $tributaria->modo;
            $this->usuario_sol = $tributaria->usuario_sol;
            $this->clave_sol = $tributaria->clave_sol;

            // ¡ESTA LÍNEA FALTABA! 👇
            $this->certificado_path = $tributaria->certificado_path;
        }
    }

    public function guardarNegocio()
    {
        $this->validate([
            'nombre_comercial' => 'required',
            'ruc' => 'required|digits:11',
            'direccion' => 'required',
            'api_token' => 'nullable|string',
        ]);

        ConfigNegocio::updateOrCreate(['id' => 1], [
            'nombre_comercial' => $this->nombre_comercial,
            'direccion' => $this->direccion,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'ruc' => $this->ruc,
            'api_token' => $this->api_token,
        ]);

        session()->flash('message_negocio', 'Datos del negocio y API Token actualizados.');
    }

    public function guardarTributaria()
    {
        $this->validate([
            'igv_porcentaje' => 'required|numeric|min:0|max:100',
            'modo' => 'required|in:beta,produccion',
            // CAMBIO AQUÍ: Usamos 'extensions' en lugar de 'mimes'
            'certificado_file' => 'nullable|file|extensions:pem,txt,pfx,cer|max:2048',
        ]);

        // LÓGICA DE SUBIDA DE ARCHIVO
        $rutaFinal = $this->certificado_path; // Mantener el anterior por defecto

        if ($this->certificado_file) {
            // Guardar en storage/app/certificados
            // Store devuelve el path con hash, pero queremos nombre limpio o controlado
            $nombreArchivo = 'certificado_' . time() . '.' . $this->certificado_file->getClientOriginalExtension();
            
            $this->certificado_file->storeAs('certificados', $nombreArchivo); // Guardar físico
            $rutaFinal = $nombreArchivo; // Guardar nombre en BD
        }

        ConfigTributaria::updateOrCreate(['id' => 1], [
            'igv_porcentaje' => $this->igv_porcentaje,
            'emision_automatica_cpe' => $this->emision_automatica_cpe,
            'modo' => $this->modo,
            'usuario_sol' => $this->usuario_sol,
            'clave_sol' => $this->clave_sol,
            'certificado_path' => $rutaFinal, // <--- Guardamos la ruta nueva
        ]);

        // 1. Actualizamos la variable visual para que el mensaje amarillo cambie a verde
        $this->certificado_path = $rutaFinal; 
        
        // 2. Limpiamos el input del archivo para que quede listo por si quieres subir otro
        $this->reset('certificado_file'); 

        session()->flash('message_tributaria', 'Configuración SUNAT actualizada y certificado subido.');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        return view('livewire.admin.configuracion.gestion-configuracion')
            ->with('titulo', 'Configuración del Sistema');
    }
}