<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OfficesSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('offices')->insert([
            // REGIONAL
            ['id' => 1, 'name' => 'Regional Office', 'parent_id' => null],

            // PENROs (under Regional)
            ['id' => 2, 'name' => 'PENRO Abra', 'parent_id' => null],
            ['id' => 3, 'name' => 'PENRO Apayao', 'parent_id' => null],
            ['id' => 4, 'name' => 'PENRO Benguet', 'parent_id' => null],
            ['id' => 5, 'name' => 'PENRO Ifugao', 'parent_id' => null],
            ['id' => 6, 'name' => 'PENRO Kalinga', 'parent_id' => null],
            ['id' => 7, 'name' => 'PENRO Mt. Province', 'parent_id' => null],

            // CENROs under Abra
            ['id' => 8, 'name' => 'CENRO Bangued', 'parent_id' => 2],
            ['id' => 9, 'name' => 'CENRO Lagangilang', 'parent_id' => 2],

            // CENROs under Apayao
            ['id' => 10, 'name' => 'CENRO Calanasan', 'parent_id' => 3],
            ['id' => 11, 'name' => 'CENRO Conner', 'parent_id' => 3],

            // CENROs under Benguet
            ['id' => 13, 'name' => 'CENRO Baguio', 'parent_id' => 4],
            ['id' => 12, 'name' => 'CENRO Buguias', 'parent_id' => 4],

            // CENROs under Ifugao
            ['id' => 15, 'name' => 'CENRO Alfonso Lista', 'parent_id' => 5],
            ['id' => 14, 'name' => 'CENRO Lamut', 'parent_id' => 5],

            // CENROs under Kalinga
            ['id' => 16, 'name' => 'CENRO Pinukpuk', 'parent_id' => 6],
            ['id' => 17, 'name' => 'CENRO Tabuk', 'parent_id' => 6],

            // CENROs under Mt. Province
            ['id' => 18, 'name' => 'CENRO Paracelis', 'parent_id' => 7],
            ['id' => 19, 'name' => 'CENRO Sabangan', 'parent_id' => 7],
        ]);
    }
}
