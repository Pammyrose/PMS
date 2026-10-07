<?php

namespace App\Support;

use App\Http\Controllers\GassController;
use App\Http\Controllers\StoController;
use App\Http\Controllers\EnfController;
use App\Http\Controllers\PaController;
use App\Http\Controllers\EngpController;
use App\Http\Controllers\LandsController;
use App\Http\Controllers\SoilconController;
use App\Http\Controllers\NraController;
use App\Http\Controllers\PariaController;
use App\Http\Controllers\CobbController;
use App\Http\Controllers\ContinuingController;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/** Export the rows selected by each sector's actual page, in their displayed order. */
class GassUiReport
{
    private const MONTHS = ['jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec'];
    private const FINANCIAL_UNITS = ['CAR', 'RO', 'ABRA', 'APAYAO', 'BENGUET', 'IFUGAO', 'KALINGA', 'MT.PROVINCE'];
    private const CONTROLLERS = [
        'gass' => GassController::class,
        'sto' => StoController::class,
        'enf' => EnfController::class,
        'pa' => PaController::class,
        'engp' => EngpController::class,
        'lands' => LandsController::class,
        'soilcon' => SoilconController::class,
        'nra' => NraController::class,
        'paria' => PariaController::class,
        'cobb' => CobbController::class,
        'continuing' => ContinuingController::class,
    ];

    public function rows(Request $request, int $year, string $sector = 'gass'): array
    {
        if (! isset(self::CONTROLLERS[$sector])) {
            throw new \InvalidArgumentException('Unknown performance sector.');
        }
        $pageRequest = Request::create('/', 'GET', ['year' => $year]);
        $page = app(self::CONTROLLERS[$sector])->index($pageRequest);
        $role = explode('.', $page->name())[0];
        $html = view("{$role}.{$sector}.partials.{$sector}_physical_table_rows", $page->getData())->render();
        $sources = [];
        foreach ([
            'physical_target' => 'physical_targets',
            'physical_accomplishment' => 'physical_accomplishments',
            'financial_target' => 'financial_target',
            'financial_accomplishment' => 'financial_accomplishment',
        ] as $kind => $table) {
            // Same key as AppServiceProvider's data supplied to the UI. program_id
            // is deliberately not part of it: inputs are addressed by row ID.
            foreach (DB::table($table)->where('sector', $sector)->where('year', $year)->get() as $record) {
                $sources[$kind][$record->row_id][$record->indicator_id][$record->office_id] = (array) $record;
            }
        }

        return $this->fromTable($html, $sources);
    }

    public function fromTable(string $html, array $sources): array
    {
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8"><table><tbody>'.$html.'</tbody></table>', LIBXML_NONET);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $output = [];
        $labels = [];
        $financial = [];
        $summaryPositions = [];
        $block = 0;

        foreach ($xpath->query('//tr[@data-core-key]') as $row) {
            $core = $row->getAttribute('data-core-key');
            if ($this->hasClass($row, 'program-header')) {
                $parts = [];
                foreach ($xpath->query('./td[1]//strong | ./td[1]//div[contains(@class,"flex-col")]/div', $row) as $label) {
                    $parts[] = $this->text($label->textContent);
                }
                $labels[$core] = implode("\n", array_filter($parts));

                // GASS has a dedicated UI row for these units. Other sectors
                // use the same first-row Excel summary without altering their UI.
                $next = $xpath->query('following-sibling::tr[1]', $row)->item(0);
                if (! $next || ! $this->hasClass($next, 'default-office-unit-row')) {
                    $block++;
                    foreach (self::FINANCIAL_UNITS as $unit) {
                        $summaryPositions[] = [count($output), $core, $unit];
                        $output[] = [
                            'pap' => $labels[$core], 'indicator' => '', 'office' => $unit,
                            'is_financial_summary' => true, '_identity_key' => $block,
                        ];
                    }
                }

                continue;
            }
            if ($this->hasClass($row, 'default-office-unit-row')) {
                $block++;
                foreach ($xpath->query('.//*[@data-default-office-unit]', $row) as $office) {
                    $unit = $office->getAttribute('data-default-office-unit');
                    $summaryPositions[] = [count($output), $core, $unit];
                    $output[] = [
                        'pap' => $labels[$core] ?? '', 'indicator' => '', 'office' => $unit,
                        'is_financial_summary' => true, '_identity_key' => $block,
                    ];
                }

                continue;
            }
            if (! $row->hasAttribute('data-row-id')) {
                if ($this->hasClass($row, 'sub-activity-label-row')) {
                    $output[] = ['pap' => $this->text($row->textContent), '_identity_key' => ++$block];
                }

                continue;
            }

            $block++;
            $rowId = (int) $row->getAttribute('data-row-id');
            $indicatorId = (int) $row->getAttribute('data-indicator-id');
            $type = $row->getAttribute('data-indicator-type');
            $ids = $this->csv($row->getAttribute('data-input-office-ids'));
            $names = explode('|', $row->getAttribute('data-input-office-names'));
            $groups = $this->officeGroups($row, $ids);
            $pap = [];
            foreach ($xpath->query('./td[1]/div', $row) as $label) {
                $pap[] = $this->text($label->textContent);
            }
            $indicator = $xpath->query('./td[2]/div/span[1]', $row)->item(0);
            $identity = [
                'pap' => implode("\n", $pap),
                'indicator' => $this->text($indicator?->textContent ?? ''),
                'indicator_type' => $type, '_identity_key' => $block,
            ];
            $officeIndex = 0;
            $provinceIndex = 0;
            $provinces = array_values(array_filter($groups, fn ($group) => $group['province']));
            foreach ($xpath->query('./td[3]//div[contains(concat(" ",normalize-space(@class)," ")," office-line ")]', $row) as $line) {
                $aggregate = true;
                if ($this->hasClass($line, 'car-office-line')) {
                    $lineIds = $ids;
                } elseif ($this->hasClass($line, 'group-total-office-line')) {
                    $lineIds = $provinces[$provinceIndex++]['ids'] ?? [];
                } else {
                    $lineIds = isset($ids[$officeIndex]) ? [$ids[$officeIndex]] : [];
                    $officeIndex++;
                    $aggregate = false;
                }
                $output[] = [
                    ...$identity, 'office' => $this->text($line->textContent),
                    'physical_target' => $this->values($sources, 'physical_target', $rowId, $indicatorId, $lineIds, $type),
                    'physical_accomplishment' => $this->values($sources, 'physical_accomplishment', $rowId, $indicatorId, $lineIds, $type),
                    'remarks' => $aggregate ? '' : (string) ($sources['physical_accomplishment'][$rowId][$indicatorId][$lineIds[0] ?? 0]['remarks'] ?? ''),
                ];
            }

            // This is the UI's officeIdsForDefaultFinancialUnit mapping: use
            // assigned input IDs and group boundaries, never a guessed province.
            $units = ['CAR' => $ids, 'RO' => []];
            foreach ($ids as $index => $id) {
                if ($this->unit($names[$index] ?? '') === 'RO') {
                    $units['RO'][] = $id;
                }
            }
            foreach ($groups as $group) {
                if ($group['province']) {
                    $units[$this->unit($group['name'])] = $group['ids'];
                }
            }
            foreach ($units as $unit => $unitIds) {
                foreach (['financial_target', 'financial_accomplishment'] as $kind) {
                    foreach ($this->values($sources, $kind, $rowId, $indicatorId, $unitIds, 'cumulative') as $month => $value) {
                        $financial[$core][$unit][$kind][$month] = ($financial[$core][$unit][$kind][$month] ?? 0) + $value;
                    }
                }
            }
        }

        foreach ($summaryPositions as [$index, $core, $unit]) {
            foreach (['financial_target', 'financial_accomplishment'] as $kind) {
                $output[$index][$kind] = $financial[$core][$this->unit($unit)][$kind] ?? array_fill_keys(self::MONTHS, 0.0);
            }
        }

        return $output;
    }

    private function values(array $sources, string $kind, int $row, int $indicator, array $offices, string $type): array
    {
        $max = str_starts_with(strtolower(trim($type)), 'non') && str_starts_with($kind, 'physical');
        $values = [];
        foreach (self::MONTHS as $month) {
            $numbers = array_map(fn ($office) => (float) ($sources[$kind][$row][$indicator][$office][$month] ?? 0), $offices);
            $values[$month] = $max ? max([0.0, ...$numbers]) : array_sum($numbers);
        }

        return $values;
    }

    private function officeGroups(DOMElement $row, array $ids): array
    {
        $names = explode('|', $row->getAttribute('data-office-names'));
        $flags = $this->csv($row->getAttribute('data-input-group-penro-flags'));
        $ends = $this->csv($row->getAttribute('data-input-break-indices'));
        sort($ends);
        $ends[] = count($ids) - 1;
        $start = 0;
        $groups = [];
        foreach ($ends as $index => $end) {
            if ($end < $start || $end >= count($ids)) {
                continue;
            }
            $groups[] = ['name' => $names[$index] ?? '', 'province' => (bool) ($flags[$index] ?? false), 'ids' => array_slice($ids, $start, $end - $start + 1)];
            $start = $end + 1;
        }

        return $groups;
    }

    private function csv(string $value): array
    {
        return $value === '' ? [] : array_map('intval', explode(',', $value));
    }

    private function hasClass(DOMElement $node, string $class): bool
    {
        return in_array($class, preg_split('/\s+/', $node->getAttribute('class')), true);
    }

    private function text(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    private function unit(string $value): string
    {
        $unit = $this->text(preg_replace('/\b(PENRO|CENRO|TOTAL)\b/', '', strtoupper($value)) ?? '');

        return in_array($unit, ['MOUNTAIN PROVINCE', 'MT PROVINCE', 'MT. PROVINCE'], true)
            ? 'MT.PROVINCE'
            : $unit;
    }
}
