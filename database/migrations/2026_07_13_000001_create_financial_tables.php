<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->createFinancialTable('financial_target');
        $this->createFinancialTable('financial_accomplishment');
    }

    public function down(): void
    {
        Schema::dropIfExists('financial_accomplishment');
        Schema::dropIfExists('financial_target');
    }

    private function createFinancialTable(string $name): void
    {
        if (Schema::hasTable($name)) {
            return;
        }

        Schema::create($name, function (Blueprint $table) use ($name) {
            $table->id();
            $table->string('sector', 24);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('program_id')->constrained('ppa')->cascadeOnDelete();
            $table->foreignId('row_id')->constrained('ppa')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->unsignedInteger('year');

            foreach (['jan', 'feb', 'mar', 'q1', 'apr', 'may', 'jun', 'q2', 'jul', 'aug', 'sep', 'q3', 'oct', 'nov', 'dec', 'q4', 'annual_total'] as $period) {
                $table->decimal($period, 18, 2)->default(0);
            }

            $table->json('car_totals')->nullable();
            $table->json('group_totals')->nullable();
            $table->timestamps();

            $table->unique(
                ['sector', 'year', 'office_id', 'row_id', 'indicator_id'],
                $name.'_entry_unique'
            );
            $table->index(['sector', 'year', 'office_id'], $name.'_page_idx');
        });
    }
};
