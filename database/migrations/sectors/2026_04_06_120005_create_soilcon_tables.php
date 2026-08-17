<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('soilcon')) {
            Schema::create('soilcon', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ppa_id')->nullable()->constrained('ppa')->nullOnDelete();
                $table->foreignId('indicator_id')->nullable()->constrained('indicators')->nullOnDelete();
                $table->json('universe_id')->nullable();
                $table->json('accomplishment_id')->nullable();
                $table->json('targets_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('soilcon_universe')) {
            Schema::create('soilcon_universe', function (Blueprint $table) {
                $table->id();
                $table->json('office_ids')->nullable();
                $table->json('values')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('soilcon_universe');
        Schema::dropIfExists('soilcon');
    }
};
