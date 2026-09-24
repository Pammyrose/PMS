<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accomplishment_submissions', function (Blueprint $table) {
            $table->text('request_reason')->nullable()->after('payload');
        });
    }

    public function down(): void
    {
        Schema::table('accomplishment_submissions', function (Blueprint $table) {
            $table->dropColumn('request_reason');
        });
    }
};
