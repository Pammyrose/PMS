<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('sto_universe', function (Blueprint $table) {
            $table->id();
            $table->json('office_ids')->nullable();
            $table->json('values')->nullable();
            $table->timestamps();
        });




    }
    public function down(): void
    {
        Schema::dropIfExists('sto_universe');
    }
};