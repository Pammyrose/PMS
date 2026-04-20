<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (Schema::hasTable('edit_histories')) {
            return;
        }

        Schema::create('edit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('user_name')->nullable();
            $table->string('user_role', 50)->nullable();
            $table->string('module', 50)->index();
            $table->string('edited_part', 100)->nullable()->index();
            $table->string('action', 50)->nullable()->index();
            $table->string('route_name')->nullable();
            $table->string('http_method', 15)->nullable();
            $table->string('record_identifier')->nullable();
            $table->json('changed_fields')->nullable();
            $table->json('request_snapshot')->nullable();
            $table->timestamps();

            $table->index(['module', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('edit_histories');
    }
};
