<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('gass_accomplishment', 'remarks')) {
            Schema::table('gass_accomplishment', function (Blueprint $table) {
                $table->text('remarks')->nullable()->after('annual_total');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('gass_accomplishment', 'remarks')) {
            Schema::table('gass_accomplishment', function (Blueprint $table) {
                $table->dropColumn('remarks');
            });
        }
    }
};
