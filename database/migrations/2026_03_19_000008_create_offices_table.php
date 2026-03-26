<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('office_types_id')->constrained('office_types')->onDelete('restrict');
            $table->timestamps();
        });


    }
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};