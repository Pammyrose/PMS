<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pap', function(Blueprint $table){
            $table->id();
            $table->string('title')->nullable();
            $table->string('program')->nullable();
            $table->string('project')->nullable();
            $table->string('activities')->nullable();
            $table->string('subactivities')->nullable();
            $table->string('indicators')->nullable();
            $table->integer('target');
            $table->decimal('budget', 15, 2);
            $table->foreignId('office_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->json('div')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pap');
    }
};