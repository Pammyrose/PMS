<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class IndicatorTypeSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('indicator_types')->insert([
            ['name' => 'cumulative'],
            ['name' => 'non-cumulative'],
            ['name' => 'semi-cumulative'],
        ]);
    }
}
