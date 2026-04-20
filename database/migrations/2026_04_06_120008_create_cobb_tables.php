<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('cobb')) {
            Schema::create('cobb', function (Blueprint $table) {
                $table->id();
                $table->foreignId('ppa_id')->nullable()->constrained('ppa')->nullOnDelete();
                $table->foreignId('indicator_id')->nullable()->constrained('indicators')->nullOnDelete();
                $table->json('universe_id')->nullable();
                $table->json('accomplishment_id')->nullable();
                $table->json('targets_id')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cobb_universe')) {
            Schema::create('cobb_universe', function (Blueprint $table) {
                $table->id();
                $table->json('office_ids')->nullable();
                $table->json('values')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('cobb_targets')) {
            Schema::create('cobb_targets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('office_ids')->nullable()->constrained('offices')->nullOnDelete();
                $table->json('values')->nullable();
                $table->unsignedInteger('years')->nullable();
                $table->decimal('jan', 12, 2)->default(0);
                $table->decimal('feb', 12, 2)->default(0);
                $table->decimal('mar', 12, 2)->default(0);
                $table->decimal('q1', 12, 2)->default(0);
                $table->decimal('apr', 12, 2)->default(0);
                $table->decimal('may', 12, 2)->default(0);
                $table->decimal('jun', 12, 2)->default(0);
                $table->decimal('q2', 12, 2)->default(0);
                $table->decimal('jul', 12, 2)->default(0);
                $table->decimal('aug', 12, 2)->default(0);
                $table->decimal('sep', 12, 2)->default(0);
                $table->decimal('q3', 12, 2)->default(0);
                $table->decimal('oct', 12, 2)->default(0);
                $table->decimal('nov', 12, 2)->default(0);
                $table->decimal('dec', 12, 2)->default(0);
                $table->decimal('q4', 12, 2)->default(0);
                $table->decimal('annual_total', 12, 2)->default(0);
                $table->timestamps();

                $table->index(['years', 'office_ids']);
            });
        }

        if (!Schema::hasTable('cobb_accomplishments')) {
            Schema::create('cobb_accomplishments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('office_ids')->nullable()->constrained('offices')->nullOnDelete();
                $table->json('values')->nullable();
                $table->json('remarks')->nullable();
                $table->unsignedInteger('years')->nullable();
                $table->decimal('jan', 12, 2)->default(0);
                $table->decimal('feb', 12, 2)->default(0);
                $table->decimal('mar', 12, 2)->default(0);
                $table->decimal('q1', 12, 2)->default(0);
                $table->decimal('apr', 12, 2)->default(0);
                $table->decimal('may', 12, 2)->default(0);
                $table->decimal('jun', 12, 2)->default(0);
                $table->decimal('q2', 12, 2)->default(0);
                $table->decimal('jul', 12, 2)->default(0);
                $table->decimal('aug', 12, 2)->default(0);
                $table->decimal('sep', 12, 2)->default(0);
                $table->decimal('q3', 12, 2)->default(0);
                $table->decimal('oct', 12, 2)->default(0);
                $table->decimal('nov', 12, 2)->default(0);
                $table->decimal('dec', 12, 2)->default(0);
                $table->decimal('q4', 12, 2)->default(0);
                $table->decimal('annual_total', 12, 2)->default(0);
                $table->timestamps();

                $table->index(['years', 'office_ids']);
            });
        }

        $this->copyCobbRowsFromSharedTables();
    }

    public function down(): void
    {
        Schema::dropIfExists('cobb_accomplishments');
        Schema::dropIfExists('cobb_targets');
        Schema::dropIfExists('cobb_universe');
        Schema::dropIfExists('cobb');
    }

    private function copyCobbRowsFromSharedTables(): void
    {
        $cobbTypeId = DB::table('types')->where('code', 'COBB')->value('id');
        if (!$cobbTypeId) {
            return;
        }

        $programTypeById = DB::table('ppa')
            ->pluck('types_id', 'id')
            ->map(fn ($value) => (int) $value)
            ->all();

        if (Schema::hasTable('gass_targets') && DB::table('cobb_targets')->count() === 0) {
            foreach (DB::table('gass_targets')->get() as $row) {
                $values = json_decode($row->values ?? '[]', true);
                $programId = (int) ($values['program_id'] ?? 0);

                if (($programTypeById[$programId] ?? null) !== (int) $cobbTypeId) {
                    continue;
                }

                DB::table('cobb_targets')->insert([
                    'office_ids' => $row->office_ids,
                    'values' => $row->values,
                    'years' => is_numeric($row->years ?? null) ? (int) $row->years : null,
                    'jan' => $row->jan ?? 0,
                    'feb' => $row->feb ?? 0,
                    'mar' => $row->mar ?? 0,
                    'q1' => $row->q1 ?? 0,
                    'apr' => $row->apr ?? 0,
                    'may' => $row->may ?? 0,
                    'jun' => $row->jun ?? 0,
                    'q2' => $row->q2 ?? 0,
                    'jul' => $row->jul ?? 0,
                    'aug' => $row->aug ?? 0,
                    'sep' => $row->sep ?? 0,
                    'q3' => $row->q3 ?? 0,
                    'oct' => $row->oct ?? 0,
                    'nov' => $row->nov ?? 0,
                    'dec' => $row->dec ?? 0,
                    'q4' => $row->q4 ?? 0,
                    'annual_total' => $row->annual_total ?? 0,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }

        if (Schema::hasTable('gass_accomplishments') && DB::table('cobb_accomplishments')->count() === 0) {
            foreach (DB::table('gass_accomplishments')->get() as $row) {
                $values = json_decode($row->values ?? '[]', true);
                $programId = (int) ($values['program_id'] ?? 0);

                if (($programTypeById[$programId] ?? null) !== (int) $cobbTypeId) {
                    continue;
                }

                DB::table('cobb_accomplishments')->insert([
                    'office_ids' => $row->office_ids,
                    'values' => $row->values,
                    'remarks' => $row->remarks,
                    'years' => is_numeric($row->years ?? null) ? (int) $row->years : null,
                    'jan' => $row->jan ?? 0,
                    'feb' => $row->feb ?? 0,
                    'mar' => $row->mar ?? 0,
                    'q1' => $row->q1 ?? 0,
                    'apr' => $row->apr ?? 0,
                    'may' => $row->may ?? 0,
                    'jun' => $row->jun ?? 0,
                    'q2' => $row->q2 ?? 0,
                    'jul' => $row->jul ?? 0,
                    'aug' => $row->aug ?? 0,
                    'sep' => $row->sep ?? 0,
                    'q3' => $row->q3 ?? 0,
                    'oct' => $row->oct ?? 0,
                    'nov' => $row->nov ?? 0,
                    'dec' => $row->dec ?? 0,
                    'q4' => $row->q4 ?? 0,
                    'annual_total' => $row->annual_total ?? 0,
                    'created_at' => $row->created_at ?? now(),
                    'updated_at' => $row->updated_at ?? now(),
                ]);
            }
        }
    }
};
