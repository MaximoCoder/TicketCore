<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ticketPrioritySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $priorities = [
            [
                'name' => 'Baja',
                'response_time' => 72, // horas
            ],
            [
                'name' => 'Media',
                'response_time' => 48,
            ],
            [
                'name' => 'Alta',
                'response_time' => 24,
            ],
            [
                'name' => 'Crítica',
                'response_time' => 4,
            ],
        ];

        DB::table('ticket_priorities')->insert($priorities);
    }
}
