<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('office_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();           // RO, PENRO, CENRO
            $table->text('desc')->nullable();
            $table->timestamps();
        });



    }
    public function down(): void
    {
        Schema::dropIfExists('office_types');
    }
};