<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gass_accomplishment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('program_id')->constrained('gass_pap')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('gass_indicators')->cascadeOnDelete();

            $table->unsignedInteger('year')->default(now()->year);

            $table->decimal('jan', 12, 2)->default(0);
            $table->decimal('feb', 12, 2)->default(0);
            $table->decimal('mar', 12, 2)->default(0);
            $table->decimal('q1', 12, 2)->default(0);
            $table->decimal('apr', 12, 2)->default(0);
            $table->decimal('may', 12, 2)->default(0);
            $table->decimal('jun', 12, 2)->default(0);
            $table->decimal('q2', 12, 2)->default(0);
            $table->decimal('jul', 12, 2)->default(0);
            $table->decimal('aug', 12, 2)->default(0);
            $table->decimal('sep', 12, 2)->default(0);
            $table->decimal('q3', 12, 2)->default(0);
            $table->decimal('oct', 12, 2)->default(0);
            $table->decimal('nov', 12, 2)->default(0);
            $table->decimal('dec', 12, 2)->default(0);
            $table->decimal('q4', 12, 2)->default(0);
            $table->decimal('annual_total', 12, 2)->default(0);

            $table->timestamps();

            $table->unique(['indicator_id', 'year', 'office_id'], 'gass_accomp_indicator_year_office_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gass_accomplishment');
    }
};
