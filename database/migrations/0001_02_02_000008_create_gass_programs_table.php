<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gass_pap', function(Blueprint $table){
            $table->id();
            $table->string('title')->nullable();
            $table->string('program')->nullable();
            $table->string('project')->nullable();
            $table->string('activities')->nullable();
            $table->string('subactivities')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gass_pap');
    }
};