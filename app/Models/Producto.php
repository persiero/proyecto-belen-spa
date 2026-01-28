<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Producto extends Model
{
    use SoftDeletes;

    // Definimos la tabla explícitamente para evitar problemas con plurales
    protected $table = 'productos';

    protected $fillable = [
        'tipo',          // 'venta', 'insumo', 'mixto'
        'nombre',
        'descripcion',
        'id_afectacion', // Relación con SUNAT
        'id_unidad',     // Relación con SUNAT
        'costo_compra',
        'precio_venta',
        'stock_actual', // ESTE SERÁ STOCK DE VENTA
        'stock_insumo', // <--- NUEVO CAMPO
        'stock_minimo',
        'codigo_barras',
        'activo'
    ];

    protected $casts = [
        'activo' => 'boolean',          // Para que sea true/false
        'costo_compra' => 'float',      // Para cálculos matemáticos
        'precio_venta' => 'float',      // Para cálculos matemáticos
        'stock_actual' => 'integer',
        'stock_insumo' => 'integer',    // <--- NUEVO
        'stock_minimo' => 'integer',
    ];

    // ==============================
    // RELACIONES
    // ==============================

    // Un producto tiene una Afectación de IGV (Gravado, Exonerado, etc.)
    public function afectacion()
    {
        return $this->belongsTo(AfectacionIgv::class, 'id_afectacion');
    }

    // Un producto tiene una Unidad de Medida (NIU, ZZ, etc.)
    public function unidad()
    {
        return $this->belongsTo(UnidadSunat::class, 'id_unidad');
    }
}