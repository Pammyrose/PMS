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

        <main class="flex-grow-1 p-3 bg-light min-vh-100">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                    <h5 class="mb-1 text-2xl font-bold">History</h5>
                    <p class="text-muted mb-0 small">Edit updates are tracked here.</p>
                </div>
                <span class="badge text-bg-primary fs-6">{{ $histories->total() }} records</span>
            </div>

            <form method="GET" action="{{ route('history') }}" class="mb-2">
                <div class="d-flex flex-wrap align-items-end gap-1">
                    <div>
                        <label for="date_from" class="form-label fw-semibold small mb-1">From</label>
                        <input type="date" id="date_from" name="date_from" class="form-control form-control-sm py-1"
                            value="{{ $filters['date_from'] ?? '' }}">
                    </div>
                    <div>
                        <label for="date_to" class="form-label fw-semibold small mb-1">To</label>
                        <input type="date" id="date_to" name="date_to" class="form-control form-control-sm py-1"
                            value="{{ $filters['date_to'] ?? '' }}">
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm py-1 px-2">
                            <i class="fa-solid fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('history') }}" class="btn btn-outline-secondary btn-sm py-1 px-2">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>

            <div class="relative overflow-x-auto bg-white shadow-sm rounded-lg border border-default">
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-sm px-10 py-2 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
                        <tr>
                            <th scope="col" class="px-3 py-2 font-medium">Date/Time (PHT)</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">User / Role</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">Module</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">Part</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">Edited</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">Action</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">Previous Data</th>
                            <th scope="col" class="px-3 py-2 font-medium text-center">Input Changed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($histories as $row)
                            <tr class="bg-neutral-primary border-b border-default">
                                <td class="px-3 py-2">
                                    {{ optional($row->created_at)->copy()->timezone('Asia/Manila')->format('M d, Y h:i A') }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    <div class="fw-semibold">{{ $row->user_name ?? 'Unknown user' }}</div>
                                    @php
                                        $roleKey = strtolower((string) ($row->user_role ?? ''));
                                        $roleLabel = match ($roleKey) {
                                            'super-admin' => 'Super Administrator',
                                            'admin' => 'Administrator',
                                            'staff' => 'Staff',
                                            'user' => 'User',
                                            default => ($row->user_role ? ucfirst(str_replace('-', ' ', (string) $row->user_role)) : 'No role'),
                                        };
                                        $roleBadge = match ($roleKey) {
                                            'super-admin' => 'text-bg-danger',
                                            'admin' => 'text-bg-primary',
                                            'staff' => 'text-bg-info',
                                            'user' => 'text-bg-secondary',
                                            default => 'text-bg-light',
                                        };
                                    @endphp
                                    <span class="badge {{ $roleBadge }} mt-1">{{ $roleLabel }}</span>
                                </td>
                                <td class="px-3 py-2 text-center">
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
                                            'paria' => 'PARIA',
                                            'cobb' => 'COBB',
                                            'continuing' => 'CONTINUING',
                                            'users', 'user' => 'Users & Roles',
                                            default => strtoupper((string) $row->module),
                                        };
                                    @endphp
                                    {{ $moduleLabel }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $employeeSnapshot = is_array($row->request_snapshot) ? $row->request_snapshot : [];
                                        $employeeModuleKey = strtolower((string) $row->module);
                                        $editedEmployeeName = null;
                                        $editedEmployeeEmail = null;
                                        $isFieldModule = in_array($employeeModuleKey, [
                                            'gass_physical', 'gass', 'sto', 'enf', 'pa', 'engp',
                                            'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing',
                                        ], true);

                                        if ($isFieldModule) {
                                            $programId = null;

                                            if (filled($employeeSnapshot['program_id'] ?? null)) {
                                                $programId = (int) $employeeSnapshot['program_id'];
                                            } elseif (filled($employeeSnapshot['ppa_id'] ?? null)) {
                                                $programId = (int) $employeeSnapshot['ppa_id'];
                                            } elseif (!empty($employeeSnapshot['entries_sample'][0]['program_id'])) {
                                                $programId = (int) $employeeSnapshot['entries_sample'][0]['program_id'];
                                            }

                                            if ($programId) {
                                                $editedEmployeeName = \Illuminate\Support\Facades\DB::table('ppa')
                                                    ->where('id', $programId)
                                                    ->value('name');
                                            }

                                            if (!$editedEmployeeName && filled($employeeSnapshot['title'] ?? null)) {
                                                $editedEmployeeName = (string) $employeeSnapshot['title'];
                                            }
                                        }

                                        if (in_array($employeeModuleKey, ['users', 'user'], true)) {
                                            $editedEmployeeName = filled($employeeSnapshot['name'] ?? null) ? (string) $employeeSnapshot['name'] : null;
                                            $editedEmployeeEmail = filled($employeeSnapshot['email'] ?? null) ? (string) $employeeSnapshot['email'] : null;

                                            foreach (($employeeSnapshot['previous_data'] ?? []) as $previousItem) {
                                                $previousItem = (string) $previousItem;

                                                if (!$editedEmployeeName && str_starts_with($previousItem, 'Name: ')) {
                                                    $editedEmployeeName = substr($previousItem, 6);
                                                }

                                                if (!$editedEmployeeEmail && str_starts_with($previousItem, 'Email: ')) {
                                                    $editedEmployeeEmail = substr($previousItem, 7);
                                                }
                                            }
                                        }
                                    @endphp

                                    @if($editedEmployeeName || $editedEmployeeEmail)
                                        <div class="fw-semibold">{{ $editedEmployeeName ?? 'Unknown employee' }}</div>
                                        @if(!$isFieldModule && $editedEmployeeEmail)
                                            <div class="text-muted small">{{ $editedEmployeeEmail }}</div>
                                        @endif
                                    @elseif($row->record_identifier)
                                        <span class="text-muted small">ID: {{ $row->record_identifier }}</span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $editedPartFields = is_array($row->changed_fields) ? $row->changed_fields : [];
                                        $editedPartMonthLabels = [
                                            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
                                            'may' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'aug' => 'Aug',
                                            'sep' => 'Sep', 'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec',
                                        ];

                                        $specificEditedParts = array_values(array_unique(array_filter(array_map(function ($field) use ($editedPartMonthLabels) {
                                            if (str_starts_with((string) $field, 'entries.')) {
                                                $periodKey = substr((string) $field, strlen('entries.'));
                                                return $editedPartMonthLabels[$periodKey] ?? ucwords(str_replace('_', ' ', $periodKey));
                                            }

                                            return match ((string) $field) {
                                                'name' => 'Name',
                                                'email' => 'Email',
                                                'role' => 'Role',
                                                'title' => 'Title',
                                                'program' => 'Program',
                                                'project' => 'Project',
                                                'activities' => 'Activity',
                                                'subactivities' => 'Sub-activity',
                                                'indicator_name' => 'Performance Indicator',
                                                'indicator_type_id' => 'Indicator Type',
                                                default => ucwords(str_replace('_', ' ', (string) $field)),
                                            };
                                        }, $editedPartFields))));

                                        $editedPartLabel = count($specificEditedParts)
                                            ? implode(', ', $specificEditedParts)
                                            : match (strtolower((string) $row->edited_part)) {
                                                'pap' => 'P/A/P',
                                                'indicators' => 'Indicators',
                                                'targets' => 'Targets',
                                                'accomplishments' => 'Accomplishments',
                                                'roles' => 'Users & Roles',
                                                default => ucwords(str_replace('_', ' ', (string) $row->edited_part)),
                                            };
                                    @endphp
                                    {{ $editedPartLabel }}
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $actionKey = strtolower((string) $row->action);
                                        $actionLabel = match ($actionKey) {
                                            'store', 'create', 'post' => 'Created',
                                            'update', 'patch', 'put' => 'Updated',
                                            'destroy', 'delete' => 'Deleted',
                                            default => ucfirst((string) $row->action),
                                        };
                                        $actionBadge = match ($actionKey) {
                                            'store', 'create', 'post' => 'text-bg-success',
                                            'update', 'patch', 'put' => 'text-bg-warning',
                                            'destroy', 'delete' => 'text-bg-danger',
                                            default => 'text-bg-secondary',
                                        };
                                    @endphp
                                    <span class="badge {{ $actionBadge }}">{{ $actionLabel }}</span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $snapshot = is_array($row->request_snapshot) ? $row->request_snapshot : [];
                                        $previousDisplay = is_array($snapshot['previous_data'] ?? null)
                                            ? array_values(array_filter($snapshot['previous_data']))
                                            : [];
                                    @endphp
                                    @if(count($previousDisplay))
                                        <span class="small">{{ implode(', ', $previousDisplay) }}</span>
                                    @else
                                        <span class="text-muted small">No previous data</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @php
                                        $changed = is_array($row->changed_fields) ? $row->changed_fields : [];
                                        $snapshot = is_array($row->request_snapshot) ? $row->request_snapshot : [];
                                        $changedFromSnapshot = is_array($snapshot['changed_data'] ?? null)
                                            ? array_values(array_filter($snapshot['changed_data']))
                                            : [];
                                        $monthLabels = [
                                            'jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar', 'apr' => 'Apr',
                                            'may' => 'May', 'jun' => 'Jun', 'jul' => 'Jul', 'aug' => 'Aug',
                                            'sep' => 'Sep', 'oct' => 'Oct', 'nov' => 'Nov', 'dec' => 'Dec',
                                        ];

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

                                        $payloadMap = [];
                                        foreach (($snapshot['entries_sample'] ?? []) as $sampleRow) {
                                            if (!is_array($sampleRow)) {
                                                continue;
                                            }

                                            foreach ($sampleRow as $sampleKey => $sampleValue) {
                                                if ($sampleValue === null || $sampleValue === '') {
                                                    continue;
                                                }

                                                $payloadMap[$sampleKey] = (string) $sampleValue;
                                            }
                                        }

                                        foreach ($snapshot as $snapshotKey => $snapshotValue) {
                                            if (in_array($snapshotKey, ['entries_count', 'entries_sample', 'has_more_entries', 'sample_entry_keys', 'previous_data'], true)) {
                                                continue;
                                            }

                                            if ($snapshotValue === null || $snapshotValue === '') {
                                                continue;
                                            }

                                            $payloadMap[$snapshotKey] = is_array($snapshotValue)
                                                ? implode(', ', array_map('strval', $snapshotValue))
                                                : (string) $snapshotValue;
                                        }

                                        $changedDisplay = array_values(array_filter(array_map(function ($field) use ($monthLabels, $payloadMap) {
                                            if (str_starts_with($field, 'entries.')) {
                                                $periodKey = substr($field, strlen('entries.'));
                                                if (!isset($monthLabels[$periodKey])) {
                                                    return null;
                                                }

                                                $label = $monthLabels[$periodKey];
                                                $value = $payloadMap[$periodKey] ?? null;

                                                return $value !== null && $value !== ''
                                                    ? $label . ': ' . $value
                                                    : $label;
                                            }

                                            $label = ucwords(str_replace('_', ' ', (string) $field));
                                            $value = $payloadMap[$field] ?? null;

                                            return $value !== null && $value !== ''
                                                ? $label . ': ' . $value
                                                : $label;
                                        }, $changed)));

                                        if (count($changedFromSnapshot)) {
                                            $changedDisplay = $changedFromSnapshot;
                                        }
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
                                <td colspan="8" class="px-3 py-2 text-center text-gray-500">No history records yet.</td>
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
