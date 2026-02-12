<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\SerieComprobante;
use App\Models\Comprobante;
use Illuminate\Support\Facades\DB;

class SincronizarCorrelativos extends Command
{
    protected $signature = 'comprobantes:sincronizar-correlativos';
    protected $description = 'Sincroniza los correlativos de series_comprobante con los últimos comprobantes emitidos';

    public function handle()
    {
        $this->info('Sincronizando correlativos...');

        $series = SerieComprobante::all();

        foreach ($series as $serie) {
            // Buscar el último correlativo usado para esta serie
            $ultimoCorrelativo = Comprobante::where('id_serie_comprobante', $serie->id)
                ->max('correlativo');

            if ($ultimoCorrelativo) {
                $serie->correlativo_actual = $ultimoCorrelativo;
                $serie->save();
                $this->line("Serie {$serie->serie}: Actualizado a {$ultimoCorrelativo}");
            } else {
                $this->line("Serie {$serie->serie}: Sin comprobantes emitidos (mantiene 0)");
            }
        }

        $this->info('\n¡Sincronización completada!');
        return 0;
    }
}
