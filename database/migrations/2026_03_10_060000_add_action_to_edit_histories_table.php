<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('edit_histories')) {
            return;
        }

        if (!Schema::hasColumn('edit_histories', 'action')) {
            Schema::table('edit_histories', function (Blueprint $table) {
                $table->string('action', 30)->nullable()->after('edited_part');
                $table->index('action');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('edit_histories')) {
            return;
        }

        if (Schema::hasColumn('edit_histories', 'action')) {
            Schema::table('edit_histories', function (Blueprint $table) {
                $table->dropIndex(['action']);
                $table->dropColumn('action');
            });
        }
    }
};
