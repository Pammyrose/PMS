<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ppa_details', function (Blueprint $table) {
            $table->unsignedInteger('source_order')->nullable()->after('column_order')->index();
        });
    }

    public function down(): void
    {
        Schema::table('ppa_details', function (Blueprint $table) {
            $table->dropColumn('source_order');
        });
    }
};
