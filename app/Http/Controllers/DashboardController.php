<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
    private array $tableExistsCache = [];
    private array $columnExistsCache = [];

    public function index(\Illuminate\Http\Request $request)
    {
        $currentYear = (int) now()->year;
        $year = (int) $request->query('year', $currentYear);

        if ($year < 2000 || $year > 2100) {
            $year = $currentYear;
        }

        $selectedSector = strtolower((string) $request->query('sector', 'all'));
        $officeId = $this->dashboardOfficeScope();

        $fieldConfigs = [
            ['key' => 'gass', 'label' => 'GASS', 'type_code' => 'GASS', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'sto', 'label' => 'STO', 'type_code' => 'STO', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'enf', 'label' => 'ENF', 'type_code' => 'ENF', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'pa', 'label' => 'PA', 'type_code' => 'PA', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'engp', 'label' => 'ENGP', 'type_code' => 'ENGP', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'lands', 'label' => 'LANDS', 'type_code' => 'Lands', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'soilcon', 'label' => 'SOILCON', 'type_code' => 'Soilcon', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'nra', 'label' => 'NRA', 'type_code' => 'NRA', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'paria', 'label' => 'PARIA', 'type_code' => 'PARIA', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'cobb', 'label' => 'COBB', 'type_code' => 'COBB', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
            ['key' => 'continuing', 'label' => 'CONTINUING', 'type_code' => 'CONTINUING', 'targets' => 'physical_targets', 'accomp' => 'physical_accomplishments'],
        ];

        $sectorOptions = collect($fieldConfigs)
            ->map(fn (array $config) => [
                'key' => $config['key'],
                'label' => $config['label'],
            ]);

        if ($selectedSector !== 'all' && !$sectorOptions->contains('key', $selectedSector)) {
            $selectedSector = 'all';
        }

        $visibleFieldConfigs = collect($fieldConfigs)
            ->when($selectedSector !== 'all', fn ($configs) => $configs->where('key', $selectedSector))
            ->values();

        $dashboardSummary = Cache::remember(
            'dashboard.summary.v10.' . $year . '.sector.' . $selectedSector . '.office.' . ($officeId ?? 'all'),
            now()->addMinutes(2),
            fn () => $this->buildDashboardSummary($visibleFieldConfigs->all(), $year, $officeId)
        );

        $fieldStats = $dashboardSummary['fieldStats'];
        $overallTarget = $dashboardSummary['overallTarget'];
        $overallAccomp = $dashboardSummary['overallAccomp'];
        $overallProgress = $dashboardSummary['overallProgress'];
        $progressTrend = $dashboardSummary['progressTrend'];
        $physicalTargetsProgress = $dashboardSummary['physicalTargetsProgress'];
        $activeFields = $dashboardSummary['activeFields'];
        $activeFieldsProgress = $dashboardSummary['activeFieldsProgress'];
        $officeStats = $dashboardSummary['officeStats'] ?? collect();
        $totalPap = $dashboardSummary['totalPap'] ?? 0;
        $totalIndicators = $dashboardSummary['totalIndicators'] ?? 0;
        $papList = $dashboardSummary['papList'] ?? collect();
        $indicatorList = $dashboardSummary['indicatorList'] ?? collect();

        $yearOptions = Cache::remember(
            'dashboard.year_options.' . $currentYear . '.office.' . ($officeId ?? 'all'),
            now()->addMinutes(5),
            fn () => $this->yearOptions($fieldConfigs, $currentYear, $officeId)
        );

        return view($this->roleView('index'), [
            'year' => $year,
            'yearOptions' => $yearOptions,
            'selectedSector' => $selectedSector,
            'sectorOptions' => $sectorOptions,
            'fieldStats' => $fieldStats,
            'officeStats' => $officeStats,
            'overallProgress' => $overallProgress,
            'progressTrend' => $progressTrend,
            'physicalTargetsProgress' => $physicalTargetsProgress,
            'overallTarget' => $overallTarget,
            'overallAccomp' => $overallAccomp,
            'totalPap' => $totalPap,
            'totalIndicators' => $totalIndicators,
            'papList' => $papList,
            'indicatorList' => $indicatorList,
            'activeFields' => $activeFields,
            'activeFieldsProgress' => $activeFieldsProgress,
            'totalFields' => $fieldStats->count(),
            'financialUtilization' => null,
        ]);
    }

    private function statusLabel(float $progress): string
    {
        if ($progress >= 80) {
            return 'On Track';
        }

        if ($progress >= 60) {
            return 'Needs Attention';
        }

        return 'Delayed';
    }

    private function buildDashboardSummary(array $fieldConfigs, int $year, ?int $officeId = null): array
    {
        $fieldStats = collect($fieldConfigs)->map(function (array $config) use ($year, $officeId) {
            $totals = $this->physicalTotalsForYear($config['targets'], $config['accomp'], $year, $officeId, $config['key']);
            $targetTotal = $totals['target_total'];
            $accompTotal = $totals['accomp_total'];

            $progress = $targetTotal > 0
                ? round(min(100, ($accompTotal / $targetTotal) * 100), 2)
                : 0.0;

            return [
                'key' => $config['key'],
                'label' => $config['label'],
                'target_total' => $targetTotal,
                'accomp_total' => $accompTotal,
                'progress' => $progress,
                'status' => $this->statusLabel($progress),
            ];
        })
            ->sortByDesc('progress')
            ->values();

        $overallTarget = (float) $fieldStats->sum('target_total');
        $overallAccomp = (float) $fieldStats->sum('accomp_total');
        $overallProgress = $overallTarget > 0
            ? round(min(100, ($overallAccomp / $overallTarget) * 100), 2)
            : 0.0;

        $fieldsWithTargets = $fieldStats->filter(fn ($row) => (float) ($row['target_total'] ?? 0) > 0)->count();
        $physicalTargetsProgress = $fieldStats->count() > 0
            ? round(($fieldsWithTargets / $fieldStats->count()) * 100, 2)
            : 0.0;

        $activeFields = $fieldStats->filter(fn ($row) => $row['target_total'] > 0 || $row['accomp_total'] > 0)->count();
        $activeFieldsProgress = $fieldStats->count() > 0
            ? round(($activeFields / $fieldStats->count()) * 100, 2)
            : 0.0;

        $papIndicatorTotals = $this->papIndicatorTotalsForYear($fieldConfigs, $year, $officeId);

        return [
            'fieldStats' => $fieldStats,
            'overallTarget' => $overallTarget,
            'overallAccomp' => $overallAccomp,
            'totalPap' => $papIndicatorTotals['pap_total'],
            'totalIndicators' => $papIndicatorTotals['indicator_total'],
            'papList' => $this->papListForYear($fieldConfigs, $year, $officeId),
            'indicatorList' => $this->indicatorListForYear($fieldConfigs, $year, $officeId),
            'overallProgress' => $overallProgress,
            'progressTrend' => $this->progressTrend($fieldConfigs, $year, $officeId),
            'officeStats' => $this->officePerformanceStatsForYear($fieldConfigs, $year, $officeId),
            'physicalTargetsProgress' => $physicalTargetsProgress,
            'activeFields' => $activeFields,
            'activeFieldsProgress' => $activeFieldsProgress,
        ];
    }

    private function papListForYear(array $fieldConfigs, int $year, ?int $officeId = null)
    {
        if (!$this->hasTable('ppa') || !$this->hasTable('types') || !$this->hasTable('record_types')) {
            return collect();
        }

        $typeCodes = collect($fieldConfigs)
            ->pluck('type_code')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($typeCodes)) {
            return collect();
        }

        $typeIds = DB::table('types')
            ->whereIn('code', $typeCodes)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $programRecordTypeId = (int) DB::table('record_types')
            ->where('name', 'PROGRAM')
            ->value('id');

        if (empty($typeIds) || $programRecordTypeId <= 0) {
            return collect();
        }

        $query = DB::table('ppa')
            ->leftJoin('types', 'types.id', '=', 'ppa.types_id')
            ->whereIn('ppa.types_id', $typeIds)
            ->where('ppa.record_type_id', $programRecordTypeId)
            ->where('ppa.year', $year)
            ->select([
                'ppa.id',
                'ppa.name',
                'ppa.types_id',
                'ppa.ppa_details_id',
                'ppa.office_id',
                'ppa.year',
                DB::raw('types.code as sector_code'),
            ])
            ->orderBy('types.code')
            ->orderBy('ppa.name');

        if ($officeId !== null && $this->hasColumn('ppa', 'office_id')) {
            $query->whereJsonContains('ppa.office_id', $officeId);
        }

        $rows = $query->get();

        if ($rows->isEmpty()) {
            return collect();
        }

        $sectorKeyByTypeCode = collect($fieldConfigs)
            ->mapWithKeys(fn (array $config) => [strtolower((string) ($config['type_code'] ?? '')) => (string) ($config['key'] ?? '')])
            ->filter()
            ->all();

        return $rows->map(function ($row) use ($typeIds, $sectorKeyByTypeCode, $officeId) {
            $officeIds = $this->parseJsonIdArray($row->office_id ?? null);
            $detailIds = $this->hasTable('ppa_details')
                ? $this->descendantPpaDetailIds([$row->ppa_details_id])
                : [];

            $indicatorRowQuery = DB::table('ppa')
                ->whereIn('types_id', $typeIds)
                ->whereNotNull('indicator_id');

            if (!empty($detailIds)) {
                $indicatorRowQuery->where(function ($query) use ($row, $detailIds) {
                    $query->where('id', (int) $row->id)
                        ->orWhereIn('ppa_details_id', $detailIds);
                });
            } else {
                $indicatorRowQuery->where('id', (int) $row->id);
            }

            $indicatorCount = $indicatorRowQuery
                ->distinct()
                ->count('indicator_id');

            $sectorKey = $sectorKeyByTypeCode[strtolower((string) ($row->sector_code ?? ''))] ?? '';
            $routeParams = [
                'program' => (int) $row->id,
                'year' => (int) ($row->year ?? 0),
            ];

            if ($officeId !== null) {
                $routeParams['office_id'] = $officeId;
            } elseif (!empty($officeIds)) {
                $routeParams['office_id'] = $officeIds[0];
            }

            return [
                'id' => (int) $row->id,
                'name' => (string) ($row->name ?? ''),
                'sector' => (string) ($row->sector_code ?? ''),
                'year' => (int) ($row->year ?? 0),
                'indicator_count' => (int) $indicatorCount,
                'url' => $sectorKey !== '' ? route($sectorKey . '_physical', $routeParams) : '#',
            ];
        })->values();
    }

    private function indicatorListForYear(array $fieldConfigs, int $year, ?int $officeId = null)
    {
        if (!$this->hasTable('ppa') || !$this->hasTable('types') || !$this->hasTable('record_types') || !$this->hasTable('indicators')) {
            return collect();
        }

        $typeCodes = collect($fieldConfigs)
            ->pluck('type_code')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($typeCodes)) {
            return collect();
        }

        $typeRows = DB::table('types')
            ->whereIn('code', $typeCodes)
            ->get(['id', 'code']);

        $typeIds = $typeRows
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $typeCodeById = $typeRows
            ->mapWithKeys(fn ($row) => [(int) $row->id => (string) $row->code])
            ->all();

        $sectorKeyByTypeCode = collect($fieldConfigs)
            ->mapWithKeys(fn (array $config) => [strtolower((string) ($config['type_code'] ?? '')) => (string) ($config['key'] ?? '')])
            ->filter()
            ->all();

        $programRecordTypeId = (int) DB::table('record_types')
            ->where('name', 'PROGRAM')
            ->value('id');

        if (empty($typeIds) || $programRecordTypeId <= 0) {
            return collect();
        }

        $baseQuery = DB::table('ppa')
            ->whereIn('types_id', $typeIds)
            ->where('year', $year);

        if ($officeId !== null && $this->hasColumn('ppa', 'office_id')) {
            $baseQuery->whereJsonContains('office_id', $officeId);
        }

        $papRows = (clone $baseQuery)
            ->where('record_type_id', $programRecordTypeId)
            ->get(['id', 'name', 'types_id', 'ppa_details_id', 'office_id', 'year']);

        if ($papRows->isEmpty()) {
            return collect();
        }

        $rowMetaById = collect();

        foreach ($papRows as $papRow) {
            $rootId = (int) ($papRow->id ?? 0);
            $typeId = (int) ($papRow->types_id ?? 0);
            $detailIds = $this->hasTable('ppa_details')
                ? $this->descendantPpaDetailIds([$papRow->ppa_details_id])
                : [];

            $rowIds = collect([$rootId]);

            if (!empty($detailIds)) {
                $rowIds = $rowIds->merge(DB::table('ppa')
                    ->whereIn('types_id', $typeIds)
                    ->whereIn('ppa_details_id', $detailIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id));
            }

            foreach ($rowIds->filter(fn ($id) => $id > 0)->unique() as $rowId) {
                $rowMetaById->put((int) $rowId, [
                    'root_id' => $rootId,
                    'pap_name' => (string) ($papRow->name ?? ''),
                    'type_id' => $typeId,
                    'sector' => (string) ($typeCodeById[$typeId] ?? ''),
                    'year' => (int) ($papRow->year ?? $year),
                    'office_ids' => $this->parseJsonIdArray($papRow->office_id ?? null),
                ]);
            }
        }

        $performanceIndicatorRowIds = $rowMetaById->keys()
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->values();

        $indicatorAssignments = $performanceIndicatorRowIds->isEmpty()
            ? collect()
            : DB::table('ppa')
                ->whereIn('id', $performanceIndicatorRowIds->all())
                ->whereNotNull('indicator_id')
                ->get(['id', 'indicator_id', 'office_id'])
                ->map(fn ($row) => [
                    'program_id' => (int) ($row->id ?? 0),
                    'indicator_id' => (int) ($row->indicator_id ?? 0),
                    'office_ids' => $this->parseJsonIdArray($row->office_id ?? null),
                ]);

        foreach ($fieldConfigs as $config) {
            $targetTable = $config['targets'] ?? null;

            if (!is_string($targetTable) || !$this->hasTable($targetTable)) {
                continue;
            }

            $targetRows = $this->physicalRowsForYear($targetTable, $year, $officeId, $config['key']);

            foreach ($targetRows as $targetRow) {
                $meta = $this->parseValuesJson($targetRow->values ?? null);
                $programId = (int) ($targetRow->row_id ?? $targetRow->program_id ?? $meta['row_id'] ?? $meta['program_id'] ?? 0);
                $indicatorId = (int) ($targetRow->indicator_id ?? $meta['indicator_id'] ?? 0);

                if ($programId <= 0 || $indicatorId <= 0 || !$rowMetaById->has($programId)) {
                    continue;
                }

                $targetOfficeId = (int) ($targetRow->office_id ?? $targetRow->office_ids ?? 0);

                $indicatorAssignments->push([
                    'program_id' => $programId,
                    'indicator_id' => $indicatorId,
                    'office_ids' => $targetOfficeId > 0 ? [$targetOfficeId] : [],
                ]);
            }
        }

        if ($officeId !== null) {
            $indicatorAssignments = $indicatorAssignments->filter(function (array $assignment) use ($officeId) {
                $officeIds = collect($assignment['office_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0);

                return $officeIds->isEmpty() || $officeIds->contains($officeId);
            });
        }

        $indicatorAssignments = $indicatorAssignments
            ->filter(fn (array $assignment) => (int) ($assignment['program_id'] ?? 0) > 0 && (int) ($assignment['indicator_id'] ?? 0) > 0)
            ->unique(fn (array $assignment) => (int) $assignment['program_id'] . '|' . (int) $assignment['indicator_id'])
            ->values();

        $indicatorNames = $indicatorAssignments->isEmpty()
            ? collect()
            : DB::table('indicators')
                ->whereIn('id', $indicatorAssignments->pluck('indicator_id')->unique()->values()->all())
                ->pluck('name', 'id');

        return $indicatorAssignments
            ->filter(fn (array $assignment) => $indicatorNames->has((int) ($assignment['indicator_id'] ?? 0)) && $rowMetaById->has((int) ($assignment['program_id'] ?? 0)))
            ->map(function (array $assignment) use ($indicatorNames, $rowMetaById, $sectorKeyByTypeCode, $officeId) {
                $rowMeta = $rowMetaById->get((int) $assignment['program_id']);
                $sector = (string) ($rowMeta['sector'] ?? '');
                $sectorKey = $sectorKeyByTypeCode[strtolower($sector)] ?? '';
                $assignmentOfficeIds = collect($assignment['office_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->values()
                    ->all();
                $rootOfficeIds = $rowMeta['office_ids'] ?? [];
                $routeParams = [
                    'program' => (int) ($rowMeta['root_id'] ?? 0),
                    'year' => (int) ($rowMeta['year'] ?? 0),
                    'highlight_row_id' => (int) ($assignment['program_id'] ?? 0),
                    'highlight_indicator_id' => (int) ($assignment['indicator_id'] ?? 0),
                ];

                if ($officeId !== null) {
                    $routeParams['office_id'] = $officeId;
                } elseif (!empty($assignmentOfficeIds)) {
                    $routeParams['office_id'] = $assignmentOfficeIds[0];
                } elseif (!empty($rootOfficeIds)) {
                    $routeParams['office_id'] = $rootOfficeIds[0];
                }

                return [
                    'id' => (int) ($assignment['indicator_id'] ?? 0),
                    'name' => (string) ($indicatorNames[(int) ($assignment['indicator_id'] ?? 0)] ?? ''),
                    'pap_name' => (string) ($rowMeta['pap_name'] ?? ''),
                    'sector' => $sector,
                    'year' => (int) ($rowMeta['year'] ?? 0),
                    'url' => $sectorKey !== '' ? route($sectorKey . '_physical', $routeParams) : '#',
                ];
            })
            ->sortBy([
                ['sector', 'asc'],
                ['pap_name', 'asc'],
                ['name', 'asc'],
            ])
            ->values();
    }

    private function officePerformanceStatsForYear(array $fieldConfigs, int $year, ?int $scopedOfficeId = null)
    {
        if (!$this->hasTable('offices')) {
            return collect();
        }

        $officesQuery = DB::table('offices')
            ->select(['id', 'name', 'office_types_id'])
            ->orderBy('office_types_id')
            ->orderBy('name');

        if ($scopedOfficeId !== null) {
            $officesQuery->where('id', $scopedOfficeId);
        }

        return $officesQuery
            ->get()
            ->map(function ($office) use ($fieldConfigs, $year) {
                $targetTotal = 0.0;
                $accompTotal = 0.0;

                foreach ($fieldConfigs as $config) {
                    $totals = $this->physicalTotalsForYear($config['targets'], $config['accomp'], $year, (int) $office->id, $config['key']);
                    $targetTotal += (float) ($totals['target_total'] ?? 0);
                    $accompTotal += (float) ($totals['accomp_total'] ?? 0);
                }

                $progress = $targetTotal > 0
                    ? round(min(100, ($accompTotal / $targetTotal) * 100), 2)
                    : 0.0;

                return [
                    'id' => (int) $office->id,
                    'label' => (string) $office->name,
                    'office_type_id' => (int) ($office->office_types_id ?? 0),
                    'target_total' => $targetTotal,
                    'accomp_total' => $accompTotal,
                    'progress' => $progress,
                    'status' => $this->statusLabel($progress),
                ];
            })
            ->sortBy([
                ['accomp_total', 'desc'],
                ['label', 'asc'],
            ])
            ->values();
    }

    private function papIndicatorTotalsForYear(array $fieldConfigs, int $year, ?int $officeId = null): array
    {
        if (!$this->hasTable('ppa') || !$this->hasTable('types') || !$this->hasTable('record_types') || !$this->hasTable('indicators')) {
            return [
                'pap_total' => 0,
                'indicator_total' => 0,
            ];
        }

        $typeCodes = collect($fieldConfigs)
            ->pluck('type_code')
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (empty($typeCodes)) {
            return [
                'pap_total' => 0,
                'indicator_total' => 0,
            ];
        }

        $typeIds = DB::table('types')
            ->whereIn('code', $typeCodes)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        $programRecordTypeId = (int) DB::table('record_types')
            ->where('name', 'PROGRAM')
            ->value('id');

        if (empty($typeIds) || $programRecordTypeId <= 0) {
            return [
                'pap_total' => 0,
                'indicator_total' => 0,
            ];
        }

        $baseQuery = DB::table('ppa')
            ->whereIn('types_id', $typeIds)
            ->where('year', $year);

        if ($officeId !== null && $this->hasColumn('ppa', 'office_id')) {
            $baseQuery->whereJsonContains('office_id', $officeId);
        }

        $papTotal = (clone $baseQuery)
            ->where('record_type_id', $programRecordTypeId)
            ->count();

        $papRows = (clone $baseQuery)
            ->where('record_type_id', $programRecordTypeId)
            ->get(['id', 'ppa_details_id']);

        $programIds = $papRows
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        $performanceIndicatorRowIds = $programIds;

        if (!$programIds->isEmpty() && $this->hasTable('ppa_details')) {
            $detailIds = $this->descendantPpaDetailIds($papRows->pluck('ppa_details_id')->all());

            if (!empty($detailIds)) {
                $performanceIndicatorRowIds = DB::table('ppa')
                    ->whereIn('types_id', $typeIds)
                    ->whereIn('ppa_details_id', $detailIds)
                    ->pluck('id')
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0)
                    ->merge($programIds)
                    ->unique()
                    ->values();
            }
        }

        $indicatorAssignments = $performanceIndicatorRowIds->isEmpty()
            ? collect()
            : DB::table('ppa')
                ->whereIn('id', $performanceIndicatorRowIds->all())
                ->whereNotNull('indicator_id')
                ->get(['id', 'indicator_id', 'office_id'])
                ->map(fn ($row) => [
                    'program_id' => (int) ($row->id ?? 0),
                    'indicator_id' => (int) ($row->indicator_id ?? 0),
                    'office_ids' => $this->parseJsonIdArray($row->office_id ?? null),
                ]);

        if (!$programIds->isEmpty()) {
            foreach ($fieldConfigs as $config) {
                $targetTable = $config['targets'] ?? null;

                if (!is_string($targetTable) || !$this->hasTable($targetTable)) {
                    continue;
                }

                $targetRows = $this->physicalRowsForYear($targetTable, $year, $officeId, $config['key']);

                foreach ($targetRows as $targetRow) {
                    $meta = $this->parseValuesJson($targetRow->values ?? null);
                    $programId = (int) ($targetRow->row_id ?? $targetRow->program_id ?? $meta['row_id'] ?? $meta['program_id'] ?? 0);
                    $indicatorId = (int) ($targetRow->indicator_id ?? $meta['indicator_id'] ?? 0);

                    if ($programId <= 0 || $indicatorId <= 0 || !$performanceIndicatorRowIds->contains($programId)) {
                        continue;
                    }

                    $targetOfficeId = (int) ($targetRow->office_id ?? $targetRow->office_ids ?? 0);

                    $indicatorAssignments->push([
                        'program_id' => $programId,
                        'indicator_id' => $indicatorId,
                        'office_ids' => $targetOfficeId > 0 ? [$targetOfficeId] : [],
                    ]);
                }
            }
        }

        if ($officeId !== null) {
            $indicatorAssignments = $indicatorAssignments->filter(function (array $assignment) use ($officeId) {
                $officeIds = collect($assignment['office_ids'] ?? [])
                    ->map(fn ($id) => (int) $id)
                    ->filter(fn ($id) => $id > 0);

                return $officeIds->isEmpty() || $officeIds->contains($officeId);
            });
        }

        $indicatorAssignments = $indicatorAssignments
            ->filter(fn (array $assignment) => (int) ($assignment['program_id'] ?? 0) > 0 && (int) ($assignment['indicator_id'] ?? 0) > 0)
            ->unique(fn (array $assignment) => (int) $assignment['program_id'] . '|' . (int) $assignment['indicator_id'])
            ->values();

        $validIndicatorIds = $indicatorAssignments
            ->pluck('indicator_id')
            ->unique()
            ->values();

        $existingIndicatorIds = $validIndicatorIds->isEmpty()
            ? collect()
            : DB::table('indicators')
                ->whereIn('id', $validIndicatorIds->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id);

        $indicatorTotal = $indicatorAssignments
            ->filter(fn (array $assignment) => $existingIndicatorIds->contains((int) ($assignment['indicator_id'] ?? 0)))
            ->count();

        return [
            'pap_total' => $papTotal,
            'indicator_total' => $indicatorTotal,
        ];
    }

    private function descendantPpaDetailIds(array $rootDetailIds): array
    {
        $allDetailIds = collect($rootDetailIds)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values();

        if ($allDetailIds->isEmpty()) {
            return [];
        }

        $frontier = $allDetailIds;

        while ($frontier->isNotEmpty()) {
            $children = DB::table('ppa_details')
                ->whereIn('parent_id', $frontier->all())
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->diff($allDetailIds)
                ->unique()
                ->values();

            if ($children->isEmpty()) {
                break;
            }

            $allDetailIds = $allDetailIds
                ->merge($children)
                ->unique()
                ->values();
            $frontier = $children;
        }

        return $allDetailIds->all();
    }

    private function parseJsonIdArray($raw): array
    {
        if (is_array($raw)) {
            return collect($raw)
                ->map(fn ($id) => (int) $id)
                ->filter(fn ($id) => $id > 0)
                ->unique()
                ->values()
                ->all();
        }

        if (is_numeric($raw)) {
            $id = (int) $raw;

            return $id > 0 ? [$id] : [];
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        if (is_numeric($decoded)) {
            $id = (int) $decoded;

            return $id > 0 ? [$id] : [];
        }

        if (!is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function physicalTotalsForYear(string $targetTable, string $accompTable, int $year, ?int $officeId = null, ?string $sector = null): array
    {
        $targetMap = $this->monthlyMapFromAggregateRows($this->physicalMonthlySumsForYear($targetTable, $year, $officeId, $sector));
        $accompMap = $this->monthlyMapFromAggregateRows($this->physicalMonthlySumsForYear($accompTable, $year, $officeId, $sector));
        $targetTotal = 0.0;
        $accompTotal = 0.0;

        foreach ($targetMap as $targetValue) {
            $targetTotal += max((float) $targetValue, 0.0);
        }

        foreach ($accompMap as $accompValue) {
            $accompTotal += max((float) $accompValue, 0.0);
        }

        return [
            'target_total' => $targetTotal,
            'accomp_total' => $accompTotal,
        ];
    }

    private function progressTrend(array $fieldConfigs, int $year, ?int $officeId = null): array
    {
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $monthColumns = $this->monthColumns();
        $monthlyAccomplishments = array_fill_keys($monthColumns, 0.0);

        foreach ($fieldConfigs as $config) {
            $totals = $this->physicalAccomplishmentTrendForYear($config['accomp'], $year, $officeId, $config['key']);

            foreach ($monthColumns as $monthColumn) {
                $monthlyAccomplishments[$monthColumn] += (float) ($totals['accomplishments'][$monthColumn] ?? 0);
            }
        }

        $monthly = [];
        $maxMonthlyAccomplishment = max($monthlyAccomplishments);

        foreach ($monthColumns as $index => $monthColumn) {
            $accomplishment = $monthlyAccomplishments[$monthColumn];

            $monthly[] = [
                'label' => $monthlyLabels[$index],
                'accomplishment' => round($accomplishment, 2),
                'progress' => $maxMonthlyAccomplishment > 0 ? round(($accomplishment / $maxMonthlyAccomplishment) * 100, 2) : 0.0,
            ];
        }

        $quarters = [
            'Q1' => ['jan', 'feb', 'mar'],
            'Q2' => ['apr', 'may', 'jun'],
            'Q3' => ['jul', 'aug', 'sep'],
            'Q4' => ['oct', 'nov', 'dec'],
        ];

        $quarterly = [];
        $quarterlyAccomplishments = [];

        foreach ($quarters as $label => $quarterMonths) {
            $quarterlyAccomplishments[$label] = array_sum(array_intersect_key($monthlyAccomplishments, array_flip($quarterMonths)));
        }

        $maxQuarterlyAccomplishment = max($quarterlyAccomplishments);

        foreach ($quarterlyAccomplishments as $label => $accomplishment) {

            $quarterly[] = [
                'label' => $label,
                'accomplishment' => round($accomplishment, 2),
                'progress' => $maxQuarterlyAccomplishment > 0 ? round(($accomplishment / $maxQuarterlyAccomplishment) * 100, 2) : 0.0,
            ];
        }

        return [
            'monthly' => $monthly,
            'quarterly' => $quarterly,
        ];
    }

    private function physicalAccomplishmentTrendForYear(string $accompTable, int $year, ?int $officeId = null, ?string $sector = null): array
    {
        $accomplishments = array_fill_keys($this->monthColumns(), 0.0);

        if (!$this->hasTable($accompTable)) {
            return [
                'accomplishments' => $accomplishments,
            ];
        }

        $yearColumn = $this->resolveYearColumn($accompTable);

        if ($yearColumn === null) {
            return [
                'accomplishments' => $accomplishments,
            ];
        }

        $selects = collect($this->monthColumns())
            ->filter(fn (string $column) => $this->hasColumn($accompTable, $column))
            ->map(fn (string $column) => DB::raw("COALESCE(SUM(`{$column}`), 0) as `{$column}`"))
            ->values()
            ->all();

        if (empty($selects)) {
            return [
                'accomplishments' => $accomplishments,
            ];
        }

        $query = DB::table($accompTable)->select($selects);
        $this->applyYearFilter($query, $yearColumn, $year);

        $officeColumn = $this->hasColumn($accompTable, 'office_id') ? 'office_id' : 'office_ids';

        if ($officeId !== null && $this->hasColumn($accompTable, $officeColumn)) {
            $query->where($officeColumn, $officeId);
        }

        if ($sector !== null && $this->hasColumn($accompTable, 'sector')) {
            $query->where('sector', $sector);
        }

        $row = $query->first();

        foreach ($this->monthColumns() as $monthColumn) {
            $accomplishments[$monthColumn] = max((float) ($row->{$monthColumn} ?? 0), 0.0);
        }

        return [
            'accomplishments' => $accomplishments,
        ];
    }

    private function physicalRowsForYear(string $table, int $year, ?int $officeId = null, ?string $sector = null)
    {
        if (!$this->hasTable($table)) {
            return collect();
        }

        $yearColumn = $this->resolveYearColumn($table);

        if ($yearColumn === null) {
            return collect();
        }

        $columns = collect(['office_id', 'office_ids', 'program_id', 'row_id', 'indicator_id', 'values', 'annual_total'])
            ->merge($this->monthColumns())
            ->filter(fn (string $column) => $this->hasColumn($table, $column))
            ->values()
            ->all();

        $query = DB::table($table)->select($columns);
        $this->applyYearFilter($query, $yearColumn, $year);

        $officeColumn = $this->hasColumn($table, 'office_id') ? 'office_id' : 'office_ids';

        if ($officeId !== null && $this->hasColumn($table, $officeColumn)) {
            $query->where($officeColumn, $officeId);
        }

        if ($sector !== null && $this->hasColumn($table, 'sector')) {
            $query->where('sector', $sector);
        }

        return $query->get();
    }

    private function physicalMonthlySumsForYear(string $table, int $year, ?int $officeId = null, ?string $sector = null)
    {
        if (!$this->hasTable($table)) {
            return collect();
        }

        $yearColumn = $this->resolveYearColumn($table);

        $officeColumn = $this->hasColumn($table, 'office_id') ? 'office_id' : 'office_ids';

        if ($yearColumn === null || !$this->hasColumn($table, $officeColumn)) {
            return collect();
        }

        $monthColumns = collect($this->monthColumns())
            ->filter(fn (string $column) => $this->hasColumn($table, $column))
            ->values();

        if ($monthColumns->isEmpty()) {
            return collect();
        }

        $programExpression = $this->dashboardProgramIdExpression($table);
        $indicatorExpression = $this->dashboardIndicatorIdExpression($table);

        $selects = [
            DB::raw("{$programExpression} as dashboard_program_id"),
            DB::raw("{$indicatorExpression} as dashboard_indicator_id"),
            DB::raw("`{$officeColumn}` as office_ids"),
        ];

        foreach ($monthColumns as $column) {
            $selects[] = DB::raw("COALESCE(SUM(`{$column}`), 0) as `{$column}`");
        }

        $query = DB::table($table)
            ->select($selects)
            ->whereNotNull($officeColumn)
            ->groupBy(DB::raw($programExpression), DB::raw($indicatorExpression), $officeColumn);

        if ($this->hasColumn($table, 'program_id')) {
            $query->groupBy('program_id');
        }

        if ($this->hasColumn($table, 'indicator_id')) {
            $query->groupBy('indicator_id');
        }

        if ($this->hasColumn($table, 'values')) {
            $query->groupBy('values');
        }

        $this->applyYearFilter($query, $yearColumn, $year);

        if ($officeId !== null) {
            $query->where($officeColumn, $officeId);
        }

        if ($sector !== null && $this->hasColumn($table, 'sector')) {
            $query->where('sector', $sector);
        }

        return $query->get();
    }

    private function dashboardOfficeScope(): ?int
    {
        $user = auth()->user();

        if ($user && $this->shouldScopeToUserOffice()) {
            return (int) $user->office_id;
        }

        return null;
    }

    private function monthlyMap($rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            $meta = $this->parseValuesJson($row->values ?? null);
            $rowId = (int) ($row->program_id ?? $meta['row_id'] ?? $meta['program_id'] ?? 0);
            $indicatorId = (int) ($row->indicator_id ?? $meta['indicator_id'] ?? 0);
            $officeId = (int) ($row->office_ids ?? 0);

            if ($rowId <= 0 || $indicatorId <= 0 || $officeId <= 0) {
                continue;
            }

            foreach ($this->monthColumns() as $monthColumn) {
                $key = "{$rowId}|{$indicatorId}|{$officeId}|{$monthColumn}";
                $map[$key] = (float) ($row->{$monthColumn} ?? 0);
            }
        }

        return $map;
    }

    private function monthlyMapFromAggregateRows($rows): array
    {
        $map = [];

        foreach ($rows as $row) {
            $rowId = (int) ($row->dashboard_program_id ?? 0);
            $indicatorId = (int) ($row->dashboard_indicator_id ?? 0);
            $officeId = (int) ($row->office_ids ?? 0);

            if ($rowId <= 0 || $indicatorId <= 0 || $officeId <= 0) {
                continue;
            }

            foreach ($this->monthColumns() as $monthColumn) {
                $key = "{$rowId}|{$indicatorId}|{$officeId}|{$monthColumn}";
                $map[$key] = (float) ($row->{$monthColumn} ?? 0);
            }
        }

        return $map;
    }

    private function dashboardProgramIdExpression(string $table): string
    {
        if ($this->hasColumn($table, 'row_id')) {
            return '`row_id`';
        }

        if ($this->hasColumn($table, 'program_id') && $this->hasColumn($table, 'values')) {
            return "COALESCE(NULLIF(`program_id`, 0), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.row_id')), ''), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.program_id')), ''), 0)";
        }

        if ($this->hasColumn($table, 'program_id')) {
            return '`program_id`';
        }

        return "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.row_id')), ''), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.program_id')), ''), 0)";
    }

    private function dashboardIndicatorIdExpression(string $table): string
    {
        if ($this->hasColumn($table, 'indicator_id') && $this->hasColumn($table, 'values')) {
            return "COALESCE(NULLIF(`indicator_id`, 0), NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.indicator_id')), ''), 0)";
        }

        if ($this->hasColumn($table, 'indicator_id')) {
            return '`indicator_id`';
        }

        return "COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(`values`, '$.indicator_id')), ''), 0)";
    }

    private function monthColumns(): array
    {
        return ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    }

    private function parseValuesJson($raw): array
    {
        if (is_array($raw)) {
            return $raw;
        }

        if (!is_string($raw) || trim($raw) === '') {
            return [];
        }

        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function applyYearFilter($query, string $yearColumn, int $year): void
    {
        $query->whereRaw("CAST({$yearColumn} AS UNSIGNED) = ?", [$year]);
    }

    private function yearOptions(array $fieldConfigs, int $currentYear, ?int $officeId = null)
    {
        $tables = collect($fieldConfigs)
            ->flatMap(fn (array $config) => [$config['targets'], $config['accomp']])
            ->unique()
            ->values();

        $years = collect();

        foreach ($tables as $table) {
            if (!$this->hasTable($table)) {
                continue;
            }

            $yearColumn = $this->resolveYearColumn($table);
            if ($yearColumn === null) {
                continue;
            }

            $query = DB::table($table)
                ->whereNotNull($yearColumn);

            $officeColumn = $this->hasColumn($table, 'office_id') ? 'office_id' : 'office_ids';

            if ($officeId !== null && $this->hasColumn($table, $officeColumn)) {
                $query->where($officeColumn, $officeId);
            }

            $years = $years->merge($query
                ->distinct()
                ->pluck($yearColumn)
                ->map(fn ($value) => $this->normalizeYearValue($value))
                ->filter(fn (int $value) => $value >= 2000 && $value <= 2100));
        }

        $years = $years
            ->push($currentYear)
            ->unique()
            ->sortDesc()
            ->values();

        return $years->isNotEmpty()
            ? $years
            : collect(range($currentYear + 1, 2020))->values();
    }

    private function normalizeYearValue($value): int
    {
        if (is_numeric($value)) {
            return (int) $value;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (is_numeric($decoded)) {
                return (int) $decoded;
            }

            return (int) trim($value, "\"' ");
        }

        return 0;
    }

    private function resolveYearColumn(string $table): ?string
    {
        if ($this->hasColumn($table, 'years')) {
            return 'years';
        }

        if ($this->hasColumn($table, 'year')) {
            return 'year';
        }

        return null;
    }

    private function hasTable(string $table): bool
    {
        return $this->tableExistsCache[$table] ??= Schema::hasTable($table);
    }

    private function hasColumn(string $table, string $column): bool
    {
        $key = "{$table}.{$column}";

        return $this->columnExistsCache[$key] ??= Schema::hasColumn($table, $column);
    }
}
