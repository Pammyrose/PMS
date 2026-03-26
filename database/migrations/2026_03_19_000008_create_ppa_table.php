<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('ppa', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('types_id')->constrained('types')->onDelete('restrict');
            $table->foreignId('record_type_id')->constrained('record_types')->onDelete('restrict');
            $table->foreignId('ppa_details_id')->nullable()->constrained('ppa_details')->onDelete('set null');
            $table->foreignId('indicator_id')->nullable()->constrained('indicators')->onDelete('set null');
            
            // JSON array of office IDs
            $table->json('office_id')->nullable();   // e.g. [1, 3, 7]
            
            $table->timestamps();
        });


    }

    public function down(): void
    {
        Schema::dropIfExists('ppa');
    }
};