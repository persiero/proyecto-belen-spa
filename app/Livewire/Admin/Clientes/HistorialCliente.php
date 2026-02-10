<?php

namespace App\Livewire\Admin\Clientes;

use Livewire\Component;
use App\Models\Cliente;
use App\Models\Turno;

class HistorialCliente extends Component
{
    public $isOpen = false;
    public $cliente_id;
    public $cliente;
    public $filtro_fecha = 'todo'; // todo, mes, trimestre, año
    
    protected $listeners = ['abrirHistorial'];

    public function abrirHistorial($clienteId)
    {
        $this->cliente_id = $clienteId;
        $this->cliente = Cliente::with([
            'turnos' => function($q) {
                $q->where('estado', '!=', 'cancelado')
                  ->orderBy('hora_inicio', 'desc');
            },
            'turnos.servicios.servicio',
            'turnos.servicios.estilista',
            'turnos.productos.producto',
            'turnos.productos.estilista',
            'ventas.comprobante.tipoComprobante'
        ])->find($clienteId);
        
        $this->isOpen = true;
    }

    public function closeModal()
    {
        $this->isOpen = false;
        $this->reset(['cliente_id', 'cliente', 'filtro_fecha']);
    }

    public function getTurnosFiltrados()
    {
        if (!$this->cliente) return collect();

        $turnos = $this->cliente->turnos;

        switch ($this->filtro_fecha) {
            case 'mes':
                return $turnos->filter(fn($t) => $t->hora_inicio->isCurrentMonth());
            case 'trimestre':
                return $turnos->filter(fn($t) => $t->hora_inicio->isCurrentQuarter());
            case 'año':
                return $turnos->filter(fn($t) => $t->hora_inicio->isCurrentYear());
            default:
                return $turnos;
        }
    }

    public function render()
    {
        $turnos_filtrados = $this->getTurnosFiltrados();
        
        // Estadísticas
        $total_gastado = $this->cliente ? $this->cliente->ventas->where('estado', 'pagada')->sum('total') : 0;
        $total_visitas = $turnos_filtrados->count();
        $ultima_visita = $turnos_filtrados->first();
        
        // Servicio más frecuente
        $servicios_count = [];
        foreach ($turnos_filtrados as $turno) {
            foreach ($turno->servicios as $ts) {
                $nombre = $ts->servicio->nombre;
                $servicios_count[$nombre] = ($servicios_count[$nombre] ?? 0) + 1;
            }
        }
        arsort($servicios_count);
        $servicio_favorito = array_key_first($servicios_count);

        return view('livewire.admin.clientes.historial-cliente', [
            'turnos' => $turnos_filtrados,
            'total_gastado' => $total_gastado,
            'total_visitas' => $total_visitas,
            'ultima_visita' => $ultima_visita,
            'servicio_favorito' => $servicio_favorito
        ]);
    }
}
