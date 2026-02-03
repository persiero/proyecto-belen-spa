<?php

namespace App\Livewire\Admin\Turnos;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Turno;
use App\Models\TurnoServicio;
use App\Models\Cliente;
use App\Models\Estilista;
use App\Models\Servicio;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GestionTurnos extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $isOpen = false;
    public $search = '';

    // Datos del Turno
    public $turno_id = null;
    public $id_cliente;
    public $observaciones;

    // [PEGAR AQUÍ] BLOQUE DE BUSCADOR DE CLIENTES -----------------------
    public $buscar_cliente = ''; 
    public $clientes_encontrados = []; 
    public $cliente_seleccionado_nombre = null;
    
    // Lista dinámica de servicios [servicio_id, estilista_id, precio]
    public $items = []; 

    // Reglas de validación
    protected $rules = [
        'id_cliente' => 'required|exists:clientes,id',
        'items' => 'required|array|min:1',
        'items.*.servicio_id' => 'required|exists:servicios,id',
        'items.*.estilista_id' => 'required|exists:estilistas,id',
        'items.*.precio' => 'required|numeric|min:0',
    ];

    public function updatedBuscarCliente()
    {
        // Limpiamos espacios en blanco al inicio/final
        $termino = trim($this->buscar_cliente);

        if(strlen($termino) < 2) {
            $this->clientes_encontrados = [];
            return;
        }

        $this->clientes_encontrados = \App\Models\Cliente::where(function($query) use ($termino) {
                $query->where('nombre', 'like', '%' . $termino . '%')
                      ->orWhere('apellido', 'like', '%' . $termino . '%')
                      ->orWhere('numero_documento', 'like', '%' . $termino . '%');
            })->limit(5)->get();
    }

    public function seleccionarCliente($id)
    {
        $cliente = \App\Models\Cliente::find($id);
        if($cliente) {
            $this->id_cliente = $cliente->id; // Usamos tu variable original $id_cliente
            $this->cliente_seleccionado_nombre = $cliente->nombre . ' ' . $cliente->apellido;
            $this->buscar_cliente = '';
            $this->clientes_encontrados = [];
        }
    }

    public function limpiarCliente()
    {
        $this->id_cliente = null;
        $this->cliente_seleccionado_nombre = null;
        $this->buscar_cliente = '';
        $this->clientes_encontrados = [];
    }

    #[Layout('layouts.admin')]
    #[Title('Recepción y Turnos')]
    public function render()
    {
        // 1. MONITOR DE ESTILISTAS
        // Traemos estilistas activos y sus atenciones de hoy que NO han terminado
        $monitorEstilistas = Estilista::where('activo', true)
            ->with(['atencionesEnCurso' => function($q) {
                // Filtramos solo las atenciones de turnos ACTIVOS
                $q->whereHas('turno', function($t) {
                    $t->where('estado', 'activo');
                });
            }]) 
            ->orderBy('nombre')
            ->get();

        // 2. HISTORIAL DE TURNOS ACTIVOS
        $turnos = Turno::with(['cliente', 'servicios.servicio', 'servicios.estilista'])
            ->where('estado', 'activo') 
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Catálogos para el Modal
        $clientes = Cliente::orderBy('nombre')->get();
        $servicios = Servicio::where('activo', true)->orderBy('nombre')->get();
        $estilistas = Estilista::where('activo', true)->orderBy('nombre')->get(); // Para el select

        return view('livewire.admin.turnos.gestion-turnos', 
            compact('turnos', 'monitorEstilistas', 'estilistas', 'clientes', 'servicios'));
    }

    public function create()
    {
        $this->resetInputFields();
        $this->addItem(); // Fila vacía por defecto
        $this->openModal();
    }

    public function addItem()
    {
        $this->items[] = [
            'servicio_id' => '',
            'estilista_id' => '',
            'precio' => 0.00
        ];
    }

    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);
    }

    // Actualizar precio automáticamente al seleccionar servicio
    public function updatedItems($value, $key)
    {
        $parts = explode('.', $key);
        if (count($parts) == 2 && $parts[1] == 'servicio_id') {
            $index = $parts[0];
            $servicio = Servicio::find($value);
            if ($servicio) {
                $this->items[$index]['precio'] = $servicio->precio;
            }
        }
    }

    public function edit($id)
    {
        $this->resetInputFields();
        $this->turno_id = $id;

        $turno = Turno::with('servicios', 'cliente')->find($id);
        $this->id_cliente = $turno->id_cliente;
        // AGREGAR ESTO: Cargar el nombre visual para que el buscador aparezca verde
        if($turno->cliente) {
            $this->cliente_seleccionado_nombre = $turno->cliente->nombre . ' ' . $turno->cliente->apellido;
        }
        $this->observaciones = $turno->observaciones;

        foreach($turno->servicios as $detalle) {
            $this->items[] = [
                'servicio_id' => $detalle->id_servicio,
                'estilista_id' => $detalle->id_estilista,
                'precio' => $detalle->precio_aplicado
            ];
        }

        $this->openModal();
    }

    public function store()
    {
        $this->validate();

        DB::beginTransaction();
        try {
            if ($this->turno_id) {
                // EDITAR
                $turno = Turno::find($this->turno_id);
                $turno->update([
                    'id_cliente' => $this->id_cliente,
                    'observaciones' => $this->observaciones
                ]);
                
                // Borrón y cuenta nueva de detalles (Estrategia segura)
                TurnoServicio::where('id_turno', $this->turno_id)->delete();
                $mensaje = 'Atención actualizada correctamente.';
            } else {
                // CREAR
                $turno = Turno::create([
                    'id_cliente' => $this->id_cliente,
                    'hora_inicio' => Carbon::now(),
                    'estado' => 'activo',
                    'observaciones' => $this->observaciones
                ]);
                $mensaje = 'Nueva atención iniciada.';
            }

            // GUARDAR DETALLES
            foreach ($this->items as $item) {
                TurnoServicio::create([
                    'id_turno' => $turno->id,
                    'id_servicio' => $item['servicio_id'],
                    'id_estilista' => $item['estilista_id'],
                    'precio_aplicado' => $item['precio']
                ]);
            }

            DB::commit();
            session()->flash('message', $mensaje);
            $this->closeModal();
            $this->resetInputFields();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('message', 'Error: ' . $e->getMessage());
        }
    }

    public function cancelar($id)
    {
        $turno = Turno::find($id);
        $turno->update(['estado' => 'cancelado', 'hora_fin' => Carbon::now()]);
        session()->flash('message', 'Turno cancelado.');
    }

    public function openModal() { $this->isOpen = true; }
    public function closeModal() { $this->isOpen = false; }

    private function resetInputFields()
    {
        $this->turno_id = null;
        $this->id_cliente = '';
        $this->observaciones = '';
        $this->items = [];
        $this->limpiarCliente();
    }
}