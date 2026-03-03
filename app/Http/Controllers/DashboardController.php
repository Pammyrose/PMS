<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
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

        $fieldConfigs = [
            ['key' => 'gass', 'label' => 'GASS', 'targets' => 'gass_targets', 'accomp' => 'gass_accomplishment'],
            ['key' => 'sto', 'label' => 'STO', 'targets' => 'sto_targets', 'accomp' => 'sto_accomplishment'],
            ['key' => 'enf', 'label' => 'ENF', 'targets' => 'enf_targets', 'accomp' => 'enf_accomplishment'],
            ['key' => 'pa', 'label' => 'PA', 'targets' => 'pa_targets', 'accomp' => 'pa_accomplishment'],
            ['key' => 'engp', 'label' => 'ENGP', 'targets' => 'engp_targets', 'accomp' => 'engp_accomplishment'],
            ['key' => 'lands', 'label' => 'LANDS', 'targets' => 'lands_targets', 'accomp' => 'lands_accomplishment'],
            ['key' => 'soilcon', 'label' => 'SOILCON', 'targets' => 'soilcon_targets', 'accomp' => 'soilcon_accomplishment'],
            ['key' => 'nra', 'label' => 'NRA', 'targets' => 'nra_targets', 'accomp' => 'nra_accomplishment'],
        ];

        $fieldStats = collect($fieldConfigs)->map(function (array $config) use ($year) {
            $targetTotal = 0.0;
            $accompTotal = 0.0;

            if (Schema::hasTable($config['targets'])) {
                $targetTable = $config['targets'];
                $targetTotal = (float) (DB::table("{$targetTable} as t")
                    ->where('t.year', $year)
                    ->selectRaw('COALESCE(SUM(t.jan + t.feb + t.mar + t.apr + t.may + t.jun + t.jul + t.aug + t.sep + t.oct + t.nov + t.dec), 0) as total')
                    ->value('total') ?? 0);
            }

            if (Schema::hasTable($config['targets']) && Schema::hasTable($config['accomp'])) {
                $accompTable = $config['accomp'];
                $targetTable = $config['targets'];

                $accompTotal = (float) (DB::table("{$accompTable} as a")
                    ->where('a.year', $year)
                    ->whereExists(function ($query) use ($targetTable, $year) {
                        $query->select(DB::raw(1))
                            ->from("{$targetTable} as t")
                            ->where('t.year', $year)
                            ->whereColumn('t.program_id', 'a.program_id')
                            ->whereColumn('t.indicator_id', 'a.indicator_id')
                            ->whereColumn('t.office_id', 'a.office_id');
                    })
                            ->selectRaw('COALESCE(SUM(a.jan + a.feb + a.mar + a.apr + a.may + a.jun + a.jul + a.aug + a.sep + a.oct + a.nov + a.dec), 0) as total')
                            ->value('total') ?? 0);
            }

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
        })->sortByDesc('progress')->values();

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

        $yearOptions = collect(range($currentYear + 1, 2020))->values();

        return view('admin.index', [
            'year' => $year,
            'yearOptions' => $yearOptions,
            'fieldStats' => $fieldStats,
            'overallProgress' => $overallProgress,
            'physicalTargetsProgress' => $physicalTargetsProgress,
            'overallTarget' => $overallTarget,
            'overallAccomp' => $overallAccomp,
            'activeFields' => $activeFields,
            'totalFields' => $fieldStats->count(),
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
}
