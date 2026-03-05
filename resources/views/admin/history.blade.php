<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-4 bg-light min-vh-100">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h5 class="mb-1 text-2xl font-bold">History</h5>
                </div>
                <span class="badge text-bg-primary">{{ $histories->total() }} records</span>
            </div>

            <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-lg border border-default">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-md px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">Date/Time</th>
                            <th scope="col" class="px-6 py-3 font-medium">User</th>
                            <th scope="col" class="px-6 py-3 font-medium">Module</th>
                            <th scope="col" class="px-6 py-3 font-medium">Edited Part</th>
                            <th scope="col" class="px-6 py-3 font-medium">Fields Changed</th>
                        </tr>
                    </thead>
                    <tbody>
                            @forelse($histories as $row)
                                <tr class="bg-neutral-primary border-b border-default">
                                    <td class="px-6 py-4">{{ optional($row->created_at)->format('M d, Y h:i A') }}</td>
                                    <td class="px-6 py-4">{{ $row->user_name ?? 'Unknown user' }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $moduleLabel = match (strtolower((string) $row->module)) {
                                                'gass_physical', 'gass' => 'GASS',
                                                'sto' => 'STO',
                                                'enf' => 'ENF',
                                                'pa' => 'PA',
                                                'engp' => 'ENGP',
                                                'lands' => 'LANDS',
                                                'soilcon' => 'SOILCON',
                                                'nra' => 'NRA',
                                                default => strtoupper((string) $row->module),
                                            };
                                        @endphp
                                        {{ $moduleLabel }}
                                    </td>
                                    <td class="px-6 py-4">{{ strtoupper(str_replace('_', ' ', $row->edited_part)) }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $changed = is_array($row->changed_fields) ? $row->changed_fields : [];
                                            $snapshot = is_array($row->request_snapshot) ? $row->request_snapshot : [];
                                            $monthLabels = [
                                                'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
                                                'may' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'aug' => 'Aug',
                                                'sep' => 'Sep', 'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec',
                                            ];

                                            // Backward-compatible display for old records that only logged "entries".
                                            if (count($changed) === 1 && ($changed[0] ?? null) === 'entries') {
                                                $legacyKeys = [];

                                                if (!empty($snapshot['sample_entry_keys']) && is_array($snapshot['sample_entry_keys'])) {
                                                    $legacyKeys = $snapshot['sample_entry_keys'];
                                                } elseif (!empty($snapshot['entries_sample'][0]) && is_array($snapshot['entries_sample'][0])) {
                                                    $legacyKeys = array_keys($snapshot['entries_sample'][0]);
                                                }

                                                if (count($legacyKeys)) {
                                                    $expanded = [];
                                                    foreach ($legacyKeys as $legacyKey) {
                                                        if (isset($monthLabels[$legacyKey])) {
                                                            $expanded[] = 'entries.' . $legacyKey;
                                                        }
                                                    }

                                                    if (count($expanded)) {
                                                        $changed = $expanded;
                                                    }
                                                }
                                            }

                                            $entriesSample = !empty($snapshot['entries_sample']) && is_array($snapshot['entries_sample'])
                                                ? $snapshot['entries_sample']
                                                : [];

                                            $monthValueMap = [];
                                            foreach ($monthLabels as $monthKey => $_monthLabel) {
                                                $values = [];
                                                foreach ($entriesSample as $sampleRow) {
                                                    if (!is_array($sampleRow) || !array_key_exists($monthKey, $sampleRow)) {
                                                        continue;
                                                    }

                                                    $rawValue = $sampleRow[$monthKey];
                                                    if ($rawValue === null || $rawValue === '') {
                                                        continue;
                                                    }

                                                    $values[] = (string) $rawValue;
                                                }

                                                $values = array_values(array_unique($values));
                                                if (count($values)) {
                                                    $monthValueMap[$monthKey] = $values;
                                                }
                                            }

                                            $changedDisplay = array_values(array_filter(array_map(function ($field) use ($monthLabels, $monthValueMap) {
                                                if (str_starts_with($field, 'entries.')) {
                                                    $periodKey = substr($field, strlen('entries.'));
                                                    if (!isset($monthLabels[$periodKey])) {
                                                        return null;
                                                    }

                                                    $label = $monthLabels[$periodKey];
                                                    $values = $monthValueMap[$periodKey] ?? [];

                                                    if (!count($values)) {
                                                        return $label;
                                                    }

                                                    if (count($values) === 1) {
                                                        return $label . ': ' . $values[0];
                                                    }

                                                    return $label . ': ' . $values[0] . ' (+' . (count($values) - 1) . ' more)';
                                                }

                                                return $field;
                                            }, $changed)));
                                        @endphp
                                        @if(count($changedDisplay))
                                            <span class="small">{{ implode(', ', $changedDisplay) }}</span>
                                        @else
                                            <span class="text-muted small">No payload keys</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No history records yet.</td>
                                </tr>
                            @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $histories->links() }}
            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
