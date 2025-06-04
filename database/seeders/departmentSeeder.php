<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class departmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Creamos los departamentos
        $departamentos = [
            'Administracion',
            'Refacciones',
            'Servicio',
            'Unidades Nuevas',
            'Unidades Seminuevas',
            'Hojalateria y Pintura'
        ];

        // Insertamos los departamentos
        foreach ($departamentos as $departamento) {
            DB::table('departments')->insert([
                'name' => $departamento,
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
