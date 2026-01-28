<?php

namespace App\Livewire\Admin\Reportes;

use Livewire\Component;
use Livewire\Attributes\Layout;
use App\Models\Estilista;
use App\Models\TurnoServicio;
use Carbon\Carbon;

class ReporteComisiones extends Component
{
    public $fecha_inicio;
    public $fecha_fin;
    public $estilista_id = ''; // '' para todos

    public function mount()
    {
        // Por defecto, mes actual
        $this->fecha_inicio = Carbon::now()->startOfMonth()->format('Y-m-d');
        $this->fecha_fin = Carbon::now()->endOfMonth()->format('Y-m-d');
    }

    #[Layout('layouts.admin')]
    public function render()
    {
        // 1. Obtenemos servicios realizados en el rango de fechas
        // Filtrando solo aquellos que pertenecen a TURNOS CERRADOS (o pagados)
        $servicios = TurnoServicio::query()
            ->with(['estilista', 'servicio', 'turno'])
            ->whereHas('turno', function($q) {
                // Filtramos por fecha del turno y que esté cerrado
                $q->whereBetween('hora_inicio', [
                    $this->fecha_inicio . ' 00:00:00', 
                    $this->fecha_fin . ' 23:59:59'
                ])->where('estado', 'cerrado'); 
            });

        if ($this->estilista_id) {
            $servicios->where('id_estilista', $this->estilista_id);
        }

        $resultados = $servicios->get();

        // 2. Calculamos totales agrupados
        $reporte = [];
        $totalGeneral = 0;
        $comisionGeneral = 0;

        foreach($resultados as $item) {
            $estilistaId = $item->id_estilista;
            $nombre = $item->estilista->nombre;
            $porcentaje = $item->estilista->porcentaje_comision; // El % configurado en su perfil
            
            $montoServicio = $item->precio_aplicado;
            $montoComision = ($montoServicio * $porcentaje) / 100;

            if(!isset($reporte[$estilistaId])) {
                $reporte[$estilistaId] = [
                    'nombre' => $nombre,
                    'servicios_count' => 0,
                    'total_vendido' => 0,
                    'porcentaje_base' => $porcentaje,
                    'total_comision' => 0
                ];
            }

            $reporte[$estilistaId]['servicios_count']++;
            $reporte[$estilistaId]['total_vendido'] += $montoServicio;
            $reporte[$estilistaId]['total_comision'] += $montoComision;

            $totalGeneral += $montoServicio;
            $comisionGeneral += $montoComision;
        }

        $estilistas = Estilista::where('activo', true)->get();

        return view('livewire.admin.reportes.reporte-comisiones', compact('reporte', 'estilistas', 'totalGeneral', 'comisionGeneral'))
            ->with('titulo', 'Liquidación de Comisiones');
    }
}