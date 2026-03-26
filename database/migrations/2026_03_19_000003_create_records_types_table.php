<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('record_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();   // PROGRAM, PROJECT, MAIN ACTIVITY, ...
            $table->text('desc')->nullable();
            $table->timestamps();
        });




    }
    public function down(): void
    {
        Schema::dropIfExists('record_types');
    }
};