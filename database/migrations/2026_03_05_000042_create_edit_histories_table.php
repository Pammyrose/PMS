<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('edit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('module', 40);
            $table->string('edited_part', 40);
            $table->string('route_name', 120);
            $table->string('http_method', 10);
            $table->string('record_identifier', 60)->nullable();
            $table->json('changed_fields')->nullable();
            $table->json('request_snapshot')->nullable();
            $table->timestamps();

            $table->index(['module', 'edited_part']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_histories');
    }
};
