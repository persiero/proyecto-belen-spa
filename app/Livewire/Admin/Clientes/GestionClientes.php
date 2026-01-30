<?php

namespace App\Livewire\Admin\Clientes;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Cliente;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Http;

class GestionClientes extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public $search = '';
    public $isOpen = false;

    // Campos
    public $cliente_id;
    public $nombre, $apellido, $telefono, $email, $direccion;
    public $tipo_documento = 'DNI';
    public $numero_documento;
    
    // CAMPOS BD EXISTENTES
    public $fecha_nacimiento;
    public $genero = 'Femenino';
    public $procedencia;

    // --- CONFIGURACIÓN API CORREGIDA ---
    // 1. Quité el "_BEARER" del final. Solo va el código puro.
    //private $apiToken = 'sk_12198.iMcPvw0tx01jydlLTqCcsIVKZBtShXxM'; 
    
    // URLs
    private $urlDni = 'https://api.decolecta.com/v1/reniec/dni?numero=';
    private $urlRuc = 'https://api.decolecta.com/v1/sunat/ruc?numero=';

    protected function rules()
    {
        return [
            'nombre' => 'required|string|max:150',
            'apellido' => $this->tipo_documento == 'RUC' ? 'nullable' : 'required|string|max:150',
            'telefono' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:150',
            'tipo_documento' => 'required|in:DNI,RUC,CE,PAS,OTRO',
            'numero_documento' => [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('clientes', 'numero_documento')->ignore($this->cliente_id)
            ],
            'direccion' => 'nullable|string|max:255',
            'fecha_nacimiento' => 'nullable|date',
            'genero' => 'nullable',
            'procedencia' => 'nullable',
        ];
    }

    public function updatedTipoDocumento($value)
    {
        $this->resetValidation();
        if($value == 'RUC') {
            $this->apellido = null;
            $this->genero = null;
        } else {
            $this->genero = 'Femenino';
        }
    }

    // --- FUNCIÓN CONSULTAR API ---
    public function buscarDocumento()
    {
        // Validaciones de longitud
        if ($this->tipo_documento == 'DNI' && strlen($this->numero_documento) != 8) {
            $this->addError('numero_documento', 'El DNI debe tener 8 dígitos.');
            return;
        }
        if ($this->tipo_documento == 'RUC' && strlen($this->numero_documento) != 11) {
            $this->addError('numero_documento', 'El RUC debe tener 11 dígitos.');
            return;
        }

        // --- CAMBIO AQUÍ: OBTENER TOKEN DE LA BD ---
        $config = \App\Models\ConfigNegocio::first();
        $tokenBD = $config->api_token ?? ''; // Obtener token o vacío

        if (empty($tokenBD)) {
            $this->addError('numero_documento', 'Falta configurar el Token API en el sistema.');
            return;
        }

        try {
            $url = ($this->tipo_documento == 'DNI') 
                    ? $this->urlDni . $this->numero_documento 
                    : $this->urlRuc . $this->numero_documento;

            // Laravel agrega automáticamente "Bearer " antes del token
            /** @var \Illuminate\Http\Client\Response $response */
            $response = Http::withToken($tokenBD)->get($url);

            if ($response->successful()) {
                $data = $response->json();

                if ($this->tipo_documento == 'DNI') {
                    // Mapeo según tu JSON de RENIEC
                    $this->nombre = $data['first_name'] ?? '';
                    $this->apellido = trim(($data['first_last_name'] ?? '') . ' ' . ($data['second_last_name'] ?? ''));
                } 
                elseif ($this->tipo_documento == 'RUC') {
                    // Mapeo según tu JSON de SUNAT
                    $this->nombre = $data['razon_social'] ?? ''; 
                    $this->apellido = null;
                    
                    // Construcción de Dirección Completa
                    // Ejemplo JSON: "direccion": "CAL. DEAN...", "distrito": "SAN ISIDRO", etc.
                    $dir = $data['direccion'] ?? '';
                    $dist = $data['distrito'] ?? '';
                    $prov = $data['provincia'] ?? '';
                    $dep = $data['departamento'] ?? '';

                    // Concatenamos: DIRECCION - DISTRITO - PROVINCIA - DEPARTAMENTO
                    $direccionCompleta = $dir;
                    if($dist) $direccionCompleta .= ' - ' . $dist;
                    if($prov) $direccionCompleta .= ' - ' . $prov;
                    if($dep)  $direccionCompleta .= ' - ' . $dep;

                    $this->direccion = $direccionCompleta;
                }
                
                // Limpiar error si tuvo éxito
                $this->resetErrorBag('numero_documento');

            } else {
                // Si la API responde error (ej: Token vencido o DNI no existe)
                // Para depurar, podrías descomentar esto temporalmente:
                // dd($response->body()); 
                $this->addError('numero_documento', 'No se encontraron datos. Verifique el número.');
            }

        } catch (\Exception $e) {
            $this->addError('numero_documento', 'Error de conexión: ' . $e->getMessage());
        }
    }

    #[Layout('layouts.admin')]
    #[Title('Directorio de Clientes')]
    public function render()
    {
        $clientes = Cliente::where(function($query) {
                $query->where('nombre', 'like', '%' . $this->search . '%')
                      ->orWhere('apellido', 'like', '%' . $this->search . '%')
                      ->orWhere('numero_documento', 'like', '%' . $this->search . '%');
            })
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('livewire.admin.clientes.gestion-clientes', compact('clientes'));
    }

    public function create() { $this->resetInputFields(); $this->openModal(); }

    public function edit($id)
    {
        $cliente = Cliente::findOrFail($id);
        $this->cliente_id = $id;
        $this->nombre = $cliente->nombre;
        $this->apellido = $cliente->apellido;
        $this->tipo_documento = $cliente->tipo_documento;
        $this->numero_documento = $cliente->numero_documento;
        
        $this->fecha_nacimiento = $cliente->fecha_nacimiento ? $cliente->fecha_nacimiento->format('Y-m-d') : null;
        $this->genero = $cliente->genero;
        $this->telefono = $cliente->telefono;
        $this->email = $cliente->email;
        $this->direccion = $cliente->direccion;
        $this->procedencia = $cliente->procedencia;

        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        Cliente::updateOrCreate(['id' => $this->cliente_id], [
            'nombre' => $this->nombre,
            'apellido' => $this->apellido,
            'tipo_documento' => $this->tipo_documento ?: null,
            'numero_documento' => $this->numero_documento ?: null,
            'fecha_nacimiento' => $this->fecha_nacimiento ?: null,
            'genero' => $this->genero,
            'telefono' => $this->telefono,
            'email' => $this->email,
            'direccion' => $this->direccion,
            'procedencia' => $this->procedencia ?: null,
        ]);

        session()->flash('message', $this->cliente_id ? 'Ficha actualizada.' : 'Cliente registrado.');
        $this->closeModal();
        $this->resetInputFields();
    }

    public function delete($id) { Cliente::find($id)->delete(); session()->flash('message', 'Eliminado.'); }
    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->cliente_id = null;
        $this->nombre = ''; $this->apellido = '';
        $this->tipo_documento = 'DNI'; 
        $this->numero_documento = '';
        $this->telefono = ''; $this->email = ''; $this->direccion = '';
        $this->fecha_nacimiento = null; 
        $this->procedencia = '';
        $this->genero = 'Femenino';
    }
}