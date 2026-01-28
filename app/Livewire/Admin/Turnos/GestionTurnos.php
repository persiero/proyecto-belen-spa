<?php

namespace App\Livewire\Admin\Turnos;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Layout;
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

    #[Layout('layouts.admin')]
    public function render()
    {
        // 1. MONITOR DE ESTILISTAS (Lo Nuevo)
        // Traemos todos los estilistas activos y cargamos sus atenciones en curso
        $monitorEstilistas = Estilista::where('activo', true)
            ->with('atencionesEnCurso') // Usamos la relación que creamos arriba
            ->get();

        // 2. LISTA PARA EL SELECT (Aquí hacemos el cambio)
        // Cargamos 'atencionesEnCurso' también aquí para poder pintar el estado en el select
        $estilistas = Estilista::where('activo', true)
            ->with('atencionesEnCurso') // <--- AGREGADO
            ->orderBy('nombre')
            ->get();

        // 3. HISTORIAL DE TURNOS
        $turnos = Turno::with(['cliente', 'servicios.servicio'])
            ->where('estado', 'activo') // Opcional: quita esto si quieres ver historial completo
            ->orderBy('id', 'desc')
            ->paginate(10);

        // Catálogos
        $clientes = Cliente::orderBy('nombre')->get();
        // Nota: Para el select del modal, usamos los mismos estilistas pero sin la carga pesada
        $servicios = Servicio::where('activo', true)->get();

        // AGREGAMOS 'estilistas' AL COMPACT
        return view('livewire.admin.turnos.gestion-turnos', 
            compact('turnos', 'monitorEstilistas', 'estilistas', 'clientes', 'servicios'))
            ->with('titulo', 'Recepción y Turnos');
    }

    public function create()
    {
        $this->resetInputFields();
        // Agregamos una fila vacía por defecto para facilitar la carga
        $this->addItem(); 
        $this->openModal();
    }

    // Agregar una fila al formulario dinámico
    public function addItem()
    {
        $this->items[] = [
            'servicio_id' => '',
            'estilista_id' => '',
            'precio' => 0.00
        ];
    }

    // Eliminar una fila del formulario
    public function removeItem($index)
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindexar array
    }

    // Cuando seleccionan un servicio, actualizamos el precio automáticamente
    public function updatedItems($value, $key)
    {
        // La $key viene como "0.servicio_id"
        $parts = explode('.', $key);
        if (count($parts) == 2 && $parts[1] == 'servicio_id') {
            $index = $parts[0];
            $servicioId = $value;
            
            // Buscar precio original
            $servicio = Servicio::find($servicioId);
            if ($servicio) {
                $this->items[$index]['precio'] = $servicio->precio;
            }
        }
    }

    // === NUEVA FUNCIÓN: CARGAR DATOS PARA EDITAR ===
    public function edit($id)
    {
        $this->resetInputFields();
        $this->turno_id = $id;

        $turno = Turno::with('servicios')->find($id);

        $this->id_cliente = $turno->id_cliente;
        $this->observaciones = $turno->observaciones;

        // Cargamos los servicios existentes al array de items visual
        foreach($turno->servicios as $detalle) {
            $this->items[] = [
                'servicio_id' => $detalle->id_servicio,
                'estilista_id' => $detalle->id_estilista,
                'precio' => $detalle->precio_aplicado
            ];
        }

        $this->openModal();
    }

    // === STORE ACTUALIZADO (CREAR O EDITAR) ===
    public function store()
    {
        $this->validate();

        DB::beginTransaction(); // Usamos transacción por seguridad
        try {
            
            if ($this->turno_id) {
                // CASO EDITAR: Actualizamos cabecera
                $turno = Turno::find($this->turno_id);
                $turno->update([
                    'id_cliente' => $this->id_cliente,
                    'observaciones' => $this->observaciones
                ]);

                // ESTRATEGIA: Borrón y cuenta nueva para los detalles
                // Es lo más seguro para evitar duplicados o lógica compleja de diff
                TurnoServicio::where('id_turno', $this->turno_id)->delete();
                
                $mensaje = 'Turno actualizado y servicios agregados.';
            } else {
                // CASO CREAR
                $turno = Turno::create([
                    'id_cliente' => $this->id_cliente,
                    'hora_inicio' => Carbon::now(),
                    'estado' => 'activo',
                    'observaciones' => $this->observaciones
                ]);
                $mensaje = 'Turno registrado correctamente.';
            }

            // CREAR LOS DETALLES (Sea nuevo o editado, se crean igual)
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

    // Cancelar turno (Si el cliente se va sin pagar)
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
        $this->turno_id = null; // Resetear ID
        $this->id_cliente = '';
        $this->observaciones = '';
        $this->items = [];
    }
}