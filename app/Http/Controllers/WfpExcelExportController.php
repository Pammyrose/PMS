<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Support\GassUiReport;
use App\Support\OfficialWfpTemplateWriter;
use App\Support\PhysicalPerformanceSectionFormatter;
use App\Support\SimpleXlsxWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class WfpExcelExportController extends Controller
{
    private const OFFICIAL_TEMPLATE_SECTORS = [];

    private const FINANCIAL_SUMMARY_OFFICES = [
        'CAR' => null,
        'RO' => ['RO'],
        'ABRA' => ['ABRA', 'BANGUED', 'LAGANGILANG'],
        'APAYAO' => ['APAYAO', 'CALANASAN', 'CONNER'],
        'BENGUET' => ['BENGUET', 'BAGUIO', 'BUGUIAS'],
        'IFUGAO' => ['IFUGAO', 'ALFONSOLISTA', 'LAMUT'],
        'KALINGA' => ['KALINGA', 'PINUKPUK', 'TABUK'],
        'MT.PROVINCE' => ['MOUNTAINPROVINCE', 'MTPROVINCE', 'PARACELIS', 'SABANGAN'],
    ];

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
        OfficialWfpTemplateWriter $officialWriter,
        PhysicalPerformanceSectionFormatter $physicalFormatter
    ): BinaryFileResponse {
        $sector = strtolower(trim($sector));
        abort_unless(isset(self::SECTORS[$sector]), 404);

        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);
        $year = (int) ($validated['year'] ?? now()->year);
        $config = self::SECTORS[$sector];
        $officeIds = $this->scopedOfficeIds();
        $rows = app(GassUiReport::class)->rows($request, $year, $sector);
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
        if (in_array($sector, self::OFFICIAL_TEMPLATE_SECTORS, true) && is_file($templatePath)) {
            $officialWriter->write(
                $templatePath,
                $xlsxPath,
                $config['sheet'],
                $rows,
                $this->officialTemplateBlocks($sector, $templatePath, $year),
                now(),
                $allowedOfficeNames
            );
            $physicalFormatter->format(
                $xlsxPath,
                $config['sheet'],
                ['I', 'J', 'K'],
                ['AU', 'AV', 'AW'],
                11,
                ['AB', 'AC', 'AD'],
                ['BN', 'BO', 'BP']
            );
        } else {
            $writer->writePerformanceReport(
                $xlsxPath,
                $config['sheet'],
                $config['label'],
                $year,
                $rows,
                now()
            );
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

            $columns = array_merge(
                ['program_id', 'row_id', 'indicator_id', 'office_id', 'car_totals'],
                self::PERIODS
            );
            if ($kind === 'physical_accomplishment' && Schema::hasColumn($table, 'remarks')) {
                $columns[] = 'remarks';
            }

            $query = DB::table($table)
                ->where('sector', $sector)
                ->where('year', $year)
                ->select($columns);

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
                if ($kind === 'physical_accomplishment' && filled($record->remarks ?? null)) {
                    $records[$key]['remarks'] = trim((string) $record->remarks);
                }
            }
        }

        $records = $this->alignRecordsWithUiAssignments($records, $ppaRows, $officeIds, $year);

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
        $uiOfficeGroups = Office::groupedForUi();

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

            foreach ($this->uiOfficeRowsForGroup($group, $offices, $uiOfficeGroups) as $officeIndex => $officeRow) {
                $outputRow = $this->outputRow(
                    $pap,
                    $indicator,
                    $indicatorType,
                    $officeRow['office'],
                    $officeRow['values'],
                    $officeRow['remarks']
                );
                $outputRow['_sort'] = implode('|', [
                    $pap,
                    $indicator,
                    str_pad((string) $officeIndex, 4, '0', STR_PAD_LEFT),
                ]);
                $outputRow['_financial_office'] = $officeRow['financial_office'];
                $outputRow['_office_aggregate'] = $officeRow['is_aggregate'];
                $output[] = $outputRow;
            }
        }

        $output = collect($output)
            ->sortBy(fn (array $row) => mb_strtolower((string) ($row['_sort'] ?? '')), SORT_NATURAL)
            ->map(function (array $row) {
                unset($row['_sort']);

                return $row;
            })
            ->values()
            ->all();

        return $sector === 'gass' ? $this->withSystemFinancialSummaryRows($output) : $output;
    }

    /**
     * Use the same P/A/P, indicator, and office assignments that build the UI.
     * Saved values belonging to an office that is no longer assigned must not
     * reappear in Excel, while newly assigned offices must still export blank.
     *
     * @param  array<string, array<string, mixed>>  $records
     * @param  Collection<int, object>  $ppaRows
     * @param  array<int, int>|null  $officeIds
     * @return array<string, array<string, mixed>>
     */
    private function alignRecordsWithUiAssignments(
        array $records,
        Collection $ppaRows,
        ?array $officeIds,
        int $year
    ): array
    {
        $scope = $officeIds === null
            ? null
            : collect($officeIds)
                ->map(fn ($id) => (int) $id)
                ->filter(fn (int $id) => $id > 0)
                ->unique()
                ->values()
                ->all();
        $assignments = [];

        foreach ($ppaRows as $ppa) {
            $ppaYear = filled($ppa->year ?? null) ? (int) $ppa->year : null;
            $indicatorId = (int) ($ppa->indicator_id ?? 0);
            if ($indicatorId <= 0 || ($ppaYear !== null && $ppaYear !== $year)) {
                continue;
            }

            $rowId = (int) ($ppa->id ?? 0);
            if ($rowId <= 0) {
                continue;
            }

            $assignedOfficeIds = $this->parseOfficeIds($ppa->office_id ?? null);
            if ($scope !== null) {
                $assignedOfficeIds = array_values(array_intersect($assignedOfficeIds, $scope));
            }

            $key = $rowId.'|'.$indicatorId;
            $assignments[$key] = [
                'program_id' => $rowId,
                'row_id' => $rowId,
                'indicator_id' => $indicatorId,
                'office_ids' => $assignedOfficeIds,
            ];
        }

        if ($assignments === []) {
            return [];
        }

        $validPpaIds = $ppaRows
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->flip();
        $aligned = [];
        $groupsByAssignment = [];

        foreach ($records as $record) {
            $programId = (int) ($record['program_id'] ?? 0);
            $rowId = (int) ($record['row_id'] ?? 0);
            $indicatorId = (int) ($record['indicator_id'] ?? 0);
            $officeId = (int) ($record['office_id'] ?? 0);
            $assignmentKey = null;

            foreach ([$programId.'|'.$indicatorId, $rowId.'|'.$indicatorId] as $candidate) {
                if (isset($assignments[$candidate])) {
                    $assignmentKey = $candidate;
                    break;
                }
            }

            if (
                $assignmentKey === null
                || $officeId <= 0
                || ! in_array($officeId, $assignments[$assignmentKey]['office_ids'], true)
                || ($rowId > 0 && ! $validPpaIds->has($rowId))
            ) {
                continue;
            }

            $key = implode('|', [$programId, $rowId, $indicatorId, $officeId]);
            $aligned[$key] = $record;
            $groupKey = implode('|', [$programId, $rowId, $indicatorId]);
            $groupsByAssignment[$assignmentKey][$groupKey] = [
                'program_id' => $programId,
                'row_id' => $rowId,
                'indicator_id' => $indicatorId,
            ];
        }

        foreach ($assignments as $assignmentKey => $assignment) {
            $groups = array_values($groupsByAssignment[$assignmentKey] ?? [[
                'program_id' => $assignment['program_id'],
                'row_id' => $assignment['row_id'],
                'indicator_id' => $assignment['indicator_id'],
            ]]);

            foreach ($groups as $group) {
                foreach ($assignment['office_ids'] as $officeId) {
                    $key = implode('|', [
                        $group['program_id'], $group['row_id'], $group['indicator_id'], $officeId,
                    ]);
                    $aligned[$key] ??= [
                        ...$group,
                        'office_id' => $officeId,
                        'values' => [],
                        'car_totals' => [],
                        'remarks' => '',
                    ];
                }
            }

            if ($assignment['office_ids'] === []) {
                foreach ($groups as $group) {
                    $key = implode('|', [
                        $group['program_id'], $group['row_id'], $group['indicator_id'], 'none',
                    ]);
                    $aligned[$key] ??= [
                        ...$group,
                        'office_id' => null,
                        'values' => [],
                        'car_totals' => [],
                        'remarks' => '',
                    ];
                }
            }
        }

        return $aligned;
    }

    /** @return array<int, int> */
    private function parseOfficeIds(mixed $value): array
    {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            $value = is_array($decoded) ? $decoded : [];
        }

        if (! is_array($value)) {
            $value = is_numeric($value) ? [$value] : [];
        }

        return collect($value)
            ->map(fn ($id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
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
            // The UI calculates CAR from its currently assigned office rows.
            // Stored CAR totals can include offices that were later unassigned.
            $result[$kind] = array_fill_keys(self::PERIODS, 0.0);
            foreach ($group as $record) {
                foreach (self::PERIODS as $period) {
                    $result[$kind][$period] += (float) ($record['values'][$kind][$period] ?? 0);
                }
            }
        }

        return $result;
    }

    /**
     * Build the same office-line hierarchy shown in the UI: CAR, RO, then a
     * province subtotal followed by its selected PENRO and CENRO offices.
     *
     * @param  Collection<int, array<string, mixed>>  $group
     * @param  Collection<int, object>  $officesById
     * @param  Collection<int, Office>  $uiOfficeGroups
     * @return array<int, array{office:string, values:array<string, mixed>, remarks:string, financial_office:?string, is_aggregate:bool}>
     */
    private function uiOfficeRowsForGroup(
        Collection $group,
        Collection $officesById,
        Collection $uiOfficeGroups
    ): array {
        $rows = [[
            'office' => 'CAR',
            'values' => $this->carValuesForGroup($group),
            'remarks' => '',
            'financial_office' => null,
            'is_aggregate' => true,
        ]];
        $recordsByOffice = $group
            ->filter(fn (array $record): bool => (int) ($record['office_id'] ?? 0) > 0)
            ->keyBy(fn (array $record): int => (int) $record['office_id']);
        $usedOfficeIds = [];

        foreach ($uiOfficeGroups as $parent) {
            $parentId = (int) ($parent->id ?? 0);
            $children = collect($parent->children ?? []);
            $selectedIds = collect([$parentId, ...$children->pluck('id')->map(fn ($id) => (int) $id)->all()])
                ->filter(fn (int $id): bool => $recordsByOffice->has($id))
                ->values();

            if ($selectedIds->isEmpty()) {
                continue;
            }

            $isPenro = (int) ($parent->office_types_id ?? 0) === 2
                || preg_match('/\bPENRO\b/i', (string) ($parent->name ?? '')) === 1;
            if ($isPenro) {
                $province = preg_replace('/\b(?:PENRO|CENRO|TOTAL)\b/i', '', (string) ($parent->name ?? '')) ?? '';
                $province = trim(preg_replace('/\s+/', ' ', $province) ?? $province);
                $provinceRecords = $selectedIds
                    ->map(fn (int $id) => $recordsByOffice->get($id))
                    ->filter()
                    ->values();
                $rows[] = [
                    'office' => $province !== '' ? $province : (string) ($parent->name ?? ''),
                    'values' => $this->carValuesForGroup($provinceRecords),
                    'remarks' => '',
                    'financial_office' => null,
                    'is_aggregate' => true,
                ];
            }

            if ($recordsByOffice->has($parentId)) {
                $rows[] = [
                    'office' => $isPenro ? 'PENRO' : (string) ($parent->name ?? ''),
                    'values' => $recordsByOffice->get($parentId)['values'] ?? [],
                    'remarks' => (string) ($recordsByOffice->get($parentId)['remarks'] ?? ''),
                    'financial_office' => (string) ($parent->name ?? ''),
                    'is_aggregate' => false,
                ];
                $usedOfficeIds[$parentId] = true;
            }

            foreach ($children as $child) {
                $childId = (int) ($child->id ?? 0);
                if (! $recordsByOffice->has($childId)) {
                    continue;
                }

                $rows[] = [
                    'office' => (string) ($child->name ?? ''),
                    'values' => $recordsByOffice->get($childId)['values'] ?? [],
                    'remarks' => (string) ($recordsByOffice->get($childId)['remarks'] ?? ''),
                    'financial_office' => (string) ($child->name ?? ''),
                    'is_aggregate' => false,
                ];
                $usedOfficeIds[$childId] = true;
            }
        }

        foreach ($recordsByOffice as $officeId => $record) {
            if (isset($usedOfficeIds[(int) $officeId])) {
                continue;
            }

            $rows[] = [
                'office' => (string) ($officesById->get((int) $officeId)->name ?? 'Office '.$officeId),
                'values' => $record['values'] ?? [],
                'remarks' => (string) ($record['remarks'] ?? ''),
                'financial_office' => (string) ($officesById->get((int) $officeId)->name ?? ''),
                'is_aggregate' => false,
            ];
        }

        if ($recordsByOffice->isEmpty()) {
            $rows[] = [
                'office' => 'N/A',
                'values' => [],
                'remarks' => '',
                'financial_office' => null,
                'is_aggregate' => true,
            ];
        }

        return $rows;
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
        array $values,
        string $remarks = ''
    ): array {
        $officeSort = strcasecmp($office, 'CAR') === 0 ? '000|CAR' : '100|'.$office;
        $hierarchy = collect(preg_split('/\R/', trim($pap)) ?: [])
            ->map(fn ($value) => trim((string) $value))
            ->filter()
            ->values();
        $programHierarchy = $hierarchy->take(3);
        $programLabel = $programHierarchy->implode("\n");
        $programKey = $programHierarchy
            ->map(fn (string $value) => mb_strtolower(preg_replace('/\s+/', ' ', $value) ?? $value))
            ->implode('|');

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
            'remarks' => $remarks,
            '_sort' => implode('|', [$pap, $indicator, $officeSort]),
            '_program_key' => $programKey,
            '_program_label' => $programLabel,
        ];
    }

    /** @param array<int, array<string, mixed>> $rows */
    private function withSystemFinancialSummaryRows(array $rows): array
    {
        return collect($rows)
            ->groupBy('_program_key', preserveKeys: true)
            ->flatMap(function (Collection $group): array {
                $detailRows = $group->values()->all();
                $label = (string) ($detailRows[0]['_program_label'] ?? $detailRows[0]['pap'] ?? '');
                $summaryRows = [];

                foreach (self::FINANCIAL_SUMMARY_OFFICES as $office => $members) {
                    $matchingRows = collect($detailRows)->filter(function (array $row) use ($members): bool {
                        if ((bool) ($row['_office_aggregate'] ?? false)) {
                            return false;
                        }

                        $normalized = $this->normalizeExportOffice((string) (
                            $row['_financial_office'] ?? $row['office'] ?? ''
                        ));
                        if ($members === null) {
                            return $normalized !== 'CAR';
                        }

                        return in_array($normalized, $members, true);
                    });
                    if ($members === null && $matchingRows->isEmpty()) {
                        $matchingRows = collect($detailRows)->filter(
                            fn (array $row): bool => ! (bool) ($row['_office_aggregate'] ?? false)
                                && $this->normalizeExportOffice((string) (
                                    $row['_financial_office'] ?? $row['office'] ?? ''
                                )) === 'CAR'
                        );
                    }

                    $summaryRows[] = [
                        'pap' => $label,
                        'indicator' => '',
                        'indicator_type' => 'Cumulative',
                        'office' => $office,
                        'physical_target' => [],
                        'physical_accomplishment' => [],
                        'financial_target' => $this->sumFinancialPeriods($matchingRows, 'financial_target'),
                        'financial_accomplishment' => $this->sumFinancialPeriods($matchingRows, 'financial_accomplishment'),
                        '_program_key' => $detailRows[0]['_program_key'] ?? '',
                        '_program_label' => $label,
                        'is_financial_summary' => true,
                    ];
                }

                $detailRows = array_map(function (array $row): array {
                    $row['financial_target'] = [];
                    $row['financial_accomplishment'] = [];

                    return $row;
                }, $detailRows);

                return [...$summaryRows, ...$detailRows];
            })
            ->values()
            ->all();
    }

    private function sumFinancialPeriods(Collection $rows, string $kind): array
    {
        $totals = array_fill_keys(self::PERIODS, 0.0);
        $hasFinancialData = false;
        foreach ($rows as $row) {
            $periods = $row[$kind] ?? [];
            if (! is_array($periods) || $periods === [] || ! $this->hasValues($periods)) {
                continue;
            }

            $hasFinancialData = true;
            foreach (self::PERIODS as $period) {
                $totals[$period] += (float) ($periods[$period] ?? 0);
            }
        }

        return $hasFinancialData ? $totals : [];
    }

    private function normalizeExportOffice(string $office): string
    {
        $office = strtoupper($office);
        $office = preg_replace('/\b(?:PENRO|CENRO|TOTAL)\b/', '', $office) ?? '';

        return preg_replace('/[^A-Z0-9]+/', '', $office) ?? '';
    }
}
