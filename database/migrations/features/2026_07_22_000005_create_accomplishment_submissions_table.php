<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accomplishment_submissions', function (Blueprint $table) {
            $table->id();
            $table->string('submission_type', 16);
            $table->string('sector', 24);
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('penro_office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('program_id')->constrained('ppa')->cascadeOnDelete();
            $table->foreignId('row_id')->constrained('ppa')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->unsignedInteger('year');
            $table->json('payload');
            $table->string('status', 16)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['penro_office_id', 'status', 'created_at'], 'accomp_submission_review_idx');
            $table->index(['user_id', 'status', 'created_at'], 'accomp_submission_user_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accomplishment_submissions');
    }
};
