<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SerieComprobante extends Model
{
    protected $table = 'series_comprobante';

    protected $fillable = [
        'id_tipo_comprobante',
        'serie',               // 'F001', 'B001'
        'correlativo_actual',  // 1500
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',
        'correlativo_actual' => 'integer',
    ];

    // Relación inversa: Pertenece a un Tipo de Comprobante
    public function tipoComprobante()
    {
        return $this->belongsTo(TipoComprobante::class, 'id_tipo_comprobante');
    }

    /**
     * Obtiene el siguiente correlativo y actualiza el registro
     * Usa lockForUpdate para evitar duplicados en concurrencia
     */
    public function obtenerSiguienteCorrelativo()
    {
        DB::beginTransaction();
        try {
            // Bloquear el registro para evitar race conditions
            $serie = self::where('id', $this->id)->lockForUpdate()->first();
            
            // Incrementar
            $nuevoCorrelativo = $serie->correlativo_actual + 1;
            
            // Actualizar
            $serie->correlativo_actual = $nuevoCorrelativo;
            $serie->save();
            
            DB::commit();
            return $nuevoCorrelativo;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}