<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('gass_indicators', 'office_id')) {
            Schema::table('gass_indicators', function (Blueprint $table) {
                $table->json('office_id')->nullable()->after('program_id');
            });
        }

        $officeIdType = DB::table('information_schema.COLUMNS')
            ->whereRaw('TABLE_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', 'gass_indicators')
            ->where('COLUMN_NAME', 'office_id')
            ->value('DATA_TYPE');

        if ($officeIdType && strtolower((string) $officeIdType) !== 'json') {
            $constraints = DB::select(
                'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
                ['gass_indicators', 'office_id']
            );

            foreach ($constraints as $constraint) {
                DB::statement("ALTER TABLE gass_indicators DROP FOREIGN KEY {$constraint->CONSTRAINT_NAME}");
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            DB::statement('ALTER TABLE gass_indicators MODIFY office_id JSON NULL');
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }

        DB::statement(
            "UPDATE gass_indicators
            SET office_id = JSON_ARRAY(CAST(JSON_UNQUOTE(office_id) AS UNSIGNED))
            WHERE office_id IS NOT NULL
            AND JSON_TYPE(office_id) <> 'ARRAY'"
        );

        // Keep office_id_old as fallback/history to avoid MySQL FK rebuild errors
        // when dropping columns on this table.
    }

    public function down(): void
    {
        Schema::table('gass_indicators', function (Blueprint $table) {
            $table->unsignedBigInteger('office_id_old')->nullable()->after('office_id');
        });

        DB::table('gass_indicators')
            ->select('id', 'office_id')
            ->whereNotNull('office_id')
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                foreach ($rows as $row) {
                    $decoded = json_decode($row->office_id, true);
                    $firstOfficeId = is_array($decoded) && count($decoded) > 0 ? (int) $decoded[0] : null;

                    DB::table('gass_indicators')
                        ->where('id', $row->id)
                        ->update(['office_id_old' => $firstOfficeId]);
                }
            });

        Schema::table('gass_indicators', function (Blueprint $table) {
            $table->dropColumn('office_id');
        });

        Schema::table('gass_indicators', function (Blueprint $table) {
            $table->foreignId('office_id')->nullable()->after('program_id')->constrained('offices')->onDelete('set null');
        });

        DB::table('gass_indicators')->whereNotNull('office_id_old')->update([
            'office_id' => DB::raw('office_id_old'),
        ]);

        Schema::table('gass_indicators', function (Blueprint $table) {
            $table->dropColumn('office_id_old');
        });
    }
};
