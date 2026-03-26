<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('indicator_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::table('indicators', function (Blueprint $table) {
            $table->foreign('indicator_type_id')
                ->references('id')
                ->on('indicator_types')
                ->nullOnDelete();
        });





    }
    public function down(): void
    {
        Schema::table('indicators', function (Blueprint $table) {
            $table->dropForeign(['indicator_type_id']);
        });

        Schema::dropIfExists('indicator_types');
    }
};