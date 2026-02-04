<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Roles
        DB::table('roles')->insert([
            ['nombre' => 'administrador', 'descripcion' => 'Acceso total', 'created_at' => now()],
            ['nombre' => 'cajero', 'descripcion' => 'Ventas y Caja', 'created_at' => now()],
            ['nombre' => 'encargado', 'descripcion' => 'Operaciones', 'created_at' => now()],
        ]);

        // 2. Usuario Admin (Para que puedas entrar)
        DB::table('usuarios')->insert([
            'id_rol' => 1, // Admin
            'nombre' => 'Admin Sistema',
            'email' => 'admin@belen.com', // <--- USA ESTE PARA LOGUEARTE
            'password' => Hash::make('password'), // Clave: password
            'activo' => 1,
            'created_at' => now(),
        ]);

        // 3. Métodos de Pago
        DB::table('metodos_pago')->insert([
            ['nombre' => 'efectivo', 'descripcion' => 'Efectivo', 'activo' => 1, 'created_at' => now()],
            ['nombre' => 'tarjeta', 'descripcion' => 'Crédito/Débito', 'activo' => 1, 'created_at' => now()],
            ['nombre' => 'yape', 'descripcion' => 'Billetera Digital', 'activo' => 1, 'created_at' => now()],
            ['nombre' => 'plin', 'descripcion' => 'Billetera Digital', 'activo' => 1, 'created_at' => now()],
            ['nombre' => 'transferencia', 'descripcion' => 'TransferenciaBancaria', 'activo' => 1, 'created_at' => now()],
        ]);

        // 4. Unidades SUNAT
        DB::table('unidades_sunat')->insert([
            ['codigo' => 'ZZ', 'descripcion' => 'Servicio', 'created_at' => now()],
            ['codigo' => 'NIU', 'descripcion' => 'Unidad (Bienes)', 'created_at' => now()],
        ]);

        // 5. Afectaciones IGV
        DB::table('afectaciones_igv')->insert([
            ['codigo' => '10', 'descripcion' => 'Gravado - Onerosa', 'gravado' => 1, 'porcentaje' => 18.00, 'created_at' => now()],
            ['codigo' => '20', 'descripcion' => 'Exonerado', 'gravado' => 0, 'porcentaje' => 0.00, 'created_at' => now()],
            ['codigo' => '30', 'descripcion' => 'Inafecto', 'gravado' => 0, 'porcentaje' => 0.00, 'created_at' => now()],
        ]);
    
       
    }
}
