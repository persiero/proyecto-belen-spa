<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 6. Configuración del Negocio
        DB::table('config_negocio')->updateOrInsert(
            ['id' => 1],
            [
                'nombre_comercial' => 'EMPRESA DE PRUEBAS S.A.C',
                'direccion' => 'AV. PRUEBAS 123',
                'telefono' => '999999999',
                'email' => 'belendegregori@hotmail.com',
                'ruc' => '20614899515',
                'precio_incluye_igv' => 1,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // 7. Configuración Tributaria
        DB::table('config_tributaria')->updateOrInsert(
            ['id' => 1],
            [
                'igv_porcentaje' => 18.00,
                'emision_automatica_cpe' => 0,
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
