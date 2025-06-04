<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ticketStatusesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'Sin Asignar'],
            ['name' => 'Asignado'],
            ['name' => 'En Progreso'],
            ['name' => 'En Espera'],
            ['name' => 'Resuelto'],
            ['name' => 'Cerrado'],
            ['name' => 'Reabierto'],
        ];

        DB::table('ticket_statuses')->insert($statuses);
    }
}
