<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (ENGP) - PMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --month-bg: #f1f5f9;
            --quarter-bg: #e7d8bd;
            --annual-bg: #cacaca;
            --header-blue: #1e40af;
            --border: #cbd5e1;
        }

        .year-header {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--header-blue);
            text-align: center;
            padding: 14px 0;
            background: linear-gradient(to right, #eff6ff, #dbeafe);
            border-bottom: 3px solid #3b82f6;
            margin-bottom: 1rem;
        }

        .group-header {
            font-size: 1rem;
            font-weight: 700;
            text-align: center;
            vertical-align: middle;
        }

        .group-target {
            background: #d1fae5;
            color: #065f46;
        }

        .group-accomp {
            background: #dbeafe;
            color: #1e40af;
        }

        .month-header {
            background: #496cce;
            color: white;
            text-align: center;
            font-weight: 600;
            font-size: 0.78rem;
            padding: 6px 4px;
            min-width: 44px;
            border: 1px solid #1e40af;
            white-space: nowrap;
        }

        .month-header.quarter {
            background: #f59e0b;
            min-width: 50px;
            font-size: 0.82rem;
            color: #000 !important;
            font-style: bold;
        }

        .month-header.annual {
            background: #334155;
            min-width: 50px;
            font-size: 0.9rem;
            font-weight: 700;
        }

        /* When Accomplishments are shown → pink month headers (JAN–DEC only) */
        th.month-header.accomp-month:not(.quarter):not(.annual) {
            background: #16958d !important;
            /* pastel pink */
            color: white !important;
            /* dark pink text for contrast */
        }

        .month-box {
            width: 100%;
            height: 28px;
            border: 1px solid var(--border);
            border-radius: 4px;
            text-align: center;
            font-size: 13px;
            padding: 2px 4px;
        }

        .month-box[readonly] {
            background: var(--month-bg);
            color: #334155;
            font-weight: 500;
            cursor: default;
        }

        .month-box:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }

        .office-lines {
            display: flex;
            flex-direction: column;
            gap: 4px;
            align-items: center;
        }

        .office-line {
            min-height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            line-height: 1.1;
            text-align: center;
        }

        .input-line {
            min-height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .target-box {
            background: #f0fdf4;
            border-color: #86efac;
        }

        .target-box[readonly] {
            background: #ecfccb !important;
            color: #166534;
        }

        .accomp-box {
            background: white;
        }

        .target-total {
            background: #e7d8bd !important;
        }

        .annual-target {
            background: #cacaca !important;
            font-weight: 600;
        }

        .quarter-total {
            background: var(--quarter-bg) !important;
        }

        .annual-total {
            background: var(--annual-bg) !important;
            font-weight: 600;
        }

        .car-total-box {
            background: #eef2ff !important;
            border-color: #dc2626;
            color: #1e3a8a;
            font-weight: 700;
        }

        .car-office-line {
            font-weight: 700;
            color: #1e3a8a;
        }

        .group-total-office-line {
            font-weight: 700;
            color: #0f766e;
        }

        .group-total-box {
            background: #ecfeff !important;
            border-color: #c48282;
            color: #115e59;
            font-weight: 700;
        }

        .table-container {
            overflow-x: auto;
            margin: 1rem 0;
            border: 1px solid var(--border);
            border-radius: 6px;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1400px;
        }

        th,
        td {
            border: 1px solid var(--border);
            vertical-align: middle;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: inherit;
        }

        thead tr.group-row th {
            position: sticky;
            top: 0;
            z-index: 11;
        }

        tr.bg-gray-100 td {
            background: #f8fafc;
            font-weight: 600;
        }

        tr.program-header {
            background: #e0e7ff !important;
            font-weight: 600 !important;
            color: #1e40af;
            cursor: pointer;
        }

        tr.program-header:hover {
            background: #c7d2fe !important;
        }

        tr.program-header td {
            user-select: none;
            padding: 16px !important;
        }

        .program-toggle-icon {
            display: inline-block;
            margin-right: 12px;
            transition: transform 0.3s ease;
            font-size: 0.85rem;
            color: #1e40af;
        }

        tr.program-header .program-toggle-icon {
            transition: transform 0.3s ease;
            transform: rotate(0deg);
        }

        .program-toggle-icon.rotate-180 {
            transform: rotate(-180deg);
        }

        .data-row:hover {
            background-color: #f1f5f9;
        }

        .black-checkbox {
            border-color: #000 !important;
        }

        .black-checkbox:checked {
            background-color: #2772fd !important;
            border-color: #2772fd !important;
        }

        .remarks-header {
            background: #fef3c7;
            color: #92400e;
            font-weight: 700;
            min-width: 280px;
        }

        .remarks-box {
            width: 100%;
            min-width: 200px;
            border: 1px solid var(--border);
            border-radius: 4px;
            box-sizing: border-box;
            font-size: 12px;
            padding: 2px 6px;
            height: 28px;
            min-height: 28px;
            max-height: 28px;
            resize: none;
            overflow-y: auto;
            line-height: 1.1;
        }

        .remarks-spacer {
            visibility: hidden;
            pointer-events: none;
        }

        td[data-dynamic-section="remarks"] .input-line {
            width: 100%;
            justify-content: flex-start;
        }
    </style>
</head>

<body class="bg-light">

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-3">

            <div class="year-header">
                (ENGP) - Physical Performance
            </div>

            <div class="bg-white rounded shadow p-3">
                <!-- TABS -->
                <div class="flex items-center mt-4">
                    <div class="flex gap-6">
                        <a href="{{ route('engp') }}">
                            <button class="font-semibold text-blue-600 border-b-2 border-blue-600 pb-2">
                                Physical
                            </button>
                        </a>
                        <button class="text-gray-400 pb-2">
                            Financial
                        </button>
                    </div>
                </div>

                    <div class="d-flex justify-content-end align-items-center mt-3 mb-1">
                        <form method="GET" action="{{ url()->current() }}" class="d-flex align-items-center gap-2">
                            @if(request()->filled('office_id'))
                                <input type="hidden" name="office_id" value="{{ request('office_id') }}">
                            @endif
                            <label for="year_filter" class="form-label fw-semibold text-muted mb-0 small">Year</label>
                            <select id="year_filter" name="year"
                                class="form-select form-select-sm shadow-sm border-primary-subtle"
                                style="width: 110px;"
                                onchange="this.form.submit()">
                                @php
                                    $selectedYear = (int) ($year ?? now()->year);
                                    $yearRangeOptions = $yearOptions ?? collect(range(now()->year + 1, 2020))->values();
                                @endphp
                                @foreach($yearRangeOptions as $optionYear)
                                    <option value="{{ $optionYear }}" {{ $selectedYear === (int) $optionYear ? 'selected' : '' }}>
                                        {{ $optionYear }}
                                    </option>
                                @endforeach
                            </select>
                        </form>
                    </div>

                    <div class="row g-3 mt-1 mb-2" id="performanceSummaryCards">
                        <div class="col-12 col-md-4">
                            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                                <div class="card-body text-white">
                                    <div class="fw-bold small text-white text-center">Targets</div>
                                    <div class="fs-3 fw-bold text-white text-center" id="summaryTargetTotal">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                                <div class="card-body text-white">
                                    <div class="fw-bold small text-white text-center">Accomplishments</div>
                                    <div class="fs-3 fw-bold text-white text-center" id="summaryAccompTotal">0</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="card border-0 shadow-sm h-100 bg-danger text-white">
                                <div class="card-body text-white">
                                    <div class="fw-bold small text-white text-center">Pending</div>
                                    <div class="fs-3 fw-bold text-white text-center" id="summaryNotYetDone">0</div>
                                </div>
                            </div>
                        </div>
                    </div>
                <div>
                    <div class="flex items-center justify-between mt-2">
                        <!-- Left side -->
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                data-bs-target="#addIndicatorModal">
                                <i class="fa fa-plus me-1"></i> Add PAP
                            </button>

                            <form 
    method="GET" 
    action="{{ url()->current() }}" 
    class="d-flex align-items-center gap-2 flex-wrap" 
    id="papSearchForm"
    role="search"
>
    {{-- Preserve important filters --}}
    @if(request()->filled('year'))
        <input type="hidden" name="year" value="{{ $year ?? now()->year }}">
    @endif
    @if(request()->filled('office_id'))
        <input type="hidden" name="office_id" value="{{ request('office_id') }}">
    @endif

    <div class="position-relative flex-grow-1" style="min-width: 320px; max-width: 480px;">
        <input
            type="search"
            name="search"
            id="papSearchInput"
            class="form-control form-control pe-5 ps-4 shadow-sm"
            placeholder="Search…"
            value="{{ old('search', $search ?? '') }}"
            autocomplete="off"
            aria-label="Search programs, projects and activities"
            required
        >
        <!-- Search icon inside input (very common pattern) -->
        <span class="position-absolute top-50 end-0 translate-middle-y pe-3 text-muted">
            <i class="bi bi-search"></i>
        </span>
    </div>

</form>
                        </div>

                        <!-- Right side -->
                        <div class="flex items-center gap-2 ">
                            <button onclick="toggleTargetColumns()" class="btn btn-danger btn-sm" id="targetBtn">
                                <i class="fa fa-plus me-1"></i> Targets
                            </button>
                            <button onclick="toggleMonthInputs()" class="btn btn-outline-secondary btn-sm" id="monthBtn" disabled>
                                <i class="fa fa-calendar-days me-1"></i> Months
                            </button>
                            <button onclick="toggleAccompColumns()" class="btn btn-success btn-sm" id="accompBtn">
                                <i class="fa fa-plus me-1"></i> Accomplishments
                            </button>
                            <button onclick="toggleRemarksColumn()" class="btn btn-warning btn-sm" id="remarksBtn">
                                <i class="fa fa-plus me-1"></i> Remarks
                            </button>
                            <button onclick="saveAllSectionEntries()" class="btn btn-primary btn-sm" id="saveAllBtn">
                                <i class="fa fa-floppy-disk me-1"></i> Save
                            </button>
                        </div>
                    </div>


                    <div class="table-container">
                        <table class="text-sm" id="performanceTable">

                            <thead>
                                <tr class="text-md bg-gradient-to-r from-primary to-primarydark text-white">
                                    <th class="px-4 py-3" style="min-width:300px">Programs/Activities/Projects (P/A/Ps)
                                    </th>
                                    <th class="px-4 py-3" style="min-width:240px">Performance Indicators</th>
                                    <th class="px-4 py-3" style="min-width:160px">Type of Indicator</th>
                                    <th class="px-4 py-3" style="min-width:180px">Office / Unit</th>
                                    <!-- month headers added dynamically -->
                                </tr>
                                <tr class="group-row" id="groupHeaders"></tr>
                            </thead>

                            <tbody class="text-gray-800">
                                @foreach($programs as $program)
                                    @php
                                        $programCoreKey = strtolower(trim((string) ($program->title ?? ''))) . '|' . strtolower(trim((string) ($program->program ?? ''))) . '|' . strtolower(trim((string) ($program->project ?? '')));
                                        $hasIndicatorData = isset($indicators[$program->id]) && $indicators[$program->id]->count() > 0;
                                    @endphp
                                    <tr class="program-header group" data-program-id="{{ $program->id }}" data-core-key="{{ $programCoreKey }}"
                                        onclick='toggleRowsByCoreKey(@json($programCoreKey))'>
                                        <td class="px-6 py-4" colspan="4">
                                            <div class="flex items-center justify-between">
                                                <span>
                                                    <strong>{{ $program->title }}</strong>
                                                    @if($program->program)
                                                        <span class="text-gray-600 font-normal text-sm ml-3">
                                                            • {{ $program->program }}
                                                        </span>
                                                    @endif
                                                    @if($program->project)
                                                        <div class="text-sm text-gray-700 font-medium mt-1">
                                                            Project: {{ $program->project }}
                                                        </div>
                                                    @endif
                                                </span>
                                                <span class="flex items-center">
                                                    @if($hasIndicatorData)
                                                        <i class="fa-solid fa-circle-check text-success me-2"
                                                            title="Indicator data available"></i>
                                                    @else
                                                        <i class="fa-solid fa-circle-xmark text-danger me-2"
                                                            title="No indicator data yet"></i>
                                                    @endif
                                                    <form method="POST" action="{{ route('admin.engp.pap.destroy', $program) }}"
                                                        class="me-2 delete-program-form" id="deleteProgramForm-{{ $program->id }}"
                                                        onsubmit="event.stopPropagation();">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button" class="btn btn-sm text-danger py-0 px-1 border-0 bg-transparent"
                                                            title="Delete PAP"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#deleteProgramConfirmModal"
                                                            data-delete-form-id="deleteProgramForm-{{ $program->id }}"
                                                            onclick="event.stopPropagation();">
                                                            <i class="fa-solid fa-trash"></i>
                                                        </button>
                                                    </form>
                                                    <i id="icon-{{ $program->id }}"
                                                        class="fa-solid fa-chevron-down program-toggle-icon transition-transform group-hover:text-indigo-600"></i>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @if($hasIndicatorData)
                                        @php
                                            $indicatorCount = $indicators[$program->id]->count();
                                        @endphp
                                        @foreach($indicators[$program->id] as $indicator)
                                            @php
                                                $indicatorSyncKey = $programCoreKey
                                                    . '|' . strtolower(trim((string) ($indicator->name ?? '')))
                                                    . '|' . strtolower(trim((string) ($indicator->indicator_type ?? '')));
                                                $officeIds = collect($indicator->office_id ?? [])
                                                    ->map(fn($id) => (int) $id)
                                                    ->filter()
                                                    ->values()
                                                    ->all();

                                                $selectedParentGroups = collect($offices ?? [])
                                                    ->map(function ($parent) use ($officeIds) {
                                                        $parentId = (int) ($parent->id ?? 0);
                                                        $parentSelected = in_array($parentId, $officeIds, true);

                                                        $children = collect($parent->children ?? []);
                                                        $selectedChildren = $children->filter(
                                                            fn($child) => in_array((int) ($child->id ?? 0), $officeIds, true)
                                                        )->values();

                                                        if (!$parentSelected && $selectedChildren->isEmpty()) {
                                                            return null;
                                                        }

                                                        $childrenForDisplay = $selectedChildren;

                                                        return [
                                                            'id' => $parentId,
                                                            'name' => (string) ($parent->name ?? ''),
                                                            'selected_parent' => $parentSelected,
                                                            'children' => $childrenForDisplay->map(fn($child) => [
                                                                'id' => (int) ($child->id ?? 0),
                                                                'name' => (string) ($child->name ?? ''),
                                                            ])->filter(fn($child) => $child['id'] > 0)->values(),
                                                        ];
                                                    })
                                                    ->filter()
                                                    ->values();

                                                $inputOffices = $selectedParentGroups
                                                    ->flatMap(function ($group) {
                                                        $selectedParent = (bool) ($group['selected_parent'] ?? false);

                                                        $children = collect($group['children'] ?? [])->map(fn($child) => [
                                                            'id' => (int) ($child['id'] ?? 0),
                                                            'name' => (string) ($child['name'] ?? ''),
                                                            'is_parent' => false,
                                                        ]);

                                                        $parentCollection = $selectedParent ? collect([
                                                            [
                                                                'id' => (int) ($group['id'] ?? 0),
                                                                'name' => (string) ($group['name'] ?? ''),
                                                                'is_parent' => true,
                                                            ]
                                                        ]) : collect();

                                                        return $parentCollection->merge($children);
                                                    })
                                                    ->filter(fn($office) => !empty($office['id']))
                                                    ->unique('id')
                                                    ->values();

                                                $groupSizes = $selectedParentGroups
                                                    ->map(function ($group) {
                                                        $selectedParent = (bool) ($group['selected_parent'] ?? false);
                                                        $childrenCount = collect($group['children'] ?? [])->count();
                                                        return ($selectedParent ? 1 : 0) + $childrenCount;
                                                    })
                                                    ->values();

                                                $groupPenroFlags = $selectedParentGroups
                                                    ->map(function ($group) {
                                                        $groupName = (string) ($group['name'] ?? '');
                                                        return preg_match('/\bPENRO\b/i', $groupName) === 1 ? 1 : 0;
                                                    })
                                                    ->values();

                                                $groupBreakIndices = [];
                                                $runningTotal = 0;
                                                foreach ($groupSizes as $index => $size) {
                                                    $runningTotal += (int) $size;
                                                    if ($index < ($groupSizes->count() - 1)) {
                                                        $groupBreakIndices[] = $runningTotal - 1;
                                                    }
                                                }
                                            @endphp
                                            <tr class="data-row @if($loop->first) first-indicator-row @endif"
                                                data-row-id="{{ $program->id }}" data-indicator-id="{{ $indicator->id }}"
                                                data-core-key="{{ $programCoreKey }}"
                                                data-sync-key="{{ $indicatorSyncKey }}"
                                                data-indicator-type="{{ $indicator->indicator_type ?? '' }}"
                                                data-office-ids="{{ implode(',', $officeIds) }}"
                                                data-office-names="{{ $selectedParentGroups->pluck('name')->map(fn($name) => str_replace('|', '/', $name))->implode('|') }}"
                                                data-input-office-ids="{{ $inputOffices->pluck('id')->implode(',') }}"
                                                data-input-office-names="{{ $inputOffices->pluck('name')->map(fn($name) => str_replace('|', '/', $name))->implode('|') }}"
                                                data-input-break-indices="{{ implode(',', $groupBreakIndices) }}"
                                                data-input-group-penro-flags="{{ $groupPenroFlags->implode(',') }}"
                                                id="content-{{ $program->id }}-{{ $loop->index }}" style="display:none;">
                                                @if($loop->first)
                                                    <td class="px-4 py-3 pl-5 text-primary fw-medium" rowspan="{{ $indicatorCount }}">
                                                        {{ $program->activities }}<br>
                                                        <span class="ms-4 small">{{ $program->subactivities }}</span>
                                                    </td>
                                                @endif
                                                <td class="px-4 py-3">
                                                    {{ $indicator->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 text-capitalize">
                                                    {{ $indicator->indicator_type ?? 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 small text-center">
                                                    @if($inputOffices->isNotEmpty())
                                                        <div class="office-lines">
                                                            <div class="office-line car-office-line">CAR</div>
                                                            @foreach($selectedParentGroups as $group)
                                                                @php
                                                                    $parentNameRaw = (string) ($group['name'] ?? '');
                                                                    $parentSubtotalLabel = preg_replace('/\b(PENRO|CENRO|TOTAL)\b/i', '', $parentNameRaw);
                                                                    $parentSubtotalLabel = trim(preg_replace('/\s+/', ' ', (string) $parentSubtotalLabel));
                                                                    $isPenroParent = preg_match('/\bPENRO\b/i', $parentNameRaw) === 1;
                                                                @endphp
                                                                @if($isPenroParent && (($group['selected_parent'] ?? false) || collect($group['children'] ?? [])->isNotEmpty()))
                                                                    <div class="office-line group-total-office-line">{{ $parentSubtotalLabel !== '' ? $parentSubtotalLabel : $parentNameRaw }}</div>
                                                                @endif
                                                                @if($group['selected_parent'] ?? false)
                                                                    <div class="office-line fw-bold">{{ $group['name'] }}</div>
                                                                @endif
                                                                @foreach($group['children'] ?? [] as $child)
                                                                    <div class="office-line">{{ $child['name'] }}</div>
                                                                @endforeach
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="office-lines">
                                                            <div class="office-line car-office-line">CAR</div>
                                                            <div class="office-line">N/A</div>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="data-row first-indicator-row" data-row-id="{{ $program->id }}"
                                            data-core-key="{{ $programCoreKey }}"
                                            data-sync-key="{{ $programCoreKey }}|no-indicator|"
                                            data-indicator-type="" data-office-ids="" data-office-names=""
                                            data-input-office-ids="" data-input-office-names="" data-input-break-indices="" data-input-group-penro-flags=""
                                            id="content-{{ $program->id }}-0" style="display:none;">
                                            <td class="px-4 py-3 pl-5 text-primary fw-medium">
                                                {{ $program->activities }}<br>
                                                <span class="ms-4 small">{{ $program->subactivities }}</span>
                                            </td>
                                            <td class="px-4 py-3">
                                                No performance indicator set
                                            </td>
                                            <td class="px-4 py-3 small">
                                                N/A
                                            </td>
                                            <td class="px-4 py-3">
                                                N/A
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach

                                <!-- Add more rows here as needed -->
                            </tbody>
                        </table>
                    </div>
                </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        const PERIODS = [
            { label: "JAN", type: "month" },
            { label: "FEB", type: "month" },
            { label: "MAR", type: "month" },
            { label: "Q1", type: "quarter" },
            { label: "APR", type: "month" },
            { label: "MAY", type: "month" },
            { label: "JUN", type: "month" },
            { label: "Q2", type: "quarter" },
            { label: "JUL", type: "month" },
            { label: "AUG", type: "month" },
            { label: "SEP", type: "month" },
            { label: "Q3", type: "quarter" },
            { label: "OCT", type: "month" },
            { label: "NOV", type: "month" },
            { label: "DEC", type: "month" },
            { label: "Q4", type: "quarter" },
            { label: "ANNUAL", type: "annual" }
        ];

        const COL_COUNT = PERIODS.length; // 17

        let targetsVisible = false;
        let accompVisible = false;
        let remarksVisible = false;
        let monthInputsVisible = false;
        let totalsListenerRegistered = false;

        const currentYear = Number(@json($year ?? now()->year));
        const currentOfficeId = Number(@json($office_id ?? 1));
        const targetStoreUrl = @json(route('admin.engp.targets.store'));
        const accompStoreUrl = @json(route('admin.engp.accomplishments.store'));
        const existingTargetsByIndicator = @json($targets ?? []);
        const existingAccompByIndicator = @json($accomplishments ?? []);

        const PERIOD_KEYS = [
            "jan", "feb", "mar", "q1",
            "apr", "may", "jun", "q2",
            "jul", "aug", "sep", "q3",
            "oct", "nov", "dec", "q4",
            "annual_total"
        ];

        let saveSuccessAlertTimeout = null;
        let saveErrorAlertTimeout = null;

        function showTopRightSuccessAlert(message = 'Data saved successfully.', options = {}) {
            const {
                reload = false,
                duration = 1800,
            } = options;

            const saveSuccessAlertWrapper = document.getElementById('saveSuccessAlertWrapper');
            const saveSuccessAlert = document.getElementById('saveSuccessAlert');
            const saveSuccessMessage = document.getElementById('saveSuccessMessage');

            if (!saveSuccessAlertWrapper || !saveSuccessAlert) {
                console.warn(message);
                if (reload) location.reload();
                return;
            }

            if (saveSuccessMessage) {
                saveSuccessMessage.textContent = message;
            }

            saveSuccessAlertWrapper.classList.remove('d-none');

            if (saveSuccessAlertTimeout) {
                clearTimeout(saveSuccessAlertTimeout);
            }

            const closeButton = saveSuccessAlert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.onclick = function () {
                    if (saveSuccessAlertTimeout) {
                        clearTimeout(saveSuccessAlertTimeout);
                    }
                    saveSuccessAlertWrapper.classList.add('d-none');
                    if (reload) location.reload();
                };
            }

            saveSuccessAlertTimeout = setTimeout(() => {
                saveSuccessAlertWrapper.classList.add('d-none');
                if (reload) location.reload();
            }, duration);
        }

        function showTopRightErrorAlert(message = 'An error occurred.', options = {}) {
            const {
                duration = 2200,
            } = options;

            const saveErrorAlertWrapper = document.getElementById('saveErrorAlertWrapper');
            const saveErrorAlert = document.getElementById('saveErrorAlert');
            const saveErrorMessage = document.getElementById('saveErrorMessage');

            if (!saveErrorAlertWrapper || !saveErrorAlert) {
                console.warn(message);
                return;
            }

            if (saveErrorMessage) {
                saveErrorMessage.textContent = message;
            }

            saveErrorAlertWrapper.classList.remove('d-none');

            if (saveErrorAlertTimeout) {
                clearTimeout(saveErrorAlertTimeout);
            }

            const closeButton = saveErrorAlert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.onclick = function () {
                    if (saveErrorAlertTimeout) {
                        clearTimeout(saveErrorAlertTimeout);
                    }
                    saveErrorAlertWrapper.classList.add('d-none');
                };
            }

            saveErrorAlertTimeout = setTimeout(() => {
                saveErrorAlertWrapper.classList.add('d-none');
            }, duration);
        }

        const MONTH_COLS = [0, 1, 2, 4, 5, 6, 8, 9, 10, 12, 13, 14];

        function buildMonthlyMapFromStored(sourceData) {
            const result = new Map();

            Object.entries(sourceData || {}).forEach(([indicatorId, offices]) => {
                Object.entries(offices || {}).forEach(([officeId, officeData]) => {
                    MONTH_COLS.forEach(colIndex => {
                        const monthKey = PERIOD_KEYS[colIndex];
                        if (!monthKey) return;

                        const value = Number(officeData?.[monthKey] ?? 0);
                        const safeValue = Number.isFinite(value) ? value : 0;
                        const key = `${indicatorId}|${officeId}|${monthKey}`;

                        result.set(key, safeValue);
                    });
                });
            });

            return result;
        }

        function applyMonthlyMapFromInputs(sectionType, targetMap) {
            const selector = `.month-box[data-section="${sectionType}"]`;

            Array.from(document.querySelectorAll(selector)).forEach(input => {
                if (input.dataset.carTotal === '1') return;
                if (input.dataset.groupTotal === '1') return;

                const colIndex = Number(input.dataset.col);
                if (!MONTH_COLS.includes(colIndex)) return;

                const monthKey = PERIOD_KEYS[colIndex];
                const officeId = String(input.dataset.officeId || '').trim();
                const row = input.closest('tr[data-indicator-id]');
                const indicatorId = String(row?.dataset?.indicatorId || '').trim();

                if (!indicatorId || !officeId || !monthKey) return;

                const value = Number(input.value);
                const safeValue = Number.isFinite(value) ? value : 0;
                const key = `${indicatorId}|${officeId}|${monthKey}`;

                targetMap.set(key, safeValue);
            });
        }

        function formatSummaryNumber(value) {
            return new Intl.NumberFormat().format(Number(value || 0));
        }

        function refreshSummaryCards() {
            const targetMap = buildMonthlyMapFromStored(existingTargetsByIndicator);
            const accompMap = buildMonthlyMapFromStored(existingAccompByIndicator);

            applyMonthlyMapFromInputs('target', targetMap);
            applyMonthlyMapFromInputs('accomp', accompMap);

            let targetTotal = 0;
            let accompTotal = 0;
            let pendingTotal = 0;

            targetMap.forEach((targetValue, key) => {
                const safeTarget = Number.isFinite(targetValue) ? targetValue : 0;
                const accompValue = Number(accompMap.get(key) ?? 0);
                const safeAccomp = Number.isFinite(accompValue) ? accompValue : 0;

                targetTotal += safeTarget;
                accompTotal += Math.min(safeAccomp, safeTarget);
                pendingTotal += Math.max(safeTarget - safeAccomp, 0);
            });

            const summaryTargetTotal = document.getElementById('summaryTargetTotal');
            const summaryAccompTotal = document.getElementById('summaryAccompTotal');
            const summaryNotYetDone = document.getElementById('summaryNotYetDone');

            if (summaryTargetTotal) summaryTargetTotal.textContent = formatSummaryNumber(targetTotal);
            if (summaryAccompTotal) summaryAccompTotal.textContent = formatSummaryNumber(accompTotal);
            if (summaryNotYetDone) summaryNotYetDone.textContent = formatSummaryNumber(pendingTotal);
        }

        function applyMonthInputVisibility() {
            document.querySelectorAll('th[data-period-type="month"]').forEach(cell => {
                cell.style.display = monthInputsVisible ? '' : 'none';
            });

            document.querySelectorAll('td[data-period-type="month"]').forEach(cell => {
                cell.style.display = monthInputsVisible ? '' : 'none';
            });

            refreshGroupHeaderColspans();
        }

        function refreshGroupHeaderColspans() {
            const groupRow = document.getElementById('groupHeaders');
            if (!groupRow) return;

            ['target', 'accomp', 'remarks'].forEach(sectionType => {
                const groupCell = groupRow.querySelector(`.group-${sectionType}`);
                if (!groupCell) return;

                const visibleCount = Array.from(
                    document.querySelectorAll(`thead tr:not(.group-row) th[data-dynamic-section="${sectionType}"]`)
                ).filter(cell => cell.style.display !== 'none').length;

                groupCell.colSpan = Math.max(visibleCount, 1);
            });
        }

        function refreshMonthButtonState() {
            const monthBtn = document.getElementById('monthBtn');
            if (!monthBtn) return;

            const canToggleMonths = targetsVisible || accompVisible;
            if (!canToggleMonths) {
                monthInputsVisible = false;
            }

            monthBtn.disabled = !canToggleMonths;
            monthBtn.innerHTML = monthInputsVisible
                ? '<i class="fa fa-eye-slash me-1"></i> Hide Months'
                : '<i class="fa fa-calendar-days me-1"></i> Show Months';

            applyMonthInputVisibility();
        }

        function toggleMonthInputs() {
            if (!targetsVisible && !accompVisible) return;
            monthInputsVisible = !monthInputsVisible;
            refreshMonthButtonState();
        }

        function toggleTargetColumns() {
            const table = document.getElementById("performanceTable");
            const headerRow = table.querySelector("thead tr:not(.group-row)");
            const groupRow = document.getElementById("groupHeaders");
            const hadVisibleSection = targetsVisible || accompVisible;

            if (!targetsVisible) {
                targetsVisible = true;
                if (!hadVisibleSection) {
                    monthInputsVisible = false;
                }
                document.getElementById("targetBtn").innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Targets';
                document.getElementById("targetBtn").classList.replace("btn-primary", "btn-outline-primary");

                addColumns(headerRow, groupRow, "Targets", "target");
                addInputCells("target");
                refreshMonthButtonState();
                refreshSummaryCards();
            } else {
                targetsVisible = false;
                document.getElementById("targetBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Targets';
                document.getElementById("targetBtn").classList.replace("btn-outline-primary", "btn-primary");

                removeSectionColumns(groupRow, headerRow, 'target');
                refreshMonthButtonState();
                refreshSummaryCards();
            }
        }

        function toggleAccompColumns() {
            const table = document.getElementById("performanceTable");
            const headerRow = table.querySelector("thead tr:not(.group-row)");
            const groupRow = document.getElementById("groupHeaders");
            const hadVisibleSection = targetsVisible || accompVisible;

            if (!accompVisible) {
                accompVisible = true;
                if (!hadVisibleSection) {
                    monthInputsVisible = false;
                }
                document.getElementById("accompBtn").innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Accomplishments';
                document.getElementById("accompBtn").classList.replace("btn-success", "btn-outline-success");

                addColumns(headerRow, groupRow, "Accomplishments", "accomp");
                addInputCells("accomp");
                refreshMonthButtonState();
                refreshSummaryCards();
            } else {
                accompVisible = false;
                document.getElementById("accompBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Accomplishments';
                document.getElementById("accompBtn").classList.replace("btn-outline-success", "btn-success");

                removeSectionColumns(groupRow, headerRow, 'accomp');
                refreshMonthButtonState();
                refreshSummaryCards();
            }
        }

        function toggleRemarksColumn() {
            const table = document.getElementById('performanceTable');
            const headerRow = table.querySelector('thead tr:not(.group-row)');
            const groupRow = document.getElementById('groupHeaders');

            if (!remarksVisible) {
                remarksVisible = true;
                document.getElementById('remarksBtn').innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Remarks';
                document.getElementById('remarksBtn').classList.replace('btn-warning', 'btn-outline-warning');

                addRemarksColumn(headerRow, groupRow);
                addRemarksCells();
                refreshSummaryCards();
            } else {
                remarksVisible = false;
                document.getElementById('remarksBtn').innerHTML = '<i class="fa fa-plus me-1"></i> Remarks';
                document.getElementById('remarksBtn').classList.replace('btn-outline-warning', 'btn-warning');

                removeSectionColumns(groupRow, headerRow, 'remarks');
                refreshSummaryCards();
            }
        }

        function addColumns(mainHeader, groupHeader, title, type) {
            if (groupHeader.children.length === 0) {
                for (let i = 0; i < 4; i++) {
                    const emptyTh = document.createElement("th");
                    groupHeader.appendChild(emptyTh);
                }
            }

            const thGroup = document.createElement("th");
            thGroup.colSpan = COL_COUNT;
            thGroup.className = `group-header group-${type}`;
            thGroup.textContent = title;
            const remarksGroup = groupHeader.querySelector('.group-remarks');
            if (remarksGroup) {
                groupHeader.insertBefore(thGroup, remarksGroup);
            } else {
                groupHeader.appendChild(thGroup);
            }

            PERIODS.forEach(p => {
                const th = document.createElement("th");
                th.classList.add("month-header", "text-center");
                th.classList.add(`dynamic-header-${type}`);
                th.dataset.dynamicSection = type;
                th.dataset.periodType = p.type;
                if (p.type === "quarter") th.classList.add("quarter");
                if (p.type === "annual") th.classList.add("annual");

                let label = p.label;
                if (p.type === "quarter") label += '<div class="tiny-period">Quarter</div>';
                if (p.type === "annual") label += '<div class="tiny-period">Total</div>';
                th.innerHTML = label;

                if (type === "accomp" && p.type === "month") {
                    th.classList.add("accomp-month");
                }

                const remarksHeader = mainHeader.querySelector('th[data-dynamic-section="remarks"]');
                if (remarksHeader) {
                    mainHeader.insertBefore(th, remarksHeader);
                } else {
                    mainHeader.appendChild(th);
                }
            });
        }

        function addRemarksColumn(mainHeader, groupHeader) {
            if (mainHeader.querySelector('th[data-dynamic-section="remarks"]')) {
                return;
            }

            if (groupHeader.children.length === 0) {
                for (let i = 0; i < 4; i++) {
                    const emptyTh = document.createElement('th');
                    groupHeader.appendChild(emptyTh);
                }
            }

            const groupCell = document.createElement('th');
            groupCell.colSpan = 1;
            groupCell.className = 'group-header group-remarks';
            groupCell.textContent = '';
            groupHeader.appendChild(groupCell);

            const th = document.createElement('th');
            th.className = 'month-header remarks-header text-center';
            th.dataset.dynamicSection = 'remarks';
            th.textContent = 'Remarks';
            mainHeader.appendChild(th);
        }

        function addRemarksCells() {
            document.querySelectorAll('tbody tr[data-row-id]').forEach(row => {
                const existingRemarksCell = row.querySelector('td[data-dynamic-section="remarks"]');
                if (existingRemarksCell) {
                    existingRemarksCell.remove();
                }

                const indicatorId = row.dataset.indicatorId;
                const existingRowDataByOffice = indicatorId ? (existingAccompByIndicator[String(indicatorId)] || {}) : {};
                const assignedOffices = getAssignedOfficesForRow(row);
                const groupBreakIndices = getInputBreakIndicesForRow(row);
                const groupPenroFlags = getInputGroupPenroFlagsForRow(row);

                const td = document.createElement('td');
                td.classList.add('p-1');
                td.dataset.dynamicSection = 'remarks';

                const wrapper = document.createElement('div');
                wrapper.className = 'office-lines';

                const officeEntries = assignedOffices.length > 0
                    ? assignedOffices
                    : [{ id: currentOfficeId || null, name: 'Office' }];

                const groupRanges = [];
                let rangeStart = 0;
                const sortedBreaks = [...groupBreakIndices]
                    .map(index => Number(index))
                    .filter(index => Number.isInteger(index) && index >= 0)
                    .sort((left, right) => left - right);

                sortedBreaks.forEach(breakIndex => {
                    if (breakIndex >= rangeStart && breakIndex < officeEntries.length) {
                        groupRanges.push({ start: rangeStart, end: breakIndex });
                        rangeStart = breakIndex + 1;
                    }
                });

                if (rangeStart < officeEntries.length) {
                    groupRanges.push({ start: rangeStart, end: officeEntries.length - 1 });
                }

                const groupStartToIndex = new Map();
                groupRanges.forEach((range, rangeIndex) => {
                    groupStartToIndex.set(range.start, rangeIndex);
                });

                const carSpacerLine = document.createElement('div');
                carSpacerLine.className = 'input-line';
                const carSpacer = document.createElement('textarea');
                carSpacer.className = 'remarks-box remarks-spacer';
                carSpacer.rows = 1;
                carSpacer.tabIndex = -1;
                carSpacer.readOnly = true;
                carSpacerLine.appendChild(carSpacer);
                wrapper.appendChild(carSpacerLine);

                officeEntries.forEach((office, officeIndex) => {
                    const currentGroupIndex = groupStartToIndex.get(officeIndex);
                    if (currentGroupIndex !== undefined) {
                        if (Boolean(groupPenroFlags[currentGroupIndex])) {
                            const groupSpacerLine = document.createElement('div');
                            groupSpacerLine.className = 'input-line';

                            const groupSpacer = document.createElement('textarea');
                            groupSpacer.className = 'remarks-box remarks-spacer';
                            groupSpacer.rows = 1;
                            groupSpacer.tabIndex = -1;
                            groupSpacer.readOnly = true;

                            groupSpacerLine.appendChild(groupSpacer);
                            wrapper.appendChild(groupSpacerLine);
                        }
                    }

                    const officeId = Number(office?.id || 0) || null;
                    const officeData = officeId ? existingRowDataByOffice[String(officeId)] : null;

                    const input = document.createElement('textarea');
                    input.className = 'remarks-box';
                    input.placeholder = 'Add comment';
                    input.rows = 1;
                    input.value = String(officeData?.remarks || '');
                    input.dataset.section = 'remarks';
                    input.dataset.officeId = officeId ? String(officeId) : '';

                    const inputLine = document.createElement('div');
                    inputLine.className = 'input-line';
                    inputLine.appendChild(input);
                    wrapper.appendChild(inputLine);
                });

                td.appendChild(wrapper);
                row.appendChild(td);
            });

            refreshGroupHeaderColspans();
        }

        function addInputCells(sectionType) {
            document.querySelectorAll("tbody tr[data-row-id]").forEach(row => {
                const indicatorId = row.dataset.indicatorId;
                const sourceData = sectionType === 'target'
                    ? existingTargetsByIndicator
                    : existingAccompByIndicator;
                const existingRowDataByOffice = indicatorId ? (sourceData[String(indicatorId)] || {}) : {};
                const indicatorType = getIndicatorTypeForRow(row);
                const assignedOffices = getAssignedOfficesForRow(row);
                const groupBreakIndices = getInputBreakIndicesForRow(row);
                const groupPenroFlags = getInputGroupPenroFlagsForRow(row);

                PERIODS.forEach((period, idx) => {
                    const td = document.createElement("td");
                    td.classList.add("p-1", "text-center");
                    td.classList.add(`dynamic-cell-${sectionType}`);
                    td.dataset.dynamicSection = sectionType;
                    td.dataset.periodType = period.type;

                    const wrapper = document.createElement("div");
                    wrapper.className = "office-lines";

                    const officeEntries = assignedOffices.length > 0
                        ? assignedOffices
                        : [{ id: currentOfficeId || null, name: 'Office' }];

                    const carInput = document.createElement("input");
                    carInput.type = "number";
                    carInput.className = `month-box ${sectionType}-box car-total-box`;
                    carInput.style.width = "70px";
                    carInput.style.maxWidth = "150px";
                    carInput.value = "0";
                    carInput.readOnly = true;
                    carInput.dataset.section = sectionType;
                    carInput.dataset.col = idx;
                    carInput.dataset.officeId = 'car-total';
                    carInput.dataset.carTotal = '1';

                    const carInputLine = document.createElement('div');
                    carInputLine.className = 'input-line';
                    carInputLine.appendChild(carInput);
                    wrapper.appendChild(carInputLine);

                    const groupRanges = [];
                    let rangeStart = 0;
                    const sortedBreaks = [...groupBreakIndices]
                        .map(index => Number(index))
                        .filter(index => Number.isInteger(index) && index >= 0)
                        .sort((left, right) => left - right);

                    sortedBreaks.forEach(breakIndex => {
                        if (breakIndex >= rangeStart && breakIndex < officeEntries.length) {
                            groupRanges.push({ start: rangeStart, end: breakIndex });
                            rangeStart = breakIndex + 1;
                        }
                    });

                    if (rangeStart < officeEntries.length) {
                        groupRanges.push({ start: rangeStart, end: officeEntries.length - 1 });
                    }

                    const groupStartToIndex = new Map();
                    groupRanges.forEach((range, rangeIndex) => {
                        groupStartToIndex.set(range.start, rangeIndex);
                    });

                    officeEntries.forEach((office, officeIndex) => {
                        const currentGroupIndex = groupStartToIndex.get(officeIndex);
                        if (currentGroupIndex !== undefined) {
                            const currentRange = groupRanges[currentGroupIndex];
                            const shouldRenderGroupInput = Boolean(groupPenroFlags[currentGroupIndex]);
                            const groupOfficeIds = officeEntries
                                .slice(currentRange.start, currentRange.end + 1)
                                .map(item => String(item?.id || '').trim())
                                .filter(Boolean);

                            if (shouldRenderGroupInput) {
                                const groupInput = document.createElement("input");
                                groupInput.type = "number";
                                groupInput.className = `month-box ${sectionType}-box group-total-box`;
                                groupInput.style.width = "70px";
                                groupInput.style.maxWidth = "150px";
                                groupInput.value = "0";
                                groupInput.readOnly = true;
                                groupInput.dataset.section = sectionType;
                                groupInput.dataset.col = idx;
                                groupInput.dataset.groupTotal = '1';
                                groupInput.dataset.groupKey = `group-${currentGroupIndex}`;
                                groupInput.dataset.groupOfficeIds = groupOfficeIds.join(',');
                                groupInput.dataset.officeId = `group-total-${currentGroupIndex}`;

                                const groupInputLine = document.createElement('div');
                                groupInputLine.className = 'input-line';
                                groupInputLine.appendChild(groupInput);
                                wrapper.appendChild(groupInputLine);
                            }
                        }

                        const officeId = Number(office?.id || 0) || null;

                        const input = document.createElement("input");
                        input.type = "number";
                        input.className = `month-box ${sectionType}-box`;
                        input.style.width = "70px";
                        input.style.maxWidth = "150px";
                        input.value = "0";
                        input.min = "0";
                        input.step = "any";
                        input.dataset.section = sectionType;
                        input.dataset.col = idx;
                        input.dataset.officeId = officeId ? String(officeId) : '';

                        const periodKey = PERIOD_KEYS[idx] || null;
                        const officeData = officeId ? existingRowDataByOffice[String(officeId)] : null;
                        if (officeData && periodKey && Object.prototype.hasOwnProperty.call(officeData, periodKey)) {
                            input.value = officeData[periodKey] ?? 0;
                        }

                        if (period.type !== "month") {
                            if (indicatorType === 'semi-comulative') {
                                input.readOnly = period.type === 'annual';
                            } else {
                                input.readOnly = true;
                            }
                            td.classList.add(
                                period.type === "quarter"
                                    ? (sectionType === "target" ? "target-total" : "quarter-total")
                                    : (sectionType === "target" ? "annual-target" : "annual-total")
                            );
                        }

                        const inputLine = document.createElement('div');
                        inputLine.className = 'input-line';
                        inputLine.appendChild(input);
                        wrapper.appendChild(inputLine);
                    });

                    td.appendChild(wrapper);

                    const remarksCell = row.querySelector('td[data-dynamic-section="remarks"]');
                    if (remarksCell) {
                        row.insertBefore(td, remarksCell);
                    } else {
                        row.appendChild(td);
                    }
                });
            });

            if (!totalsListenerRegistered) {
                document.getElementById("performanceTable").addEventListener('input', updateTotals);
                totalsListenerRegistered = true;
            }

            recalculateSectionRows(sectionType);
            recalculateCarTotalsForSection(sectionType);
            applyMonthInputVisibility();
            refreshSummaryCards();
        }

        function recalculateSectionRows(sectionType) {
            document.querySelectorAll('tbody tr[data-row-id]').forEach(row => {
                const allInputs = row.querySelectorAll('.month-box');
                const officeIds = getOfficeIdsFromSectionInputs(allInputs, sectionType);
                const indicatorType = getIndicatorTypeForRow(row);

                officeIds.forEach(officeId => {
                    const monthInputs = Array.from(allInputs)
                        .filter(i => i.dataset.section === sectionType
                            && String(i.dataset.officeId || '') === String(officeId)
                            && PERIODS[Number(i.dataset.col)]?.type === 'month');

                    if (monthInputs.length === 12) {
                        updateSection(monthInputs, allInputs, sectionType, indicatorType, officeId);
                    }
                });
            });
        }

        function getAggregatePayloadForRow(row, sectionType) {
            const sectionInputs = Array.from(row.querySelectorAll(`.month-box[data-section="${sectionType}"]`));

            const car_totals = PERIOD_KEYS.reduce((acc, periodKey, index) => {
                const input = sectionInputs.find(candidate =>
                    candidate.dataset.carTotal === '1' && Number(candidate.dataset.col) === index
                );

                const value = Number(input?.value ?? 0);
                acc[periodKey] = Number.isFinite(value) ? value : 0;
                return acc;
            }, {});

            const group_totals = {};

            sectionInputs
                .filter(input => input.dataset.groupTotal === '1')
                .forEach(input => {
                    const groupKey = String(input.dataset.groupKey || '').trim();
                    const colIndex = Number(input.dataset.col);
                    const periodKey = PERIOD_KEYS[colIndex] || null;

                    if (!groupKey || !periodKey) return;

                    if (!group_totals[groupKey]) {
                        group_totals[groupKey] = {};
                    }

                    const value = Number(input.value);
                    group_totals[groupKey][periodKey] = Number.isFinite(value) ? value : 0;
                });

            return { car_totals, group_totals };
        }

        function getSectionPayloadForRow(row, sectionType) {
            const indicatorId = Number(row.dataset.indicatorId || 0);
            const programId = Number(row.dataset.rowId || 0);
            if (!indicatorId || !programId) return [];

            const aggregatePayload = getAggregatePayloadForRow(row, sectionType);

            const inputs = Array.from(row.querySelectorAll('.month-box'))
                .filter(i => i.dataset.section === sectionType
                    && i.dataset.carTotal !== '1'
                    && i.dataset.groupTotal !== '1');

            if (inputs.length === 0) return [];

            const officeIds = getOfficeIdsFromSectionInputs(inputs, sectionType);
            if (officeIds.length === 0) return [];

            return officeIds.map(officeId => {
                const officeInputs = inputs
                    .filter(i => String(i.dataset.officeId || '') === String(officeId))
                    .sort((left, right) => Number(left.dataset.col) - Number(right.dataset.col));

                if (officeInputs.length !== PERIOD_KEYS.length) return null;

                const entry = {
                    program_id: programId,
                    indicator_id: indicatorId,
                    office_id: Number(officeId) || (currentOfficeId || null),
                    year: currentYear,
                };

                PERIOD_KEYS.forEach((key, index) => {
                    const value = Number(officeInputs[index]?.value);
                    entry[key] = Number.isFinite(value) ? value : 0;
                });

                entry.car_totals = aggregatePayload.car_totals;
                entry.group_totals = aggregatePayload.group_totals;

                return entry;
            }).filter(Boolean);
        }

        function collectSectionEntries(sectionType) {
            return Array.from(document.querySelectorAll('tbody tr[data-row-id]'))
                .flatMap(row => getSectionPayloadForRow(row, sectionType) || [])
                .filter(Boolean);
        }

        function getRemarksByOfficeForRow(row) {
            const remarksInputs = Array.from(row.querySelectorAll('.remarks-box'));
            return remarksInputs.reduce((acc, input) => {
                const officeId = String(input.dataset.officeId || '').trim();
                if (!officeId) return acc;
                acc[officeId] = String(input.value || '').trim();
                return acc;
            }, {});
        }

        function collectAccomplishmentEntries() {
            return Array.from(document.querySelectorAll('tbody tr[data-row-id]'))
                .flatMap(row => {
                    const indicatorId = Number(row.dataset.indicatorId || 0);
                    const programId = Number(row.dataset.rowId || 0);
                    if (!indicatorId || !programId) return [];

                    const aggregatePayload = getAggregatePayloadForRow(row, 'accomp');

                    const officeIds = new Set();
                    getAssignedOfficeIdsForRow(row).forEach(id => officeIds.add(String(id)));

                    const accompInputs = Array.from(row.querySelectorAll('.month-box[data-section="accomp"]'));
                    accompInputs.forEach(input => {
                        if (input.dataset.carTotal === '1') return;
                        if (input.dataset.groupTotal === '1') return;
                        const officeId = String(input.dataset.officeId || '').trim();
                        if (officeId) officeIds.add(officeId);
                    });

                    const remarksByOffice = getRemarksByOfficeForRow(row);
                    Object.keys(remarksByOffice).forEach(officeId => officeIds.add(String(officeId)));

                    if (officeIds.size === 0) {
                        officeIds.add(String(currentOfficeId || '0'));
                    }

                    const existingByOffice = existingAccompByIndicator[String(indicatorId)] || {};

                    return Array.from(officeIds).map(officeId => {
                        const entry = {
                            program_id: programId,
                            indicator_id: indicatorId,
                            office_id: Number(officeId) || (currentOfficeId || null),
                            year: currentYear,
                        };

                        const officeInputs = accompInputs
                            .filter(input => String(input.dataset.officeId || '') === String(officeId));

                        const existingOfficeData = existingByOffice[String(officeId)] || {};

                        PERIOD_KEYS.forEach((key, index) => {
                            const matchingInput = officeInputs.find(input => Number(input.dataset.col) === index);
                            if (matchingInput) {
                                const value = Number(matchingInput.value);
                                entry[key] = Number.isFinite(value) ? value : 0;
                                return;
                            }

                            const existingValue = Number(existingOfficeData[key] ?? 0);
                            entry[key] = Number.isFinite(existingValue) ? existingValue : 0;
                        });

                        entry.car_totals = aggregatePayload.car_totals;
                        entry.group_totals = aggregatePayload.group_totals;
                        entry.remarks = String(remarksByOffice[String(officeId)] ?? existingOfficeData.remarks ?? '').trim();

                        return entry;
                    });
                })
                .filter(Boolean);
        }

        function getAssignedOfficeIdsForRow(row) {
            const raw = String(row?.dataset?.inputOfficeIds || row?.dataset?.officeIds || '').trim();
            if (!raw) return [];

            return raw
                .split(',')
                .map(value => Number(String(value).trim()))
                .filter(value => Number.isInteger(value) && value > 0);
        }

        function getAssignedOfficesForRow(row) {
            const ids = getAssignedOfficeIdsForRow(row);
            const names = String(row?.dataset?.inputOfficeNames || row?.dataset?.officeNames || '')
                .split('|')
                .map(value => value.trim())
                .filter(Boolean);

            return ids.map((id, index) => ({
                id,
                name: names[index] || `Office ${id}`,
            }));
        }

        function getOfficeIdsFromSectionInputs(inputs, sectionType) {
            const ids = Array.from(inputs)
                .filter(input => input.dataset.section === sectionType
                    && input.dataset.carTotal !== '1'
                    && input.dataset.groupTotal !== '1'
                    && String(input.dataset.officeId || '').trim() !== '')
                .map(input => String(input.dataset.officeId));

            return Array.from(new Set(ids));
        }

        function getInputBreakIndicesForRow(row) {
            const raw = String(row?.dataset?.inputBreakIndices || '').trim();
            if (!raw) return [];

            return raw
                .split(',')
                .map(value => Number(String(value).trim()))
                .filter(value => Number.isInteger(value) && value >= 0);
        }

            function getInputGroupPenroFlagsForRow(row) {
                const raw = String(row?.dataset?.inputGroupPenroFlags || '').trim();
                if (!raw) return [];

                return raw
                .split(',')
                .map(value => Number(String(value).trim()) === 1);
            }

        async function saveSectionEntries(sectionType, options = {}) {
            const {
                requireVisible = true,
                showAlerts = true,
            } = options;

            const isTarget = sectionType === 'target';
            const shouldBeVisible = isTarget ? targetsVisible : (accompVisible || remarksVisible);

            if (requireVisible && !shouldBeVisible) {
                if (showAlerts) {
                    showTopRightErrorAlert(`Please open ${isTarget ? 'Targets' : 'Accomplishments'} first before saving.`);
                }
                return { success: false, skipped: true, message: 'Section is not visible.' };
            }

            const entries = isTarget
                ? collectSectionEntries('target')
                : collectAccomplishmentEntries();
            if (entries.length === 0) {
                if (showAlerts) {
                    showTopRightErrorAlert('No indicator rows available to save.');
                }
                return { success: false, skipped: true, message: 'No rows to save.' };
            }

            const url = isTarget ? targetStoreUrl : accompStoreUrl;
            const tokenInput = document.querySelector('input[name="_token"]');
            const token = tokenInput ? tokenInput.value : '';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ entries }),
                });

                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || `Failed to save ${sectionType}.`);
                }

                if (showAlerts) {
                    showTopRightSuccessAlert('Data saved successfully.');
                }

                return {
                    success: true,
                    message: 'Data saved successfully.',
                };
            } catch (error) {
                console.error(`${sectionType} save error:`, error);
                if (showAlerts) {
                    showTopRightErrorAlert(`Error saving ${isTarget ? 'targets' : 'accomplishments'}. Please try again.`);
                }

                return {
                    success: false,
                    message: error?.message || `Error saving ${isTarget ? 'targets' : 'accomplishments'}.`,
                };
            }
        }

        async function saveAllSectionEntries() {
            const saveTarget = targetsVisible;
            const saveAccomp = accompVisible || remarksVisible;

            if (!saveTarget && !saveAccomp) {
                showTopRightErrorAlert('Please open Targets, Accomplishments, and/or Remarks first before saving.');
                return;
            }

            const results = [];

            if (saveTarget) {
                results.push(await saveSectionEntries('target', { requireVisible: false, showAlerts: false }));
            }

            if (saveAccomp) {
                results.push(await saveSectionEntries('accomp', { requireVisible: false, showAlerts: false }));
            }

            const failed = results.filter(result => !result.success);
            if (failed.length > 0) {
                showTopRightErrorAlert('Some entries failed to save. Please try again.');
                return;
            }

            showTopRightSuccessAlert('Data saved successfully.');
        }

        function removeSectionColumns(groupRow, mainHeader, sectionType) {
            const groupCell = groupRow.querySelector(`.group-${sectionType}`);
            if (!groupCell) {
                refreshSummaryCards();
                return;
            }

            mainHeader
                .querySelectorAll(`th[data-dynamic-section="${sectionType}"]`)
                .forEach(cell => cell.remove());

            document.querySelectorAll(`tbody tr[data-row-id] td[data-dynamic-section="${sectionType}"]`)
                .forEach(cell => cell.remove());

            groupCell.remove();

            const hasDynamicGroups = groupRow.querySelectorAll('.group-header').length > 0;
            if (!hasDynamicGroups) {
                while (groupRow.firstChild) {
                    groupRow.removeChild(groupRow.firstChild);
                }
            }

            refreshSummaryCards();
        }

        function updateTotals(e) {
            const input = e.target;
            if (!input.classList.contains('month-box')) return;

            const row = input.closest('tr');
            if (!row) return;

            const indicatorType = getIndicatorTypeForRow(row);
            if (input.readOnly) return;

            const allInputs = row.querySelectorAll('.month-box');
            const officeId = String(input.dataset.officeId || '');
            const sectionType = String(input.dataset.section || '');

            if (!officeId || !sectionType) return;

            const syncedRows = syncMonthValueAcrossCoreRows(input);

            // Group by section
            const targetInputs = Array.from(allInputs).filter(i => i.dataset.section === 'target' && String(i.dataset.officeId || '') === officeId && PERIODS[Number(i.dataset.col)]?.type === 'month');
            const accompInputs = Array.from(allInputs).filter(i => i.dataset.section === 'accomp' && String(i.dataset.officeId || '') === officeId && PERIODS[Number(i.dataset.col)]?.type === 'month');

            // Update targets if present
            if (targetInputs.length === 12) {
                updateSection(targetInputs, allInputs, 'target', indicatorType, officeId);
            }

            // Update accomplishments if present
            if (accompInputs.length === 12) {
                updateSection(accompInputs, allInputs, 'accomp', indicatorType, officeId);
            }

            syncedRows.forEach(syncedRow => {
                recalculateRowOfficeSection(syncedRow, sectionType, officeId);
                recalculateCarTotalsForRow(syncedRow, sectionType);
            });

            recalculateCarTotalsForRow(row, sectionType);

            refreshSummaryCards();
        }

        function recalculateRowOfficeSection(row, sectionType, officeId) {
            if (!row || !sectionType || !officeId) return;

            const indicatorType = getIndicatorTypeForRow(row);
            const allInputs = row.querySelectorAll('.month-box');
            const monthInputs = Array.from(allInputs).filter(i =>
                i.dataset.section === sectionType
                && String(i.dataset.officeId || '') === String(officeId)
                && PERIODS[Number(i.dataset.col)]?.type === 'month'
            );

            if (monthInputs.length === 12) {
                updateSection(monthInputs, allInputs, sectionType, indicatorType, officeId);
            }
        }

        function recalculateCarTotalsForSection(sectionType) {
            document.querySelectorAll('tbody tr[data-row-id]').forEach(row => {
                recalculateCarTotalsForRow(row, sectionType);
            });
        }

        function recalculateCarTotalsForRow(row, sectionType) {
            if (!row || !sectionType) return;

            const sectionInputs = Array.from(row.querySelectorAll(`.month-box[data-section="${sectionType}"]`));
            if (sectionInputs.length === 0) return;

            const indicatorType = getIndicatorTypeForRow(row);
            const useAddition = indicatorType === 'comulative';

            const sourceInputs = sectionInputs.filter(input => input.dataset.carTotal !== '1' && input.dataset.groupTotal !== '1');
            const groupInputs = sectionInputs.filter(input => input.dataset.groupTotal === '1');
            const carInputs = sectionInputs.filter(input => input.dataset.carTotal === '1');
            if (carInputs.length === 0 && groupInputs.length === 0) return;

            groupInputs.forEach(groupInput => {
                const colIndex = Number(groupInput.dataset.col);
                const groupOfficeIdSet = new Set(
                    String(groupInput.dataset.groupOfficeIds || '')
                        .split(',')
                        .map(value => value.trim())
                        .filter(Boolean)
                );

                const values = sourceInputs
                    .filter(input => Number(input.dataset.col) === colIndex && groupOfficeIdSet.has(String(input.dataset.officeId || '')))
                    .map(input => {
                        const value = Number(input.value);
                        return Number.isFinite(value) ? value : 0;
                    });

                const groupTotal = useAddition
                    ? values.reduce((sum, value) => sum + value, 0)
                    : (values.length > 0 ? Math.max(...values) : 0);

                groupInput.value = groupTotal;
            });

            for (let colIndex = 0; colIndex < COL_COUNT; colIndex++) {
                const values = sourceInputs
                    .filter(input => Number(input.dataset.col) === colIndex)
                    .map(input => {
                        const value = Number(input.value);
                        return Number.isFinite(value) ? value : 0;
                    });

                const colTotal = useAddition
                    ? values.reduce((sum, value) => sum + value, 0)
                    : (values.length > 0 ? Math.max(...values) : 0);

                const carColInput = carInputs.find(input => Number(input.dataset.col) === colIndex);
                if (carColInput) {
                    carColInput.value = colTotal;
                }
            }
        }

        function syncMonthValueAcrossCoreRows(sourceInput) {
            const sourceRow = sourceInput.closest('tr[data-core-key]');
            const coreKey = String(sourceRow?.dataset?.coreKey || '').trim();
            if (!coreKey) return [];

            const sectionType = String(sourceInput.dataset.section || '');
            const col = String(sourceInput.dataset.col || '');
            const officeId = String(sourceInput.dataset.officeId || '');
            if (!sectionType || col === '' || !officeId) return [];

            const touchedRows = new Set();

            document.querySelectorAll('.month-box').forEach(candidate => {
                if (candidate === sourceInput) return;
                if (String(candidate.dataset.section || '') !== sectionType) return;
                if (String(candidate.dataset.col || '') !== col) return;
                if (String(candidate.dataset.officeId || '') !== officeId) return;

                const candidateRow = candidate.closest('tr[data-core-key]');
                if (!candidateRow) return;
                if (String(candidateRow.dataset.coreKey || '').trim() !== coreKey) return;

                candidate.value = sourceInput.value;
                touchedRows.add(candidateRow);
            });

            return Array.from(touchedRows);
        }

        function getIndicatorTypeForRow(row) {
            const rawType = String(row?.dataset?.indicatorType || '').trim().toLowerCase();
            if (rawType === 'semi-comulative') return 'semi-comulative';
            if (rawType === 'non-comulative') return 'non-comulative';
            return 'comulative';
        }

        function getSectionColInput(allInputs, section, colIndex, officeId = null) {
            return Array.from(allInputs).find(i =>
                i.dataset.section === section
                && Number(i.dataset.col) === colIndex
                && String(i.dataset.officeId || '') === String(officeId || '')
            ) || null;
        }

        function updateSection(monthInputs, allInputs, section, indicatorType = 'comulative', officeId = null) {
            const values = monthInputs.map(inp => Number(inp.value) || 0);

            let q1 = 0;
            let q2 = 0;
            let q3 = 0;
            let q4 = 0;
            let annual = 0;

            if (indicatorType === 'non-comulative') {
                q1 = Math.max(values[0] || 0, values[1] || 0, values[2] || 0);
                q2 = Math.max(values[3] || 0, values[4] || 0, values[5] || 0);
                q3 = Math.max(values[6] || 0, values[7] || 0, values[8] || 0);
                q4 = Math.max(values[9] || 0, values[10] || 0, values[11] || 0);
                annual = Math.max(q1, q2, q3, q4);
            } else if (indicatorType === 'semi-comulative') {
                q1 = Number(getSectionColInput(allInputs, section, 3, officeId)?.value) || 0;
                q2 = Number(getSectionColInput(allInputs, section, 7, officeId)?.value) || 0;
                q3 = Number(getSectionColInput(allInputs, section, 11, officeId)?.value) || 0;
                q4 = Number(getSectionColInput(allInputs, section, 15, officeId)?.value) || 0;
                annual = q1 + q2 + q3 + q4;
            } else {
                q1 = values[0] + values[1] + values[2];
                q2 = values[3] + values[4] + values[5];
                q3 = values[6] + values[7] + values[8];
                q4 = values[9] + values[10] + values[11];
                annual = q1 + q2 + q3 + q4;
            }

            const q1Input = getSectionColInput(allInputs, section, 3, officeId);
            const q2Input = getSectionColInput(allInputs, section, 7, officeId);
            const q3Input = getSectionColInput(allInputs, section, 11, officeId);
            const q4Input = getSectionColInput(allInputs, section, 15, officeId);
            const annualInput = getSectionColInput(allInputs, section, 16, officeId);

            if (q1Input?.readOnly) q1Input.value = q1;
            if (q2Input?.readOnly) q2Input.value = q2;
            if (q3Input?.readOnly) q3Input.value = q3;
            if (q4Input?.readOnly) q4Input.value = q4;
            if (annualInput) annualInput.value = annual;
        }

        let currentProgramIndicators = [];

        function setOfficeCheckboxes(officeIdsArray = []) {
            const officeIds = new Set((officeIdsArray || []).map(id => String(id)));
            document.querySelectorAll('.office-checkbox').forEach(checkbox => {
                checkbox.checked = officeIds.has(String(checkbox.value));
            });
        }

        function findIndicatorByName(programId, indicatorName) {
            if (!programId || !indicatorName) return null;
            const normalized = String(indicatorName).trim().toLowerCase();
            if (!normalized) return null;

            const source = indicatorsData[programId] || [];
            return source.find(item => String(item.name || '').trim().toLowerCase() === normalized) || null;
        }

        function normalizePapField(value) {
            return String(value || '').replace(/\s+/g, ' ').trim().toLowerCase();
        }

        function getNormalizedPapCoreKey(item) {
            return [
                normalizePapField(item?.title),
                normalizePapField(item?.program),
                normalizePapField(item?.project),
            ].join('|');
        }

        function getUniquePapCoreEntries() {
            const source = Array.isArray(papPrefillData) ? papPrefillData : [];
            const seen = new Set();

            return source.filter(item => {
                const key = getNormalizedPapCoreKey(item);
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            });
        }

        function buildPapDropdownValue(item) {
            const title = String(item?.title || '').trim();
            const program = String(item?.program || '').trim();
            const project = String(item?.project || '').trim();

            return `${title} | ${program} | ${project}`;
        }

        function parsePapDropdownValue(rawValue) {
            const raw = String(rawValue || '').trim();
            if (!raw.includes('|')) return null;

            const parts = raw.split(/\s*\|\s*/);
            if (parts.length < 3) return null;

            return {
                title: parts[0]?.trim() || '',
                program: parts[1]?.trim() || '',
                project: parts.slice(2).join(' | ').trim(),
            };
        }

        function normalizePapCoreInputsFromDropdownValue() {
            const titleInput = document.getElementById('pap_title');
            const programInput = document.getElementById('pap_program');
            const projectInput = document.getElementById('pap_project');

            if (!titleInput) return null;

            const parsed = parsePapDropdownValue(titleInput.value);
            if (!parsed) return null;

            titleInput.value = parsed.title;
            if (programInput) {
                programInput.value = parsed.program;
            }
            if (projectInput) {
                projectInput.value = parsed.project;
            }

            return parsed;
        }

        function findPapByCoreFields({ titleValue, programValue, projectValue }) {
            const normalizedTitle = normalizePapField(titleValue);
            const normalizedProgram = normalizePapField(programValue);
            const normalizedProject = normalizePapField(projectValue);
            if (!normalizedTitle) return null;

            const byTitle = (papPrefillData || []).filter(item =>
                normalizePapField(item?.title) === normalizedTitle
            );
            if (byTitle.length === 0) return null;

            const byTitleProgram = normalizedProgram
                ? byTitle.filter(item => normalizePapField(item?.program) === normalizedProgram)
                : byTitle;

            const byCore = normalizedProject
                ? byTitleProgram.filter(item => normalizePapField(item?.project) === normalizedProject)
                : byTitleProgram;

            return byCore[0] || byTitleProgram[0] || byTitle[0] || null;
        }

        function applyPapFieldsFromTitleSelection() {
            const titleInput = document.getElementById('pap_title');
            const programInput = document.getElementById('pap_program');
            const projectInput = document.getElementById('pap_project');

            normalizePapCoreInputsFromDropdownValue();

            const matchedPap = findPapByCoreFields({
                titleValue: titleInput?.value,
                programValue: programInput?.value,
                projectValue: projectInput?.value,
            });
            if (!matchedPap) return null;

            const papActivitiesInput = document.getElementById('pap_activities');
            const papSubactivitiesInput = document.getElementById('pap_subactivities');

            if (programInput) programInput.value = String(matchedPap.program || '');
            if (projectInput) projectInput.value = String(matchedPap.project || '');
            if (papActivitiesInput) papActivitiesInput.value = String(matchedPap.activities || '');
            if (papSubactivitiesInput) papSubactivitiesInput.value = String(matchedPap.subactivities || '');

            return matchedPap;
        }

        function populatePapTitleDropdown() {
            const titleOptions = document.getElementById('pap_title_options');
            if (!titleOptions) return;

            const uniqueEntries = getUniquePapCoreEntries();
            titleOptions.innerHTML = '';

            uniqueEntries.forEach(item => {
                const option = document.createElement('option');
                option.value = buildPapDropdownValue(item);
                titleOptions.appendChild(option);
            });
        }

        function findMatchingPapFromModal() {
            const title = normalizePapField(document.getElementById('pap_title')?.value);
            const program = normalizePapField(document.getElementById('pap_program')?.value);
            const project = normalizePapField(document.getElementById('pap_project')?.value);
            const activities = normalizePapField(document.getElementById('pap_activities')?.value);
            const subactivities = normalizePapField(document.getElementById('pap_subactivities')?.value);

            if (!title && !program && !project && !activities && !subactivities) {
                return null;
            }

            return (papPrefillData || []).find(item =>
                normalizePapField(item?.title) === title
                && normalizePapField(item?.program) === program
                && normalizePapField(item?.project) === project
                && normalizePapField(item?.activities) === activities
                && normalizePapField(item?.subactivities) === subactivities
            ) || null;
        }

        function applyModalPrefillFromExistingPap() {
            const matchedPap = findMatchingPapFromModal();
            const indicatorIdInput = document.getElementById('indicator_id');
            const indicatorNameInput = document.getElementById('modal_indicator_name');
            const indicatorTypeInput = document.getElementById('modal_indicator_type');
            const indicatorTypeToggle = document.getElementById('use_indicator_type');

            if (!matchedPap || !Array.isArray(matchedPap.indicators) || matchedPap.indicators.length === 0) {
                if (indicatorIdInput) {
                    indicatorIdInput.value = '';
                }
                if (indicatorNameInput) {
                    indicatorNameInput.value = '';
                }
                if (indicatorTypeInput) {
                    indicatorTypeInput.value = '';
                }
                if (indicatorTypeToggle) {
                    indicatorTypeToggle.checked = false;
                }
                toggleIndicatorTypeDropdown();
                setOfficeCheckboxes([]);
                return;
            }

            const selectedIndicator = matchedPap.indicators.find(i => String(i?.name || '').trim() !== '') || matchedPap.indicators[0];
            if (!selectedIndicator) return;

            if (indicatorNameInput) {
                indicatorNameInput.value = String(selectedIndicator.name || '').trim();
            }

            if (indicatorTypeInput) {
                indicatorTypeInput.value = String(selectedIndicator.indicator_type || '').trim();
            }

            if (indicatorTypeToggle) {
                indicatorTypeToggle.checked = Boolean(indicatorTypeInput?.value);
            }

            toggleIndicatorTypeDropdown();

            if (indicatorIdInput) {
                indicatorIdInput.value = String(selectedIndicator.id || '').trim();
            }

            setOfficeCheckboxes(selectedIndicator.office_ids || []);
        }

        function toggleIndicatorTypeDropdown() {
            const indicatorTypeToggle = document.getElementById('use_indicator_type');
            const indicatorTypeWrapper = document.getElementById('indicator_type_wrapper');
            const indicatorTypeInput = document.getElementById('modal_indicator_type');

            const enabled = Boolean(indicatorTypeToggle?.checked);
            if (indicatorTypeWrapper) {
                indicatorTypeWrapper.style.display = enabled ? '' : 'none';
            }

            if (!enabled && indicatorTypeInput) {
                indicatorTypeInput.value = '';
            }
        }

        // Handle Add Indicator Form Submission
        document.addEventListener('DOMContentLoaded', function () {
            populatePapTitleDropdown();

            // Toggle all rows that share the same title/program/project core key.
            window.toggleRowsByCoreKey = function (coreKey) {
                if (!coreKey) return;

                const rows = Array.from(document.querySelectorAll('tbody tr.data-row'))
                    .filter(row => String(row.dataset.coreKey || '') === String(coreKey));
                if (!rows.length) return;

                const shouldShow = Array.from(rows).some(row => row.style.display === 'none');
                rows.forEach(row => {
                    row.style.display = shouldShow ? 'table-row' : 'none';
                });

                const headers = Array.from(document.querySelectorAll('tbody tr.program-header'))
                    .filter(row => String(row.dataset.coreKey || '') === String(coreKey))
                    .map(row => row.querySelector('.program-toggle-icon'))
                    .filter(Boolean);
                headers.forEach(icon => {
                    icon.classList.toggle('rotate-180', shouldShow);
                });
            };

            const papFieldIds = ['pap_title', 'pap_program', 'pap_project', 'pap_activities', 'pap_subactivities'];
            let modalPrefillTimer = null;

            const titleField = document.getElementById('pap_title');
            if (titleField) {
                titleField.addEventListener('input', function () {
                    clearTimeout(modalPrefillTimer);
                    modalPrefillTimer = setTimeout(() => {
                        applyPapFieldsFromTitleSelection();
                        applyModalPrefillFromExistingPap();
                    }, 180);
                });

                titleField.addEventListener('change', function () {
                    clearTimeout(modalPrefillTimer);
                    applyPapFieldsFromTitleSelection();
                    applyModalPrefillFromExistingPap();
                });
            }

            papFieldIds.forEach(fieldId => {
                if (fieldId === 'pap_title') return;

                const field = document.getElementById(fieldId);
                if (!field) return;

                field.addEventListener('input', function () {
                    clearTimeout(modalPrefillTimer);
                    modalPrefillTimer = setTimeout(() => {
                        applyModalPrefillFromExistingPap();
                    }, 220);
                });

                field.addEventListener('change', function () {
                    clearTimeout(modalPrefillTimer);
                    applyModalPrefillFromExistingPap();
                });
            });

            const indicatorTypeToggle = document.getElementById('use_indicator_type');
            if (indicatorTypeToggle) {
                indicatorTypeToggle.addEventListener('change', toggleIndicatorTypeDropdown);
            }

            toggleIndicatorTypeDropdown();
        });
    </script>

    <script>
        // Make indicators data available to JavaScript
        const indicatorsData = {!! json_encode($indicatorsForJs ?? []) !!};
        const papPrefillData = {!! json_encode($papPrefillData ?? []) !!};
    </script>

    <div id="saveSuccessAlertWrapper" class="position-fixed top-20 end-0 p-3 d-none"
        style="z-index: 1080; max-width: 420px;">
        <div class="alert alert-success alert-dismissible fade show shadow" role="alert" id="saveSuccessAlert">
            <strong>Success!</strong>
            <span id="saveSuccessMessage">Data saved successfully.</span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
    </div>

    <div id="saveErrorAlertWrapper" class="position-fixed top-20 end-0 p-3 d-none"
        style="z-index: 1080; max-width: 420px;">
        <div class="alert alert-danger alert-dismissible fade show shadow" role="alert" id="saveErrorAlert">
            <strong>Error!</strong>
            <span id="saveErrorMessage">An error occurred.</span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
    </div>

    <div class="modal fade" id="deleteProgramConfirmModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title">Confirm Delete</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    are you sure you want to delete?
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" id="confirmDeleteProgramBtn">Delete</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Indicator Modal -->
    <div class="modal fade" id="addIndicatorModal" tabindex="-1" aria-labelledby="addIndicatorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addIndicatorModalLabel">Add Performance Indicator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="addIndicatorForm" method="POST" action="{{ route('admin.engp.indicators.store') }}"
                    data-update-route-template="{{ route('admin.engp.indicators.update', ':id') }}"
                    data-delete-route-template="{{ route('admin.engp.indicators.destroy', ':id') }}">
                    @csrf
                    <input type="hidden" id="indicator_id" name="indicator_id" value="">

                    <div class="modal-body">
                        <h4
                            class="font-extrabold text-2xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent pb-1 border-b-2 border-blue-200 inline-block">
                            P/A/P
                        </h4>
                        <div class="row g-3 mb-2">
                            <div class="col-12 col-md-6">
                                <label for="pap_title" class="form-label fw-bold">Title</label>
                                <input type="text" id="pap_title" class="form-control form-control-lg" list="pap_title_options" required>
                                <datalist id="pap_title_options">
                                    @foreach(($papTitles ?? []) as $existingTitle)
                                        <option value="{{ $existingTitle }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pap_program" class="form-label fw-bold">Program</label>
                                <input type="text" id="pap_program" class="form-control form-control-lg">
                            </div>

                            <div class="col-12">
                                <label for="pap_project" class="form-label fw-bold">Project</label>
                                <input type="text" id="pap_project" class="form-control form-control-lg" list="pap_project_options">
                                <datalist id="pap_project_options">
                                    @foreach(($papProjects ?? []) as $existingProject)
                                        <option value="{{ $existingProject }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pap_activities" class="form-label fw-bold">Activity</label>
                                <input type="text" id="pap_activities" class="form-control form-control-lg" list="pap_activity_options">
                                <datalist id="pap_activity_options">
                                    @foreach(($papActivities ?? []) as $existingActivity)
                                        <option value="{{ $existingActivity }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pap_subactivities" class="form-label fw-bold">Sub-activity</label>
                                <input type="text" id="pap_subactivities" class="form-control form-control-lg" list="pap_subactivity_options">
                                <datalist id="pap_subactivity_options">
                                    @foreach(($papSubactivities ?? []) as $existingSubactivity)
                                        <option value="{{ $existingSubactivity }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                        </div>
                        <h4
                            class="font-extrabold text-2xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent pb-1 border-b-2 border-blue-200 inline-block">
                            Indicator
                        </h4>
                        <!-- Performance Indicator -->
                        <div class="mb-2">
                            <label for="modal_indicator_name" class="form-label fw-bold">Performance Indicator</label>
                            <textarea type="text" name="indicator_name" id="modal_indicator_name"
                                class="form-control form-control-lg" placeholder="Input the performance indicator"
                                required></textarea>
                            @error('indicator_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="use_indicator_type" style="border-color: #000;">
                            <label class="form-check-label fw-bold" for="use_indicator_type">Choose indicator type</label>
                        </div>

                        <div class="mb-2" id="indicator_type_wrapper" style="display: none;">
                            <label for="modal_indicator_type" class="form-label fw-bold">Type of Indicator</label>
                            <select name="indicator_type" id="modal_indicator_type" class="form-control form-control-lg">
                                <option value="">-- Select Type --</option>
                                <option value="non-comulative">non-comulative</option>
                                <option value="comulative">comulative</option>
                                <option value="semi-comulative">semi-comulative</option>
                            </select>
                            @error('indicator_type')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Office -->
                        <div>
                            <label class="form-label fw-bold">Office / Unit</label>

                            <div >
                                <div class="row row-cols-1 row-cols-md-3">
                                    @forelse($offices ?? [] as $parent)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input office-checkbox black-checkbox"
                                                    type="checkbox" value="{{ $parent->id }}" id="office_{{ $parent->id }}"
                                                    name="office_id[]">
                                                <label class="form-check-label" for="office_{{ $parent->id }}">
                                                    {{ $parent->name }}
                                                </label>
                                            </div>

                                            @foreach($parent->children ?? [] as $child)
                                                <div class="form-check">
                                                    <input class="form-check-input office-checkbox black-checkbox"
                                                        type="checkbox"
                                                        value="{{ $child->id }}"
                                                        id="office_{{ $child->id }}"
                                                        name="office_id[]">
                                                    <label class="form-check-label" for="office_{{ $child->id }}">
                                                        {{ $child->name }}
                                                    </label>
                                                </div>
                                            @endforeach
                                        </div>
                                    @empty
                                        <div class="col-12">
                                            <p class="text-muted mb-0">No offices available</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>

                            @error('office_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('addIndicatorForm')?.addEventListener('submit', async function (e) {
            e.preventDefault();

            const form = this;
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const actionUrl = form.getAttribute('action');
            const submitButton = form.querySelector('button[type="submit"]');
            const selectedOffices = Array.from(document.querySelectorAll('.office-checkbox:checked'))
                .map(checkbox => checkbox.value);

            const papTitle = document.getElementById('pap_title')?.value?.trim() || '';
            const papProgram = document.getElementById('pap_program')?.value?.trim() || '';
            const papProject = document.getElementById('pap_project')?.value?.trim() || '';
            const papActivities = document.getElementById('pap_activities')?.value?.trim() || '';
            const papSubactivities = document.getElementById('pap_subactivities')?.value?.trim() || '';
            const indicatorId = String(document.getElementById('indicator_id')?.value || '').trim();

            const indicatorName = document.getElementById('modal_indicator_name').value.trim();
            const indicatorTypeToggle = document.getElementById('use_indicator_type');
            const indicatorType = (indicatorTypeToggle?.checked ? document.getElementById('modal_indicator_type').value : '').trim();

            if (!papTitle) {
                showTopRightErrorAlert('Please input title.');
                return;
            }

            if (!indicatorName) {
                showTopRightErrorAlert('Please input a performance indicator.');
                return;
            }

            if (selectedOffices.length === 0) {
                showTopRightErrorAlert('Please select at least one office.');
                return;
            }

            if (submitButton) submitButton.disabled = true;

            try {
                const matchedPap = findMatchingPapFromModal();
                let programId = matchedPap?.id ? String(matchedPap.id) : '';

                if (!programId) {
                    const papFormData = new FormData();
                    papFormData.append('_token', token);
                    papFormData.append('title', papTitle);
                    papFormData.append('program', papProgram);
                    papFormData.append('project', papProject);
                    papFormData.append('activities', papActivities);
                    papFormData.append('subactivities', papSubactivities);

                    const papResponse = await fetch(@json(route('admin.engp.pap.store')), {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                        },
                        body: papFormData,
                    });

                    const papData = await papResponse.json();
                    if (!papResponse.ok || !papData?.pap?.id) {
                        const firstError = papData?.errors ? Object.values(papData.errors)[0]?.[0] : null;
                        throw new Error(firstError || papData?.message || 'Failed to save PAP.');
                    }

                    programId = String(papData.pap.id);
                }

                const formData = new FormData();
                formData.append('_token', token);
                formData.append('program_id', programId);
                formData.append('indicator_name', indicatorName);
                if (indicatorType) {
                    formData.append('indicator_type', indicatorType);
                }
                selectedOffices.forEach(officeId => formData.append('office_id[]', officeId));

                const updateRouteTemplate = form.dataset.updateRouteTemplate || '';
                const shouldUpdateExistingIndicator = Boolean(indicatorId && programId && matchedPap?.id && String(matchedPap.id) === String(programId));

                let indicatorResponse;
                if (shouldUpdateExistingIndicator && updateRouteTemplate) {
                    const updateUrl = updateRouteTemplate.replace(':id', indicatorId);
                    formData.append('_method', 'PATCH');

                    indicatorResponse = await fetch(updateUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                } else {
                    indicatorResponse = await fetch(actionUrl, {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                        },
                        body: formData
                    });
                }

                if (!indicatorResponse.ok) {
                    const text = await indicatorResponse.text();
                    throw new Error(`HTTP ${indicatorResponse.status}: ${text}`);
                }

                const indicatorData = await indicatorResponse.json();
                if (!indicatorData.success) {
                    throw new Error('Failed to save indicator.');
                }

                const modal = bootstrap.Modal.getInstance(document.getElementById('addIndicatorModal'));
                if (modal) modal.hide();
                form.reset();
                document.getElementById('indicator_id').value = '';
                document.querySelectorAll('.office-checkbox').forEach(cb => cb.checked = false);
                currentProgramIndicators = [];
                const successMessage = shouldUpdateExistingIndicator
                    ? 'Data updated successfully.'
                    : 'Data saved successfully.';

                showTopRightSuccessAlert(successMessage, { reload: true, duration: 1800 });
            } catch (error) {
                console.error('Save error:', error);
                showTopRightErrorAlert(error?.message || 'An error occurred while saving PAP and indicator.');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });

        const papSearchForm = document.getElementById('papSearchForm');
        const papSearchInput = document.getElementById('papSearchInput');

        if (papSearchForm && papSearchInput) {
            let searchDebounceTimer = null;

            const table = document.getElementById('performanceTable');
            const programHeaders = Array.from(
                table?.querySelectorAll('tr.program-header[data-program-id]') || []
            );

            const initialRowDisplay = new Map();
            programHeaders.forEach(header => {
                const programId = header.dataset.programId;
                const rows = table.querySelectorAll(`tr[id^="content-${programId}-"]`);
                rows.forEach(row => {
                    initialRowDisplay.set(row.id, row.style.display || '');
                });
            });

            const applyProgramSearch = () => {
                const query = (papSearchInput.value || '').trim().toLowerCase();

                programHeaders.forEach(header => {
                    const programId = header.dataset.programId;
                    const rows = Array.from(table.querySelectorAll(`tr[id^="content-${programId}-"]`));
                    const rowText = rows.map(row => row.innerText || '').join(' ');
                    const haystack = `${header.innerText || ''} ${rowText}`.toLowerCase();
                    const isMatch = query === '' || haystack.includes(query);

                    header.style.display = isMatch ? 'table-row' : 'none';

                    if (!isMatch) {
                        rows.forEach(row => {
                            row.style.display = 'none';
                        });
                        return;
                    }

                    if (query === '') {
                        rows.forEach(row => {
                            row.style.display = initialRowDisplay.get(row.id) ?? '';
                        });
                    } else {
                        rows.forEach(row => {
                            row.style.display = 'table-row';
                        });
                    }
                });
            };

            papSearchForm.addEventListener('submit', function (event) {
                event.preventDefault();
                applyProgramSearch();
            });

            papSearchInput.addEventListener('input', function () {
                clearTimeout(searchDebounceTimer);

                searchDebounceTimer = setTimeout(() => {
                    applyProgramSearch();
                }, 180);
            });

            papSearchInput.addEventListener('keydown', function (event) {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    clearTimeout(searchDebounceTimer);
                    applyProgramSearch();
                }
            });

            applyProgramSearch();


        document.addEventListener('DOMContentLoaded', function () {
            const deleteModalElement = document.getElementById('deleteProgramConfirmModal');
            const confirmDeleteBtn = document.getElementById('confirmDeleteProgramBtn');
            if (!deleteModalElement || !confirmDeleteBtn) return;

            let selectedDeleteFormId = '';

            deleteModalElement.addEventListener('show.bs.modal', function (event) {
                const trigger = event.relatedTarget;
                selectedDeleteFormId = trigger?.getAttribute('data-delete-form-id') || '';
            });

            confirmDeleteBtn.addEventListener('click', function () {
                if (!selectedDeleteFormId) return;

                const form = document.getElementById(selectedDeleteFormId);
                if (!form) return;

                form.submit();
            });
        });
            refreshSummaryCards();
        }
    </script>

</body>

</html>

