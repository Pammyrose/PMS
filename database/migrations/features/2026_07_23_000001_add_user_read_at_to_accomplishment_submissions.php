<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accomplishment_submissions', function (Blueprint $table) {
            $table->timestamp('user_read_at')->nullable()->after('reviewed_at');
            $table->index(['user_id', 'status', 'user_read_at'], 'accomp_submission_notification_idx');
        });
    }

    public function down(): void
    {
        Schema::table('accomplishment_submissions', function (Blueprint $table) {
            $table->dropIndex('accomp_submission_notification_idx');
            $table->dropColumn('user_read_at');
        });
    }
};
