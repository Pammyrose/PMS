<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class DashboardController extends Controller
{
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
            ['key' => 'gass', 'label' => 'GASS', 'targets' => 'gass_targets', 'accomp' => 'gass_accomplishments'],
            ['key' => 'sto', 'label' => 'STO', 'targets' => 'sto_targets', 'accomp' => 'sto_accomplishments'],
            ['key' => 'enf', 'label' => 'ENF', 'targets' => 'enf_targets', 'accomp' => 'enf_accomplishments'],
            ['key' => 'pa', 'label' => 'PA', 'targets' => 'pa_targets', 'accomp' => 'pa_accomplishments'],
            ['key' => 'engp', 'label' => 'ENGP', 'targets' => 'engp_targets', 'accomp' => 'engp_accomplishments'],
            ['key' => 'lands', 'label' => 'LANDS', 'targets' => 'lands_targets', 'accomp' => 'lands_accomplishments'],
            ['key' => 'soilcon', 'label' => 'SOILCON', 'targets' => 'soilcon_targets', 'accomp' => 'soilcon_accomplishments'],
            ['key' => 'nra', 'label' => 'NRA', 'targets' => 'nra_targets', 'accomp' => 'nra_accomplishments'],
            ['key' => 'paria', 'label' => 'PARIA', 'targets' => 'paria_targets', 'accomp' => 'paria_accomplishments'],
            ['key' => 'cobb', 'label' => 'COBB', 'targets' => 'cobb_targets', 'accomp' => 'cobb_accomplishments'],
            ['key' => 'continuing', 'label' => 'CONTINUING', 'targets' => 'continuing_targets', 'accomp' => 'continuing_accomplishments'],
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

        $fieldStats = $visibleFieldConfigs->map(function (array $config) use ($year, $officeId) {
            $totals = $this->physicalTotalsForYear($config['targets'], $config['accomp'], $year, $officeId);
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

        $progressTrend = $this->progressTrend($visibleFieldConfigs->all(), $year, $officeId);

        $fieldsWithTargets = $fieldStats->filter(fn ($row) => (float) ($row['target_total'] ?? 0) > 0)->count();
        $physicalTargetsProgress = $fieldStats->count() > 0
            ? round(($fieldsWithTargets / $fieldStats->count()) * 100, 2)
            : 0.0;

        $activeFields = $fieldStats->filter(fn ($row) => $row['target_total'] > 0 || $row['accomp_total'] > 0)->count();
        $activeFieldsProgress = $fieldStats->count() > 0
            ? round(($activeFields / $fieldStats->count()) * 100, 2)
            : 0.0;

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
            'overallProgress' => $overallProgress,
            'progressTrend' => $progressTrend,
            'physicalTargetsProgress' => $physicalTargetsProgress,
            'overallTarget' => $overallTarget,
            'overallAccomp' => $overallAccomp,
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

    private function physicalTotalsForYear(string $targetTable, string $accompTable, int $year, ?int $officeId = null): array
    {
        $targetRows = $this->physicalRowsForYear($targetTable, $year, $officeId);
        $accompRows = $this->physicalRowsForYear($accompTable, $year, $officeId);
        $targetMap = $this->monthlyMap($targetRows);
        $accompMap = $this->monthlyMap($accompRows);
        $targetTotal = 0.0;
        $accompTotal = 0.0;

        foreach ($targetMap as $key => $targetValue) {
            $safeTarget = max((float) $targetValue, 0.0);
            $safeAccomp = max((float) ($accompMap[$key] ?? 0), 0.0);

            $targetTotal += $safeTarget;
            $accompTotal += min($safeAccomp, $safeTarget);
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
            $totals = $this->physicalAccomplishmentTrendForYear($config['accomp'], $year, $officeId);

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

    private function physicalAccomplishmentTrendForYear(string $accompTable, int $year, ?int $officeId = null): array
    {
        $accompRows = $this->physicalRowsForYear($accompTable, $year, $officeId);
        $accompMap = $this->monthlyMap($accompRows);
        $accomplishments = array_fill_keys($this->monthColumns(), 0.0);

        foreach ($accompMap as $key => $accompValue) {
            $monthColumn = substr((string) $key, strrpos((string) $key, '|') + 1);

            if (!array_key_exists($monthColumn, $accomplishments)) {
                continue;
            }

            $accomplishments[$monthColumn] += max((float) $accompValue, 0.0);
        }

        return [
            'accomplishments' => $accomplishments,
        ];
    }

    private function physicalRowsForYear(string $table, int $year, ?int $officeId = null)
    {
        if (!Schema::hasTable($table)) {
            return collect();
        }

        $yearColumn = $this->resolveYearColumn($table);

        if ($yearColumn === null) {
            return collect();
        }

        $columns = collect(['office_ids', 'program_id', 'indicator_id', 'values', 'annual_total'])
            ->merge($this->monthColumns())
            ->filter(fn (string $column) => Schema::hasColumn($table, $column))
            ->values()
            ->all();

        $query = DB::table($table)->select($columns);
        $this->applyYearFilter($query, $yearColumn, $year);

        if ($officeId !== null && Schema::hasColumn($table, 'office_ids')) {
            $query->where('office_ids', $officeId);
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
            if (!Schema::hasTable($table)) {
                continue;
            }

            $yearColumn = $this->resolveYearColumn($table);
            if ($yearColumn === null) {
                continue;
            }

            $query = DB::table($table)
                ->whereNotNull($yearColumn);

            if ($officeId !== null && Schema::hasColumn($table, 'office_ids')) {
                $query->where('office_ids', $officeId);
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
        if (Schema::hasColumn($table, 'years')) {
            return 'years';
        }

        if (Schema::hasColumn($table, 'year')) {
            return 'year';
        }

        return null;
    }
}
