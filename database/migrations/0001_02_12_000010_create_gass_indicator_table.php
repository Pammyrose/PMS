<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('gass_indicators', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();                     // e.g. "Number of training sessions conducted"
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('program_id')->nullable()->constrained('gass_pap')->onDelete('cascade');
            $table->foreignId('office_id')->nullable()->constrained('offices')->onDelete('set null');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gass_indicators');
    }
};