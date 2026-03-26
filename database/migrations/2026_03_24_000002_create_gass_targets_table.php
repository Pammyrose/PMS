<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('gass_targets')) {
            return;
        }

        Schema::create('gass_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_ids')->nullable()->constrained('offices')->nullOnDelete();
            $table->json('values')->nullable();
            $table->json('years')->nullable();


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
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gass_targets');
    }
};