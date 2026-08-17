<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::table('types')->updateOrInsert(
            ['code' => 'ENGP'],
            [
                'desc' => 'Enhanced National Greening Program',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    public function down(): void
    {
        DB::table('types')->where('code', 'ENGP')->delete();
    }
};
