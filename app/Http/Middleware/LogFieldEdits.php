<?php

namespace App\Http\Middleware;

use App\Models\EditHistory;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response;

class LogFieldEdits
{
    public function handle(Request $request, Closure $next): Response
    {
        $routeName = (string) optional($request->route())->getName();
        $segments = explode('.', $routeName);

        $module = $segments[1] ?? 'unknown';
        $normalizedModule = $this->normalizeModuleName($module);
        $editedPart = $segments[2] ?? 'unknown';
        $action = $segments[3] ?? strtolower($request->method());

        $payload = $request->except(['_token', '_method']);
        $entryBaseline = $this->buildEntryBaseline($payload, $normalizedModule, $editedPart);

        $response = $next($request);

        if (!$this->shouldLog($request, $response)) {
            return $response;
        }

        if (!Schema::hasTable('edit_histories')) {
            return $response;
        }

        $recordKey = $request->route('indicator')
            ?? $request->route('program')
            ?? $request->route('target')
            ?? $request->route('accomplishment');

        $recordId = is_object($recordKey) && method_exists($recordKey, 'getKey')
            ? $recordKey->getKey()
            : $recordKey;

        EditHistory::create([
            'user_id' => optional($request->user())->id,
            'user_name' => optional($request->user())->name,
            'module' => $normalizedModule,
            'edited_part' => $editedPart,
            'action' => $action,
            'route_name' => $routeName,
            'http_method' => $request->method(),
            'record_identifier' => $recordId ? (string) $recordId : null,
            'changed_fields' => $this->extractChangedFields($payload, $normalizedModule, $editedPart, $entryBaseline),
            'request_snapshot' => $this->buildSnapshot($payload),
        ]);

        return $response;
    }

    private function shouldLog(Request $request, Response $response): bool
    {
        $route = $request->route();
        if (!$route) {
            return false;
        }

        $routeName = (string) $route->getName();

        if (!str_starts_with($routeName, 'admin.')) {
            return false;
        }

        if (!preg_match('/\.(targets|accomplishments|indicators|pap)\./', $routeName)) {
            return false;
        }

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return $response->getStatusCode() < 400;
    }

    private function buildSnapshot(array $payload): array
    {
        if (isset($payload['entries']) && is_array($payload['entries'])) {
            $entryItems = array_values($payload['entries']);
            $entryCount = count($entryItems);
            $sampleItems = array_slice($entryItems, 0, 3);

            return [
                'entries_count' => $entryCount,
                'entries_sample' => $this->normalizeValue($sampleItems),
                'has_more_entries' => $entryCount > 3,
                'sample_entry_keys' => array_keys((array) ($payload['entries'][0] ?? [])),
            ];
        }

        return $this->normalizeValue($payload);
    }

    private function extractChangedFields(array $payload, string $module, string $editedPart, array $entryBaseline = []): array
    {
        $fields = array_keys($payload);

        if (!isset($payload['entries']) || !is_array($payload['entries'])) {
            return array_values(array_unique($fields));
        }

        $fieldsWithoutGenericEntries = array_values(array_filter(
            $fields,
            fn ($field) => $field !== 'entries'
        ));

        $changedByDiff = $this->extractChangedEntryPeriodsByDiff($payload['entries'], $module, $editedPart, $entryBaseline);

        if (in_array($editedPart, ['targets', 'accomplishments'], true)) {
            // Strict mode: for cell-based grids, show only truly changed period cells.
            return array_values(array_unique(array_merge($fieldsWithoutGenericEntries, $changedByDiff)));
        }

        if (count($changedByDiff) > 0) {
            return array_values(array_unique(array_merge($fieldsWithoutGenericEntries, $changedByDiff)));
        }

        $entryKeys = [];
        $periodsWithSubmittedValue = [];
        foreach ($payload['entries'] as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            foreach (array_keys($entry) as $entryKey) {
                $entryKeys[$entryKey] = true;
            }

            foreach ($entry as $entryKey => $entryValue) {
                if ($this->isPeriodKey($entryKey) && $this->hasMeaningfulValue($entryValue)) {
                    $periodsWithSubmittedValue[$entryKey] = true;
                }
            }
        }

        $periodKeys = [
            'jan', 'feb', 'mar',
            'apr', 'may', 'jun',
            'jul', 'aug', 'sep',
            'oct', 'nov', 'dec',
        ];

        $detectedPeriods = [];
        foreach ($periodKeys as $periodKey) {
            if (isset($periodsWithSubmittedValue[$periodKey])) {
                $detectedPeriods[] = 'entries.' . $periodKey;
            }
        }

        // Fallback for legacy payloads where all values are empty but keys are present.
        if (count($detectedPeriods) === 0) {
            foreach ($periodKeys as $periodKey) {
                if (isset($entryKeys[$periodKey])) {
                    $detectedPeriods[] = 'entries.' . $periodKey;
                }
            }
        }

        if (count($detectedPeriods) === 0) {
            return array_values(array_unique($fields));
        }

        return array_values(array_unique(array_merge($fieldsWithoutGenericEntries, $detectedPeriods)));
    }

    private function extractChangedEntryPeriodsByDiff(array $entries, string $module, string $editedPart, array $entryBaseline = []): array
    {
        if (!in_array($editedPart, ['targets', 'accomplishments'], true)) {
            return [];
        }

        $periodKeys = [
            'jan', 'feb', 'mar',
            'apr', 'may', 'jun',
            'jul', 'aug', 'sep',
            'oct', 'nov', 'dec',
        ];

        $changedPeriods = [];

        foreach ($entries as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $programId = $entry['program_id'] ?? null;
            $indicatorId = $entry['indicator_id'] ?? null;
            $officeId = $entry['office_id'] ?? null;
            $year = $entry['year'] ?? null;

            if (!$programId || !$indicatorId || !$year) {
                continue;
            }

            $entryKey = $this->buildEntryLookupKey($programId, $indicatorId, $officeId, $year);
            $existingRow = $entryBaseline[$entryKey] ?? [];

            foreach ($periodKeys as $periodKey) {
                if (!array_key_exists($periodKey, $entry)) {
                    continue;
                }

                $submittedValue = $entry[$periodKey];
                $existingValue = is_array($existingRow) ? ($existingRow[$periodKey] ?? null) : null;

                if ($this->valuesDiffer($submittedValue, $existingValue)) {
                    $changedPeriods[] = 'entries.' . $periodKey;
                }
            }
        }

        return array_values(array_unique($changedPeriods));
    }

    private function buildEntryBaseline(array $payload, string $module, string $editedPart): array
    {
        if (!isset($payload['entries']) || !is_array($payload['entries'])) {
            return [];
        }

        if (!in_array($editedPart, ['targets', 'accomplishments'], true)) {
            return [];
        }

        $tableName = $this->resolveTableName($module, $editedPart);
        if (!$tableName || !Schema::hasTable($tableName)) {
            return [];
        }

        $periodKeys = [
            'jan', 'feb', 'mar',
            'apr', 'may', 'jun',
            'jul', 'aug', 'sep',
            'oct', 'nov', 'dec',
        ];

        $baseline = [];

        foreach ($payload['entries'] as $entry) {
            if (!is_array($entry)) {
                continue;
            }

            $programId = $entry['program_id'] ?? null;
            $indicatorId = $entry['indicator_id'] ?? null;
            $officeId = $entry['office_id'] ?? null;
            $year = $entry['year'] ?? null;

            if (!$programId || !$indicatorId || !$year) {
                continue;
            }

            $lookupKey = $this->buildEntryLookupKey($programId, $indicatorId, $officeId, $year);
            if (array_key_exists($lookupKey, $baseline)) {
                continue;
            }

            $row = DB::table($tableName)
                ->where('program_id', $programId)
                ->where('indicator_id', $indicatorId)
                ->where('year', $year)
                ->when($officeId, fn ($query) => $query->where('office_id', $officeId))
                ->first();

            if (!$row) {
                $baseline[$lookupKey] = [];
                continue;
            }

            $rowValues = [];
            foreach ($periodKeys as $periodKey) {
                $rowValues[$periodKey] = $row->{$periodKey} ?? null;
            }

            $baseline[$lookupKey] = $rowValues;
        }

        return $baseline;
    }

    private function buildEntryLookupKey(mixed $programId, mixed $indicatorId, mixed $officeId, mixed $year): string
    {
        $office = is_null($officeId) ? 'null' : (string) $officeId;
        return (string) $programId . '|' . (string) $indicatorId . '|' . $office . '|' . (string) $year;
    }

    private function normalizeModuleName(string $module): string
    {
        return match (strtolower($module)) {
            'gass_physical', 'gass' => 'GASS',
            'sto' => 'STO',
            'enf' => 'ENF',
            'pa' => 'PA',
            'engp' => 'ENGP',
            'lands' => 'LANDS',
            'soilcon' => 'SOILCON',
            'nra' => 'NRA',
            default => strtoupper($module),
        };
    }

    private function resolveTableName(string $module, string $editedPart): ?string
    {
        $normalizedModule = strtolower($module);

        if (!in_array($normalizedModule, ['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra'], true)) {
            return null;
        }

        if ($editedPart === 'targets') {
            return $normalizedModule . '_targets';
        }

        if ($editedPart === 'accomplishments') {
            return $normalizedModule . '_accomplishment';
        }

        return null;
    }

    private function valuesDiffer(mixed $submittedValue, mixed $existingValue): bool
    {
        $submitted = $this->normalizeComparable($submittedValue);
        $existing = $this->normalizeComparable($existingValue);

        return $submitted !== $existing;
    }

    private function normalizeComparable(mixed $value): mixed
    {
        if (is_null($value)) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return null;
            }

            if (is_numeric($trimmed)) {
                return (float) $trimmed;
            }

            return $trimmed;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return $value;
    }

    private function isPeriodKey(string $key): bool
    {
        return in_array($key, [
            'jan', 'feb', 'mar',
            'apr', 'may', 'jun',
            'jul', 'aug', 'sep',
            'oct', 'nov', 'dec',
        ], true);
    }

    private function hasMeaningfulValue(mixed $value): bool
    {
        if (is_null($value)) {
            return false;
        }

        if (is_string($value)) {
            return trim($value) !== '';
        }

        if (is_numeric($value)) {
            return (float) $value !== 0.0;
        }

        return true;
    }

    private function normalizeValue(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 4) {
            return '[max depth reached]';
        }

        if (is_array($value)) {
            $normalized = [];
            $count = 0;

            foreach ($value as $key => $item) {
                if ($count >= 20) {
                    $normalized['__truncated__'] = 'Only first 20 items are shown';
                    break;
                }

                $normalized[$key] = $this->normalizeValue($item, $depth + 1);
                $count++;
            }

            return $normalized;
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            return strlen($trimmed) > 500
                ? substr($trimmed, 0, 500) . '... [truncated]'
                : $trimmed;
        }

        return $value;
    }
}
