<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('paria')) {
            Schema::create('paria', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ppa_id')->nullable()->constrained('ppa')->nullOnDelete();
                $table->foreignId('indicator_id')->nullable()->constrained('indicators')->nullOnDelete();
                $table->json('universe_id')->nullable();
                $table->json('accomplishment_id')->nullable();
                $table->json('targets_id')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('paria_universe')) {
            Schema::create('paria_universe', function (Blueprint $table) {
                $table->id();
                $table->json('office_ids')->nullable();
                $table->json('values')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('paria_universe');
        Schema::dropIfExists('paria');
    }
};
