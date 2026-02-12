<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FieldsSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('fields')->insert([
            ['name' => 'GASS'],
            ['name' => 'STO'],
            ['name' => 'ENF'],
            ['name' => 'PA'],
            ['name' => 'ENGP'],
            ['name' => 'LANDS'],
            ['name' => 'SOILCON'],
            ['name' => 'NRA'],
            ['name' => 'PARIA'],
            ['name' => 'COBB'],
            ['name' => 'CONTINUING'],
        ]);
    }
}
