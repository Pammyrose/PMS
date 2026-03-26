<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code' => 'GASS',
                'desc' => 'General Administration and Support Services',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'STO',
                'desc' => 'Support To Operations',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'ENF',
                'desc' => 'Natural Resources Enforcement and Regulatory Program',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'Biodiv',
                'desc' => 'Enhanced Biodiversity Conservation',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'Lands',
                'desc' => 'Land Management',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'Soilcon',
                'desc' => '',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'code' => 'NRA',
                'desc' => 'Natural Resources Assessment',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('types')->insert($types);
    }
}
