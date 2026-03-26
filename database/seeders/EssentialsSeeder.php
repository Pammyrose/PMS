<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Office;
use Illuminate\Support\Facades\DB;

class EssentialsSeeder extends Seeder
{
    public function run(): void
    {
        // Create Office Types
        $officeTypes = [
            ['name' => 'RO', 'desc' => 'Regional Office'],
            ['name' => 'PENRO', 'desc' => 'Provincial Environment and Natural Resources Office'],
            ['name' => 'CENRO', 'desc' => 'Community Environment and Natural Resources Office'],
        ];

        foreach ($officeTypes as $type) {
            DB::table('office_types')->updateOrInsert(
                ['name' => $type['name']],
                $type
            );
        }

        // Create Offices
        $offices = [
            ['name' => 'RO', 'office_types_id' => 1],
            ['name' => 'ABRA', 'office_types_id' => 2],
            ['name' => 'APAYAO', 'office_types_id' => 2],
            ['name' => 'BENGUET', 'office_types_id' => 2],
            ['name' => 'IFUGAO', 'office_types_id' => 2],
            ['name' => 'KALINGA', 'office_types_id' => 2],
            ['name' => 'MT.PROVINCE', 'office_types_id' => 2],

            //CENRO's
            ['name' => 'BUGUIAS', 'office_types_id' => 3],
            ['name' => 'BAGUIO', 'office_types_id' => 3],
            ['name' => 'PARACELIS', 'office_types_id' => 3],
            ['name' => 'SABANGAN', 'office_types_id' => 3],
            ['name' => 'BANGUED', 'office_types_id' => 3],
            ['name' => 'LAGANGILANG', 'office_types_id' => 3],
            ['name' => 'LAMUT', 'office_types_id' => 3],
            ['name' => 'ALFONSO LISTA', 'office_types_id' => 3],
            ['name' => 'CALANASAN', 'office_types_id' => 3],
            ['name' => 'CONNER', 'office_types_id' => 3],
            ['name' => 'PINUKPUK', 'office_types_id' => 3],
            ['name' => 'TABUK', 'office_types_id' => 3],
        ];

        foreach ($offices as $office) {
            Office::firstOrCreate(['name' => $office['name']], $office);
        }

        // Create Record Types
        $recordTypes = [
            ['name' => 'PROGRAM', 'desc' => 'Program level record'],
            ['name' => 'PROJECT', 'desc' => 'Project level record'],
            ['name' => 'MAIN ACTIVITY', 'desc' => 'Main activity record'],
            ['name' => 'SUB-ACTIVITY', 'desc' => 'Sub-activity record'],
            ['name' => 'SUB-SUB-ACTIVITY', 'desc' => 'Sub-sub-activity record'],
        ];

        foreach ($recordTypes as $type) {
            DB::table('record_types')->updateOrInsert(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
