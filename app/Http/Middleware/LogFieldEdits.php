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
        [$module, $editedPart, $action] = $this->parseRouteContext($routeName, $request);

        $normalizedModule = $this->normalizeModuleName($module);
        $payload = $request->except([
            '_token',
            '_method',
            'password',
            'password_confirmation',
            'current_password',
        ]);
        $entryBaseline = $this->buildEntryBaseline($payload, $normalizedModule, $editedPart);
        $payloadBaseline = $this->buildPayloadBaseline($request, $payload);
        $changedFields = $this->extractChangedFields($payload, $normalizedModule, $editedPart, $entryBaseline, $payloadBaseline);
        $previousDataPreview = $this->buildPreviousDataPreview($request, $payload, $editedPart, $entryBaseline, $payloadBaseline);
        $changedDataPreview = $this->buildChangedDataPreview($payload, $changedFields);

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
            'user_role' => optional($request->user())->role,
            'module' => $normalizedModule,
            'edited_part' => $editedPart,
            'action' => $action,
            'route_name' => $routeName,
            'http_method' => $request->method(),
            'record_identifier' => $recordId ? (string) $recordId : null,
            'changed_fields' => $changedFields,
            'request_snapshot' => $this->buildSnapshot($payload, $previousDataPreview, $changedDataPreview),
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
        $isFieldRoute = str_starts_with($routeName, 'admin.')
            && preg_match('/\.(targets|accomplishments|indicators|pap)\./', $routeName);
        $isUserRoleRoute = in_array($routeName, ['users.store', 'users.update', 'users.destroy'], true);

        if (!$isFieldRoute && !$isUserRoleRoute) {
            return false;
        }

        if (!in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return false;
        }

        return $response->getStatusCode() < 400;
    }

    private function parseRouteContext(string $routeName, Request $request): array
    {
        $segments = array_values(array_filter(explode('.', $routeName)));

        if (($segments[0] ?? null) === 'admin') {
            return [
                $segments[1] ?? 'unknown',
                $segments[2] ?? 'unknown',
                $segments[3] ?? strtolower($request->method()),
            ];
        }

        if (($segments[0] ?? null) === 'users') {
            return ['users', 'roles', $segments[1] ?? strtolower($request->method())];
        }

        return [
            $segments[0] ?? 'unknown',
            $segments[1] ?? 'unknown',
            strtolower($request->method()),
        ];
    }

    private function buildSnapshot(array $payload, array $previousDataPreview = [], array $changedDataPreview = []): array
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
                'previous_data' => array_values($previousDataPreview),
                'changed_data' => array_values($changedDataPreview),
            ];
        }

        $snapshot = $this->normalizeValue($payload);
        if (!is_array($snapshot)) {
            $snapshot = ['value' => $snapshot];
        }

        if (!empty($previousDataPreview)) {
            $snapshot['previous_data'] = array_values($previousDataPreview);
        }

        if (!empty($changedDataPreview)) {
            $snapshot['changed_data'] = array_values($changedDataPreview);
        }

        return $snapshot;
    }

    private function buildChangedDataPreview(array $payload, array $changedFields): array
    {
        $periodLabels = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar',
            'apr' => 'Apr', 'may' => 'May', 'jun' => 'Jun',
            'jul' => 'Jul', 'aug' => 'Aug', 'sep' => 'Sep',
            'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec',
        ];

        $preview = [];

        if (isset($payload['entries']) && is_array($payload['entries'])) {
            foreach ($changedFields as $field) {
                if (!str_starts_with($field, 'entries.')) {
                    continue;
                }

                $periodKey = substr($field, strlen('entries.'));
                $label = $periodLabels[$periodKey] ?? ucwords(str_replace('_', ' ', $periodKey));

                foreach ($payload['entries'] as $entry) {
                    if (!is_array($entry) || !array_key_exists($periodKey, $entry)) {
                        continue;
                    }

                    $value = $entry[$periodKey];
                    if ($value === null || $value === '') {
                        continue;
                    }

                    $preview[] = $label . ': ' . $value;
                }
            }

            return array_values(array_unique($preview));
        }

        foreach ($changedFields as $field) {
            if (!array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];
            if (is_array($value)) {
                $value = implode(', ', array_map('strval', $value));
            }

            if ($value === null || $value === '') {
                $value = '—';
            }

            if ($field === 'role') {
                $value = ucfirst(str_replace('-', ' ', (string) $value));
            }

            $label = ucwords(str_replace('_', ' ', (string) $field));
            $preview[] = $label . ': ' . $value;
        }

        return array_values(array_unique($preview));
    }

    private function buildPreviousDataPreview(Request $request, array $payload, string $editedPart, array $entryBaseline = [], array $payloadBaseline = []): array
    {
        if (isset($payload['entries']) && is_array($payload['entries'])) {
            return $this->buildEntryPreviousPreview($payload['entries'], $entryBaseline);
        }

        if (!empty($payloadBaseline)) {
            $preview = [];

            foreach ($payloadBaseline as $field => $oldValue) {
                if (!array_key_exists($field, $payload)) {
                    continue;
                }

                if (!$this->valuesDiffer($payload[$field], $oldValue)) {
                    continue;
                }

                $label = ucwords(str_replace('_', ' ', (string) $field));
                $displayValue = $oldValue;

                if ($displayValue === null || $displayValue === '') {
                    $displayValue = '—';
                } elseif ($field === 'role') {
                    $displayValue = ucfirst(str_replace('-', ' ', (string) $displayValue));
                }

                $preview[] = $label . ': ' . $displayValue;
            }

            return array_values(array_unique($preview));
        }

        return [];
    }

    private function buildPayloadBaseline(Request $request, array $payload): array
    {
        if (isset($payload['entries']) && is_array($payload['entries'])) {
            return [];
        }

        $routeModel = $this->resolveRouteModel($request);
        if (!is_object($routeModel)) {
            return [];
        }

        $baseline = [];
        foreach (array_keys($payload) as $field) {
            if ($field === 'user_id') {
                continue;
            }

            $baseline[$field] = $routeModel->{$field} ?? null;
        }

        return $baseline;
    }

    private function resolveRouteModel(Request $request): mixed
    {
        foreach (['user', 'indicator', 'program', 'target', 'accomplishment'] as $routeKey) {
            $routeValue = $request->route($routeKey);

            if ($routeKey === 'user' && is_numeric($routeValue)) {
                $routeValue = \App\Models\User::find((int) $routeValue);
            }

            if (is_object($routeValue)) {
                return $routeValue;
            }
        }

        return null;
    }

    private function buildEntryPreviousPreview(array $entries, array $entryBaseline = []): array
    {
        $periodLabels = [
            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar',
            'apr' => 'Apr', 'may' => 'May', 'jun' => 'Jun',
            'jul' => 'Jul', 'aug' => 'Aug', 'sep' => 'Sep',
            'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec',
        ];

        $previousPreview = [];

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

            foreach ($periodLabels as $periodKey => $label) {
                if (!array_key_exists($periodKey, $entry)) {
                    continue;
                }

                $existingValue = is_array($existingRow) ? ($existingRow[$periodKey] ?? null) : null;
                if (!$this->valuesDiffer($entry[$periodKey], $existingValue)) {
                    continue;
                }

                $displayValue = $existingValue;
                if ($displayValue === null || $displayValue === '') {
                    $displayValue = '—';
                }

                $previousPreview[] = $label . ': ' . $displayValue;
            }
        }

        return array_values(array_unique($previousPreview));
    }

    private function extractChangedFields(array $payload, string $module, string $editedPart, array $entryBaseline = [], array $payloadBaseline = []): array
    {
        $fields = array_keys($payload);

        if (!isset($payload['entries']) || !is_array($payload['entries'])) {
            if (empty($payloadBaseline)) {
                return array_values(array_unique($fields));
            }

            $changedFields = [];
            foreach ($payload as $field => $value) {
                if ($field === 'user_id') {
                    continue;
                }

                if (!array_key_exists($field, $payloadBaseline)) {
                    if ($this->hasMeaningfulValue($value)) {
                        $changedFields[] = $field;
                    }
                    continue;
                }

                if ($this->valuesDiffer($value, $payloadBaseline[$field])) {
                    $changedFields[] = $field;
                }
            }

            return array_values(array_unique($changedFields));
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

            $row = $this->findExistingEntryRow($tableName, $programId, $indicatorId, $officeId, $year);

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
            'paria' => 'PARIA',
            'cobb' => 'COBB',
            'continuing' => 'CONTINUING',
            'user', 'users' => 'USERS',
            default => strtoupper($module),
        };
    }

    private function resolveTableName(string $module, string $editedPart): ?string
    {
        $normalizedModule = strtolower($module);

        if (!in_array($normalizedModule, ['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing'], true)) {
            return null;
        }

        if ($editedPart === 'targets') {
            return $normalizedModule . '_targets';
        }

        if ($editedPart === 'accomplishments') {
            $pluralTable = $normalizedModule . '_accomplishments';
            if (Schema::hasTable($pluralTable)) {
                return $pluralTable;
            }

            return $normalizedModule . '_accomplishment';
        }

        return null;
    }

    private function findExistingEntryRow(string $tableName, mixed $programId, mixed $indicatorId, mixed $officeId, mixed $year): ?object
    {
        $hasDirectProgramColumn = Schema::hasColumn($tableName, 'program_id');
        $hasDirectIndicatorColumn = Schema::hasColumn($tableName, 'indicator_id');
        $yearColumn = Schema::hasColumn($tableName, 'year') ? 'year' : (Schema::hasColumn($tableName, 'years') ? 'years' : null);
        $officeColumn = Schema::hasColumn($tableName, 'office_id') ? 'office_id' : (Schema::hasColumn($tableName, 'office_ids') ? 'office_ids' : null);

        if ($hasDirectProgramColumn && $hasDirectIndicatorColumn && $yearColumn) {
            return DB::table($tableName)
                ->where('program_id', $programId)
                ->where('indicator_id', $indicatorId)
                ->where($yearColumn, $year)
                ->when($officeId && $officeColumn, fn ($query) => $query->where($officeColumn, $officeId))
                ->first();
        }

        $query = DB::table($tableName);

        if ($yearColumn) {
            $query->where($yearColumn, (string) $year);
        }

        if ($officeId && $officeColumn) {
            $query->where($officeColumn, $officeId);
        }

        return $query->get()->first(function ($row) use ($programId, $indicatorId) {
            $meta = $this->decodeJsonObject($row->values ?? null);

            return (string) ($meta['program_id'] ?? '') === (string) $programId
                && (string) ($meta['indicator_id'] ?? '') === (string) $indicatorId;
        });
    }

    private function decodeJsonObject(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (is_object($value)) {
            return (array) $value;
        }

        if (!is_string($value) || trim($value) === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
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
