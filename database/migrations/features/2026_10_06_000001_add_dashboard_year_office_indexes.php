<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const TABLES = [
        'physical_targets',
        'physical_accomplishments',
        'financial_target',
        'financial_accomplishment',
    ];

    public function up(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && ! Schema::hasIndex($table, $table.'_dashboard_idx')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->index(['year', 'office_id', 'sector'], $table.'_dashboard_idx');
                });
            }
        }
    }

    public function down(): void
    {
        foreach (self::TABLES as $table) {
            if (Schema::hasTable($table) && Schema::hasIndex($table, $table.'_dashboard_idx')) {
                Schema::table($table, function (Blueprint $blueprint) use ($table) {
                    $blueprint->dropIndex($table.'_dashboard_idx');
                });
            }
        }
    }
};
