<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
public function up(): void
    {
        Schema::create('physical', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('office_id');
            $table->unsignedBigInteger('programs_id');  // ← NOT nullable

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('office_id')
                ->references('id')
                ->on('offices')
                ->onDelete('restrict');

            $table->foreign('programs_id')
                ->references('id')
                ->on('programs')
                ->onDelete('restrict');

            // Main fields
            $table->string('performance_indicator', 300)->nullable();
            $table->unsignedInteger('target')->default(0);

            $table->enum('period_type', ['monthly', 'quarterly', 'semiannual', 'annual'])
                ->default('monthly');
            $table->unsignedInteger('year')->default(now()->year);

            $table->unsignedInteger('jan')->default(0);
            $table->unsignedInteger('feb')->default(0);
            $table->unsignedInteger('mar')->default(0);
            $table->unsignedInteger('apr')->default(0);
            $table->unsignedInteger('may')->default(0);
            $table->unsignedInteger('jun')->default(0);
            $table->unsignedInteger('jul')->default(0);
            $table->unsignedInteger('aug')->default(0);
            $table->unsignedInteger('sep')->default(0);
            $table->unsignedInteger('oct')->default(0);
            $table->unsignedInteger('nov')->default(0);
            $table->unsignedInteger('dec')->default(0);

            $table->unsignedInteger('q1')->default(0);
            $table->unsignedInteger('q2')->default(0);
            $table->unsignedInteger('q3')->default(0);
            $table->unsignedInteger('q4')->default(0);

            $table->unsignedInteger('first_half')->default(0);
            $table->unsignedInteger('second_half')->default(0);

            $table->unsignedInteger('annual_total')->default(0);

            $table->text('remarks')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('physical');
    }
};