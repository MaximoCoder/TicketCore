<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ticketCategorySeeder extends Seeder
{
    public function run()
    {

        $categories = [
            'Problemas de inicio de sesión',
            'Recuperación de contraseña',
            'Error en el sistema',
            'Problemas con la red',
            'Problemas con el hardware',
            'Instalación de software',
            'Actualización de sistema',
            'Solicitud de acceso a sistemas',
            'Quejas y sugerencias',
        ];

        foreach ($categories as $name) {
            DB::table('ticket_categories')->insert([
                'name' => $name,
                'description' => '',
                'is_active' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }
}
