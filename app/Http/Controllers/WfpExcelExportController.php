<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Support\OfficialWfpTemplateWriter;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WfpExcelExportController extends Controller
{
    private const SECTORS = [
        'gass' => ['type' => 'GASS', 'sheet' => 'GASS', 'label' => 'GENERAL ADMINISTRATION AND SUPPORT SERVICES (GASS)'],
        'sto' => ['type' => 'STO', 'sheet' => 'STO', 'label' => 'SUPPORT TO OPERATIONS (STO)'],
        'enf' => ['type' => 'ENF', 'sheet' => 'NRE&RP', 'label' => 'ENFORCEMENT'],
        'pa' => ['type' => 'Biodiv', 'sheet' => 'PA', 'label' => 'PROTECTED AREAS AND BIODIVERSITY'],
        'engp' => ['type' => 'ENGP', 'sheet' => 'E-NGP', 'label' => 'ENHANCED NATIONAL GREENING PROGRAM'],
        'lands' => ['type' => 'Lands', 'sheet' => 'LANDS', 'label' => 'LANDS MANAGEMENT'],
        'soilcon' => ['type' => 'Soilcon', 'sheet' => 'SOILCON', 'label' => 'SOIL CONSERVATION AND WATERSHED MANAGEMENT'],
        'nra' => ['type' => 'NRA', 'sheet' => 'NRA', 'label' => 'NATURAL RESOURCES ASSESSMENT'],
        'paria' => ['type' => 'PARIA', 'sheet' => 'PARIA', 'label' => 'PROTECTED AREA RETAINED INCOME ACCOUNT'],
        'cobb' => ['type' => 'COBB', 'sheet' => 'COBB', 'label' => 'CAVES, OTHER BIODIVERSITY AND BIODIVERSITY-FRIENDLY BUSINESS'],
        'continuing' => ['type' => 'CONTINUING', 'sheet' => 'CONTINUING', 'label' => 'CONTINUING APPROPRIATIONS'],
    ];

    private const TABLES = [
        'physical_target' => 'physical_targets',
        'physical_accomplishment' => 'physical_accomplishments',
        'financial_target' => 'financial_target',
        'financial_accomplishment' => 'financial_accomplishment',
    ];

    private const PERIODS = [
        'jan', 'feb', 'mar', 'q1', 'apr', 'may', 'jun', 'q2',
        'jul', 'aug', 'sep', 'q3', 'oct', 'nov', 'dec', 'q4', 'annual_total',
    ];

    public function __invoke(
        Request $request,
        string $sector,
        SimpleXlsxWriter $writer,
        OfficialWfpTemplateWriter $officialWriter
    ): BinaryFileResponse {
        $sector = strtolower(trim($sector));
        abort_unless(isset(self::SECTORS[$sector]), 404);

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
        $year = (int) ($validated['year'] ?? now()->year);
        $config = self::SECTORS[$sector];
        $officeIds = $this->scopedOfficeIds();
        $rows = $this->exportRows($sector, $config['type'], $year, $officeIds);
        $allowedOfficeNames = $officeIds === null
            ? null
            : Office::query()->whereIn('id', $officeIds)->pluck('name')->all();
        $path = tempnam(sys_get_temp_dir(), 'pms-wfp-');

        if ($path === false) {
            abort(500, 'Unable to prepare the Excel download.');
        }

        $xlsxPath = $path.'.xlsx';
        @unlink($path);
        $templatePath = resource_path('templates/DENR-CAR-2026-WFP-GAA-MIP.xlsx');
        if (in_array($sector, ['gass', 'sto'], true) && is_file($templatePath)) {
            $officialWriter->write(
                $templatePath,
                $xlsxPath,
                $config['sheet'],
                $rows,
                $this->officialTemplateBlocks($sector, $templatePath, $year),
                now(),
                $allowedOfficeNames
            );
        } else {
            $writer->writeWfp($xlsxPath, $config['sheet'], $config['label'], $year, $rows, now());
        }

        $filename = sprintf(
            'DENR-CAR-%d-WFP-%s-%s.xlsx',
            $year,
            strtoupper($sector),
            now()->format('Ymd-His')
        );

        return response()->download(
            $xlsxPath,
            $filename,
            [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' => 'no-store, no-cache, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0',
            ]
        )->deleteFileAfterSend(true);
    }

    /** @return array<int, array<string, mixed>> */
    private function officialTemplateBlocks(string $sector, string $templatePath, int $year): array
    {
        $controller = app(PhysicalExcelUploadController::class)->forSector($sector);
        $method = new \ReflectionMethod(PhysicalExcelUploadController::class, 'previewStoPhysicalRowsFromExcel');

        $preview = $method->invoke($controller, $templatePath, $year, 'target');

        return is_array($preview['rows'] ?? null) ? $preview['rows'] : [];
    }

    /** @return array<int, array<string, mixed>> */
    private function exportRows(string $sector, string $typeCode, int $year, ?array $officeIds): array
    {
        $records = [];
        $typeId = DB::table('types')->where('code', $typeCode)->value('id');
        // Some legacy P/A/P rows have a null year while their saved target rows do not.
        $ppaRows = $typeId
            ? DB::table('ppa')->where('types_id', $typeId)->get()
            : collect();

        foreach (self::TABLES as $kind => $table) {
            if (! Schema::hasTable($table)) {
                continue;
            }

            $query = DB::table($table)
                ->where('sector', $sector)
                ->where('year', $year)
                ->select(array_merge(
                    ['program_id', 'row_id', 'indicator_id', 'office_id', 'car_totals'],
                    self::PERIODS
                ));

            if ($officeIds !== null) {
                $query->whereIn('office_id', $officeIds);
            }

            foreach ($query->get() as $record) {
                $key = $this->recordKey($record);
                $records[$key] ??= [
                    'program_id' => (int) ($record->program_id ?? 0),
                    'row_id' => (int) ($record->row_id ?? 0),
                    'indicator_id' => (int) ($record->indicator_id ?? 0),
                    'office_id' => filled($record->office_id ?? null) ? (int) $record->office_id : null,
                    'values' => [],
                    'car_totals' => [],
                ];
                $records[$key]['values'][$kind] = $this->periodValues($record);
                $records[$key]['car_totals'][$kind] = $this->decodeTotals($record->car_totals ?? null);
            }
        }

        if ($officeIds !== null) {
            $records = $this->addDefaultOfficeRows($records, $ppaRows, $officeIds, $year);
        }

        if ($records === []) {
            return [];
        }

        $ppaById = $ppaRows->keyBy(fn ($row) => (int) $row->id);
        $ppaByDetail = $ppaRows
            ->filter(fn ($row) => (int) ($row->ppa_details_id ?? 0) > 0)
            ->groupBy(fn ($row) => (int) $row->ppa_details_id);
        $details = DB::table('ppa_details')->get()->keyBy(fn ($row) => (int) $row->id);
        $indicatorIds = collect($records)->pluck('indicator_id')->filter()->unique()->all();
        $indicatorColumns = ['id', 'name'];
        if (Schema::hasColumn('indicators', 'indicator_type_id')) {
            $indicatorColumns[] = 'indicator_type_id';
        }
        $indicatorRows = DB::table('indicators')
            ->whereIn('id', $indicatorIds)
            ->get($indicatorColumns);
        $indicatorNames = $indicatorRows->pluck('name', 'id');
        $indicatorTypeNames = Schema::hasTable('indicator_types')
            ? DB::table('indicator_types')->pluck('name', 'id')
            : collect();
        $indicatorTypes = $indicatorRows->mapWithKeys(fn ($indicator) => [
            (int) $indicator->id => (string) ($indicatorTypeNames[(int) ($indicator->indicator_type_id ?? 0)] ?? ''),
        ]);
        $offices = DB::table('offices')
            ->whereIn('id', collect($records)->pluck('office_id')->filter()->unique()->all())
            ->get()
            ->keyBy(fn ($row) => (int) $row->id);

        $logicalGroups = collect($records)->groupBy(fn (array $record) => implode('|', [
            $record['program_id'], $record['row_id'], $record['indicator_id'],
        ]));
        $output = [];

        foreach ($logicalGroups as $group) {
            $first = $group->first();
            $pap = $this->hierarchyLabel(
                (int) $first['row_id'],
                (int) $first['program_id'],
                $ppaById,
                $ppaByDetail,
                $details
            );
            $indicator = trim((string) ($indicatorNames[$first['indicator_id']] ?? ''));
            $indicatorType = trim((string) ($indicatorTypes[$first['indicator_id']] ?? ''));

            if ($officeIds === null) {
                $output[] = $this->outputRow(
                    $pap,
                    $indicator,
                    $indicatorType,
                    'CAR',
                    $this->carValuesForGroup($group)
                );
            }

            foreach ($group->sortBy(function (array $record) use ($offices) {
                $office = $offices[$record['office_id']] ?? null;

                return sprintf('%03d|%s', (int) ($office->office_types_id ?? 999), (string) ($office->name ?? ''));
            }, SORT_NATURAL | SORT_FLAG_CASE) as $record) {
                $officeName = $record['office_id'] === null
                    ? 'CAR'
                    : (string) ($offices[$record['office_id']]->name ?? 'Office '.$record['office_id']);
                $output[] = $this->outputRow($pap, $indicator, $indicatorType, $officeName, $record['values']);
            }
        }

        return collect($output)
            ->sortBy(fn (array $row) => mb_strtolower((string) ($row['_sort'] ?? '')), SORT_NATURAL)
            ->map(function (array $row) {
                unset($row['_sort']);

                return $row;
            })
            ->values()
            ->all();
    }

    /**
     * Ensure every scoped office is represented for each P/A/P and indicator,
     * even before target or accomplishment values have been entered.
     *
     * @param  array<string, array<string, mixed>>  $records
     * @param  Collection<int, object>  $ppaRows
     * @param  array<int, int>  $officeIds
     * @return array<string, array<string, mixed>>
     */
    private function addDefaultOfficeRows(array $records, Collection $ppaRows, array $officeIds, int $year): array
    {
        $officeIds = collect($officeIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();

        if ($officeIds === []) {
            return $records;
        }

        $groups = collect($records)
            ->map(fn (array $record): array => [
                'program_id' => (int) ($record['program_id'] ?? 0),
                'row_id' => (int) ($record['row_id'] ?? 0),
                'indicator_id' => (int) ($record['indicator_id'] ?? 0),
            ])
            ->filter(fn (array $group): bool => $group['row_id'] > 0 && $group['indicator_id'] > 0)
            ->keyBy(fn (array $group): string => implode('|', $group));

        foreach ($ppaRows as $ppa) {
            $ppaYear = filled($ppa->year ?? null) ? (int) $ppa->year : null;
            $indicatorId = (int) ($ppa->indicator_id ?? 0);
            if ($indicatorId <= 0 || ($ppaYear !== null && $ppaYear !== $year)) {
                continue;
            }

            $rowId = (int) ($ppa->id ?? 0);
            $alreadyRepresented = $groups->contains(
                fn (array $group): bool => $group['row_id'] === $rowId
                    && $group['indicator_id'] === $indicatorId
            );
            if (! $alreadyRepresented) {
                $group = [
                    'program_id' => $rowId,
                    'row_id' => $rowId,
                    'indicator_id' => $indicatorId,
                ];
                $groups->put(implode('|', $group), $group);
            }
        }

        foreach ($groups as $group) {
            foreach ($officeIds as $officeId) {
                $key = implode('|', [
                    $group['program_id'], $group['row_id'], $group['indicator_id'], $officeId,
                ]);
                $records[$key] ??= [
                    ...$group,
                    'office_id' => $officeId,
                    'values' => [],
                    'car_totals' => [],
                ];
            }
        }

        return $records;
    }

    /**
     * A PENRO export covers the provincial office and all CENROs assigned to it.
     * Regional and administrator exports remain region-wide.
     *
     * @return array<int, int>|null
     */
    private function scopedOfficeIds(): ?array
    {
        if (! $this->shouldScopeToUserOffice()) {
            return null;
        }

        $user = auth()->user();
        $officeId = (int) ($user?->office_id ?? 0);

        if ($user?->isPenro()) {
            $serviceAreaIds = Office::serviceAreaOfficeIdsForPenro($officeId);

            if ($serviceAreaIds !== []) {
                return $serviceAreaIds;
            }
        }

        return [$officeId];
    }

    private function recordKey(object $record): string
    {
        return implode('|', [
            (int) ($record->program_id ?? 0),
            (int) ($record->row_id ?? 0),
            (int) ($record->indicator_id ?? 0),
            filled($record->office_id ?? null) ? (int) $record->office_id : 'car',
        ]);
    }

    /** @return array<string, float> */
    private function periodValues(object $record): array
    {
        $values = [];
        foreach (self::PERIODS as $period) {
            $values[$period] = is_numeric($record->{$period} ?? null) ? (float) $record->{$period} : 0.0;
        }

        return $values;
    }

    /** @return array<string, float> */
    private function decodeTotals(mixed $value): array
    {
        if (is_string($value)) {
            $value = json_decode($value, true);
        }
        if (! is_array($value)) {
            return [];
        }

        $totals = [];
        foreach (self::PERIODS as $period) {
            $totals[$period] = is_numeric($value[$period] ?? null) ? (float) $value[$period] : 0.0;
        }

        return $totals;
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $group
     * @return array<string, array<string, float>>
     */
    private function carValuesForGroup(Collection $group): array
    {
        $result = [];
        foreach (array_keys(self::TABLES) as $kind) {
            $saved = $group
                ->map(fn (array $record) => $record['car_totals'][$kind] ?? [])
                ->first(fn (array $totals) => $this->hasValues($totals));

            if (is_array($saved) && $saved !== []) {
                $result[$kind] = $saved;

                continue;
            }

            $result[$kind] = array_fill_keys(self::PERIODS, 0.0);
            foreach ($group as $record) {
                foreach (self::PERIODS as $period) {
                    $result[$kind][$period] += (float) ($record['values'][$kind][$period] ?? 0);
                }
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $values */
    private function hasValues(array $values): bool
    {
        return collect($values)->contains(fn ($value) => is_numeric($value) && (float) $value != 0.0);
    }

    /**
     * @param  Collection<int, object>  $ppaById
     * @param  Collection<int, Collection<int, object>>  $ppaByDetail
     * @param  Collection<int, object>  $details
     */
    private function hierarchyLabel(
        int $rowId,
        int $programId,
        Collection $ppaById,
        Collection $ppaByDetail,
        Collection $details
    ): string {
        $leaf = $ppaById[$rowId] ?? $ppaById[$programId] ?? null;
        if (! $leaf) {
            return 'P/A/P #'.($rowId ?: $programId);
        }

        $names = [];
        $detailId = (int) ($leaf->ppa_details_id ?? 0);
        $visited = [];
        while ($detailId > 0 && ! isset($visited[$detailId])) {
            $visited[$detailId] = true;
            $ppa = $ppaByDetail->get($detailId)?->first();
            $name = trim((string) ($ppa->name ?? ''));
            if ($name !== '' && ! in_array(mb_strtolower($name), ['n/a', 'na', 'not applicable'], true)) {
                array_unshift($names, $name);
            }
            $detailId = (int) ($details->get($detailId)->parent_id ?? 0);
        }

        if ($names === []) {
            $names[] = trim((string) $leaf->name);
        }

        return implode("\n", array_values(array_unique($names)));
    }

    /**
     * @param  array<string, array<string, float>>  $values
     * @return array<string, mixed>
     */
    private function outputRow(
        string $pap,
        string $indicator,
        string $indicatorType,
        string $office,
        array $values
    ): array {
        $officeSort = strcasecmp($office, 'CAR') === 0 ? '000|CAR' : '100|'.$office;

        return [
            'pap' => $pap,
            'indicator' => $indicator,
            'indicator_type' => $indicatorType,
            'office' => $office,
            'expense_class' => 'TOTAL',
            'physical_target' => $values['physical_target'] ?? [],
            'financial_target' => $values['financial_target'] ?? [],
            'physical_accomplishment' => $values['physical_accomplishment'] ?? [],
            'financial_accomplishment' => $values['financial_accomplishment'] ?? [],
            '_sort' => implode('|', [$pap, $indicator, $officeSort]),
        ];
    }
}
