<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();                     // e.g. "Number of training sessions conducted"
            $table->string('target')->nullable();          // e.g. "12", "500 beneficiaries", "100%"
            $table->decimal('budget', 15, 2)->nullable();  // e.g. 450000.00
            $table->date('deadline')->nullable();           // optional: to filter by year like 2025
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('program_id')->nullable()->constrained('programs')->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('indicators');
    }
};