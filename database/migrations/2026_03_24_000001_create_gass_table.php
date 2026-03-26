<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('gass', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ppa_id')->nullable()->constrained('ppa')->onDelete('set null');
            $table->foreignId('indicator_id')->nullable()->constrained('indicators')->onDelete('set null');
            $table->json('universe_id')->nullable();
            $table->json('accomplishment_id')->nullable();
            $table->json('targets_id')->nullable();
            $table->timestamps();
        });



    }

    public function down(): void
    {
        Schema::dropIfExists('gass');
    }
};