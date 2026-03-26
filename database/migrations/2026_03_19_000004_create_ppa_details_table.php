<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
Schema::create('ppa_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('ppa_details')->onDelete('cascade');
            $table->unsignedInteger('column_order')->default(0);
            $table->timestamps();

            // Optional: prevent circular references
            $table->index(['parent_id']);
        });

    }
    public function down(): void
    {
        Schema::dropIfExists('ppa_details');
    }
};