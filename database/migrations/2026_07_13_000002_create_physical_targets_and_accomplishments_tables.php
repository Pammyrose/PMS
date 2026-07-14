<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_TABLES = [
        'gass' => ['gass_targets', 'gass_accomplishments'],
        'sto' => ['sto_targets', 'sto_accomplishments'],
        'enf' => ['enf_targets', 'enf_accomplishments'],
        'pa' => ['pa_targets', 'pa_accomplishments'],
        'engp' => ['engp_targets', 'engp_accomplishments'],
        'lands' => ['lands_targets', 'lands_accomplishments'],
        'soilcon' => ['soilcon_targets', 'soilcon_accomplishments'],
        'nra' => ['nra_targets', 'nra_accomplishments'],
        'paria' => ['paria_targets', 'paria_accomplishments'],
        'cobb' => ['cobb_targets', 'cobb_accomplishments'],
        'continuing' => ['continuing_targets', 'continuing_accomplishments'],
    ];

    private const PERIODS = [
        'jan', 'feb', 'mar', 'q1',
        'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3',
        'oct', 'nov', 'dec', 'q4',
        'annual_total',
    ];

    public function up(): void
    {
        $this->createPhysicalTable('physical_targets');
        $this->createPhysicalTable('physical_accomplishments', true);
        $this->migrateLegacyRows();
    }

    public function down(): void
    {
        Schema::dropIfExists('physical_accomplishments');
        Schema::dropIfExists('physical_targets');
    }

    private function createPhysicalTable(string $tableName, bool $withRemarks = false): void
    {
        if (Schema::hasTable($tableName)) {
            return;
        }

        Schema::create($tableName, function (Blueprint $table) use ($withRemarks) {
            $table->id();
            $table->string('sector', 24);
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('office_id')->nullable()->constrained('offices')->nullOnDelete();
            $table->foreignId('program_id')->constrained('ppa')->cascadeOnDelete();
            $table->foreignId('row_id')->constrained('ppa')->cascadeOnDelete();
            $table->foreignId('indicator_id')->constrained('indicators')->cascadeOnDelete();
            $table->unsignedInteger('year');

            foreach (self::PERIODS as $period) {
                $table->decimal($period, 18, 2)->default(0);
            }

            $table->json('car_totals')->nullable();
            $table->json('group_totals')->nullable();

            if ($withRemarks) {
                $table->text('remarks')->nullable();
            }

            $table->string('imported_from', 50)->nullable();
            $table->timestamps();

            $table->unique(
                ['sector', 'year', 'office_id', 'row_id', 'indicator_id'],
                $withRemarks ? 'physical_accomp_entry_unique' : 'physical_target_entry_unique'
            );
            $table->index(
                ['sector', 'year', 'office_id'],
                $withRemarks ? 'physical_accomp_page_idx' : 'physical_target_page_idx'
            );
        });
    }

    private function migrateLegacyRows(): void
    {
        $validPpaIds = DB::table('ppa')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $validIndicatorIds = DB::table('indicators')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $validOfficeIds = DB::table('offices')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();
        $validUserIds = DB::table('users')->pluck('id')->mapWithKeys(fn ($id) => [(int) $id => true])->all();

        foreach (self::LEGACY_TABLES as $sector => [$targetTable, $accomplishmentTable]) {
            $this->copyLegacyTable(
                $targetTable,
                'physical_targets',
                $sector,
                false,
                $validPpaIds,
                $validIndicatorIds,
                $validOfficeIds,
                $validUserIds
            );
            $this->copyLegacyTable(
                $accomplishmentTable,
                'physical_accomplishments',
                $sector,
                true,
                $validPpaIds,
                $validIndicatorIds,
                $validOfficeIds,
                $validUserIds
            );
        }
    }

    private function copyLegacyTable(
        string $sourceTable,
        string $destinationTable,
        string $sector,
        bool $withRemarks,
        array $validPpaIds,
        array $validIndicatorIds,
        array $validOfficeIds,
        array $validUserIds
    ): void {
        if (! Schema::hasTable($sourceTable)) {
            return;
        }

        DB::table($sourceTable)
            ->orderBy('id')
            ->chunkById(250, function ($rows) use (
                $destinationTable,
                $sector,
                $withRemarks,
                $validPpaIds,
                $validIndicatorIds,
                $validOfficeIds,
                $validUserIds
            ) {
                foreach ($rows as $row) {
                    $meta = $this->decodeObject($row->values ?? null);
                    $programId = (int) ($row->program_id ?? $meta['program_id'] ?? 0);
                    $rowId = (int) ($meta['row_id'] ?? $programId);
                    $indicatorId = (int) ($row->indicator_id ?? $meta['indicator_id'] ?? 0);
                    $year = $this->normalizeYear($row->year ?? $row->years ?? null);

                    if (
                        $year < 2000
                        || ! isset($validPpaIds[$programId], $validPpaIds[$rowId], $validIndicatorIds[$indicatorId])
                    ) {
                        continue;
                    }

                    $officeId = (int) ($row->office_id ?? $row->office_ids ?? 0);
                    $officeId = isset($validOfficeIds[$officeId]) ? $officeId : null;
                    $userId = (int) ($row->user_id ?? $meta['user_id'] ?? 0);
                    $userId = isset($validUserIds[$userId]) ? $userId : null;
                    $identity = [
                        'sector' => $sector,
                        'year' => $year,
                        'office_id' => $officeId,
                        'row_id' => $rowId,
                        'indicator_id' => $indicatorId,
                    ];
                    $values = [
                        'user_id' => $userId,
                        'program_id' => $programId,
                        'car_totals' => json_encode($meta['car_totals'] ?? []),
                        'group_totals' => json_encode($meta['group_totals'] ?? []),
                        'imported_from' => filled($meta['imported_from'] ?? null)
                            ? (string) $meta['imported_from']
                            : null,
                        'created_at' => $row->created_at ?? now(),
                        'updated_at' => $row->updated_at ?? now(),
                    ];

                    foreach (self::PERIODS as $period) {
                        $values[$period] = is_numeric($row->{$period} ?? null)
                            ? (float) $row->{$period}
                            : 0;
                    }

                    if ($withRemarks) {
                        $values['remarks'] = $this->normalizeRemarks($row->remarks ?? null);
                    }

                    DB::table($destinationTable)->updateOrInsert($identity, $values);
                }
            });
    }

    private function decodeObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (! is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeYear(mixed $value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            return is_numeric($decoded) ? (int) $decoded : (int) trim($value, "\"' ");
        }

        return 0;
    }

    private function normalizeRemarks(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (is_string($decoded)) {
                return trim($decoded) !== '' ? $decoded : null;
            }

            if (is_array($decoded) && is_string($decoded['text'] ?? null)) {
                return trim($decoded['text']) !== '' ? $decoded['text'] : null;
            }

            return trim($value) !== '' ? $value : null;
        }

        return null;
    }
};
