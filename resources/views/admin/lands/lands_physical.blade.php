<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (LANDS) - PMS</title>

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
    </style>
</head>

<body class="bg-light">

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-3">

            <div class="year-header">
                (LANDS) - Physical Performance
            </div>

            <div class="bg-white rounded shadow p-3">
                <!-- TABS -->
                <div class="flex items-center mt-4">
                    <div class="flex gap-6">
                        <a href="{{ route('lands') }}">
                            <button class="font-semibold text-blue-600 border-b-2 border-blue-600 pb-2">
                                Physical
                            </button>
                        </a>
                        <button class="text-gray-400 pb-2">
                            Financial
                        </button>
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
                            <button onclick="toggleAccompColumns()" class="btn btn-success btn-sm" id="accompBtn">
                                <i class="fa fa-plus me-1"></i> Accomplishments
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
                                        $hasIndicatorData = isset($indicators[$program->id]) && $indicators[$program->id]->count() > 0;
                                    @endphp
                                    <tr class="program-header group" data-program-id="{{ $program->id }}"
                                        onclick="toggleRow('content-{{ $program->id }}', 'icon-{{ $program->id }}')">
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
                                                $officeIds = collect($indicator->office_id ?? [])
                                                    ->map(fn($id) => (int) $id)
                                                    ->filter()
                                                    ->values()
                                                    ->all();

                                                $selectedParents = collect($offices ?? [])->filter(
                                                    fn($parent) => in_array((int) $parent->id, $officeIds, true)
                                                );

                                                $inputOffices = $selectedParents
                                                    ->flatMap(function ($parent) {
                                                        $children = collect($parent->children ?? [])->map(fn($child) => [
                                                            'id' => (int) $child->id,
                                                            'name' => (string) $child->name,
                                                            'is_parent' => false,
                                                        ]);

                                                        return collect([
                                                            [
                                                                'id' => (int) $parent->id,
                                                                'name' => (string) $parent->name,
                                                                'is_parent' => true,
                                                            ]
                                                        ])->merge($children);
                                                    })
                                                    ->filter(fn($office) => !empty($office['id']))
                                                    ->unique('id')
                                                    ->values();

                                                $groupSizes = $selectedParents
                                                    ->map(function ($parent) {
                                                        return 1 + collect($parent->children ?? [])->count();
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
                                                data-indicator-type="{{ $indicator->indicator_type ?? '' }}"
                                                data-office-ids="{{ implode(',', $officeIds) }}"
                                                data-office-names="{{ $selectedParents->pluck('name')->map(fn($name) => str_replace('|', '/', $name))->implode('|') }}"
                                                data-input-office-ids="{{ $inputOffices->pluck('id')->implode(',') }}"
                                                data-input-office-names="{{ $inputOffices->pluck('name')->map(fn($name) => str_replace('|', '/', $name))->implode('|') }}"
                                                data-input-break-indices="{{ implode(',', $groupBreakIndices) }}"
                                                id="content-{{ $program->id }}-{{ $loop->index }}" @if($loop->first)
                                                style="display:none;" @endif>
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
                                                            @foreach($inputOffices as $office)
                                                                <div
                                                                    class="office-line @if(!empty($office['is_parent'])) fw-bold @endif @if(in_array($loop->index, $groupBreakIndices, true)) mb-3 @endif">
                                                                    {{ $office['name'] }}</div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="office-lines">
                                                            <div class="office-line">N/A</div>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="data-row first-indicator-row" data-row-id="{{ $program->id }}"
                                            data-indicator-type="" data-office-ids="" data-office-names=""
                                            data-input-office-ids="" data-input-office-names="" data-input-break-indices=""
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
        let totalsListenerRegistered = false;

        const currentYear = Number(@json($year ?? now()->year));
        const currentOfficeId = Number(@json($office_id ?? 1));
        const targetStoreUrl = @json(route('admin.lands.targets.store'));
        const accompStoreUrl = @json(route('admin.lands.accomplishments.store'));
        const existingTargetsByIndicator = @json($targets ?? []);
        const existingAccompByIndicator = @json($accomplishments ?? []);

        const PERIOD_KEYS = [
            "jan", "feb", "mar", "q1",
            "apr", "may", "jun", "q2",
            "jul", "aug", "sep", "q3",
            "oct", "nov", "dec", "q4",
            "annual_total"
        ];

        function toggleTargetColumns() {
            const table = document.getElementById("performanceTable");
            const headerRow = table.querySelector("thead tr:not(.group-row)");
            const groupRow = document.getElementById("groupHeaders");

            if (!targetsVisible) {
                targetsVisible = true;
                document.getElementById("targetBtn").innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Targets';
                document.getElementById("targetBtn").classList.replace("btn-primary", "btn-outline-primary");

                addColumns(headerRow, groupRow, "Targets", "target");
                addInputCells("target");
            } else {
                targetsVisible = false;
                document.getElementById("targetBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Targets';
                document.getElementById("targetBtn").classList.replace("btn-outline-primary", "btn-primary");

                removeColumns(groupRow, headerRow);
            }
        }

        function toggleAccompColumns() {
            const table = document.getElementById("performanceTable");
            const headerRow = table.querySelector("thead tr:not(.group-row)");
            const groupRow = document.getElementById("groupHeaders");

            if (!accompVisible) {
                accompVisible = true;
                document.getElementById("accompBtn").innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Accomplishments';
                document.getElementById("accompBtn").classList.replace("btn-success", "btn-outline-success");

                addColumns(headerRow, groupRow, "Accomplishments", "accomp");
                addInputCells("accomp");
            } else {
                accompVisible = false;
                document.getElementById("accompBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Accomplishments';
                document.getElementById("accompBtn").classList.replace("btn-outline-success", "btn-success");

                removeColumns(groupRow, headerRow);
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
            groupHeader.appendChild(thGroup);

            PERIODS.forEach(p => {
                const th = document.createElement("th");
                th.classList.add("month-header", "text-center");
                if (p.type === "quarter") th.classList.add("quarter");
                if (p.type === "annual") th.classList.add("annual");

                let label = p.label;
                if (p.type === "quarter") label += '<div class="tiny-period">Quarter</div>';
                if (p.type === "annual") label += '<div class="tiny-period">Total</div>';
                th.innerHTML = label;

                if (type === "accomp" && p.type === "month") {
                    th.classList.add("accomp-month");
                }

                mainHeader.appendChild(th);
            });
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

                PERIODS.forEach((period, idx) => {
                    const td = document.createElement("td");
                    td.classList.add("p-1", "text-center");

                    const wrapper = document.createElement("div");
                    wrapper.className = "office-lines";

                    const officeEntries = assignedOffices.length > 0
                        ? assignedOffices
                        : [{ id: currentOfficeId || null, name: 'Office' }];

                    officeEntries.forEach((office, officeIndex) => {
                        const officeId = Number(office?.id || 0) || null;

                        const input = document.createElement("input");
                        input.type = "number";
                        input.className = `month-box ${sectionType}-box`;
                        input.style.maxWidth = "92px";
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
                        if (groupBreakIndices.includes(officeIndex)) {
                            inputLine.classList.add('mb-3');
                        }
                        inputLine.appendChild(input);
                        wrapper.appendChild(inputLine);
                    });

                    td.appendChild(wrapper);
                    row.appendChild(td);
                });
            });

            if (!totalsListenerRegistered) {
                document.getElementById("performanceTable").addEventListener('input', updateTotals);
                totalsListenerRegistered = true;
            }

            recalculateSectionRows(sectionType);
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

        function getSectionPayloadForRow(row, sectionType) {
            const indicatorId = Number(row.dataset.indicatorId || 0);
            const programId = Number(row.dataset.rowId || 0);
            if (!indicatorId || !programId) return [];

            const inputs = Array.from(row.querySelectorAll('.month-box'))
                .filter(i => i.dataset.section === sectionType);

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

                return entry;
            }).filter(Boolean);
        }

        function collectSectionEntries(sectionType) {
            return Array.from(document.querySelectorAll('tbody tr[data-row-id]'))
                .flatMap(row => getSectionPayloadForRow(row, sectionType) || [])
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
                .filter(input => input.dataset.section === sectionType && String(input.dataset.officeId || '').trim() !== '')
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

        async function saveSectionEntries(sectionType, options = {}) {
            const {
                requireVisible = true,
                showAlerts = true,
            } = options;

            const isTarget = sectionType === 'target';
            const shouldBeVisible = isTarget ? targetsVisible : accompVisible;

            if (requireVisible && !shouldBeVisible) {
                if (showAlerts) {
                    alert(`Please open ${isTarget ? 'Targets' : 'Accomplishments'} first before saving.`);
                }
                return { success: false, skipped: true, message: 'Section is not visible.' };
            }

            const entries = collectSectionEntries(sectionType);
            if (entries.length === 0) {
                if (showAlerts) {
                    alert('No indicator rows available to save.');
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
                    alert(data.message || `${isTarget ? 'Targets' : 'Accomplishments'} saved successfully.`);
                }

                return {
                    success: true,
                    message: data.message || `${isTarget ? 'Targets' : 'Accomplishments'} saved successfully.`,
                };
            } catch (error) {
                console.error(`${sectionType} save error:`, error);
                if (showAlerts) {
                    alert(`Error saving ${isTarget ? 'targets' : 'accomplishments'}. Please try again.`);
                }

                return {
                    success: false,
                    message: error?.message || `Error saving ${isTarget ? 'targets' : 'accomplishments'}.`,
                };
            }
        }

        async function saveAllSectionEntries() {
            const saveTarget = targetsVisible;
            const saveAccomp = accompVisible;

            if (!saveTarget && !saveAccomp) {
                alert('Please open Targets and/or Accomplishments first before saving.');
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
                alert('Some entries failed to save. Please try again.');
                return;
            }

            alert('Targets and Accomplishments saved successfully.');
        }

        function removeColumns(groupRow, mainHeader) {
            // Remove group title cells
            while (groupRow.children.length > 0) {
                groupRow.removeChild(groupRow.lastChild);
            }

            // Remove month columns from main header
            for (let i = 0; i < COL_COUNT; i++) {
                if (mainHeader.lastChild) mainHeader.removeChild(mainHeader.lastChild);
            }

            // Remove cells from data rows
            document.querySelectorAll("tbody tr[data-row-id]").forEach(row => {
                for (let i = 0; i < COL_COUNT; i++) {
                    if (row.lastChild) row.removeChild(row.lastChild);
                }
            });
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

            if (!officeId) return;

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
            document.querySelectorAll('.parent-office-checkbox').forEach(checkbox => {
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

        function findPapByTitle(titleValue) {
            const normalizedTitle = normalizePapField(titleValue);
            if (!normalizedTitle) return null;

            return (papPrefillData || []).find(item =>
                normalizePapField(item?.title) === normalizedTitle
            ) || null;
        }

        function applyPapFieldsFromTitleSelection() {
            const titleInput = document.getElementById('pap_title');
            const matchedPap = findPapByTitle(titleInput?.value);
            if (!matchedPap) return null;

            const papProgramInput = document.getElementById('pap_program');
            const papProjectInput = document.getElementById('pap_project');
            const papActivitiesInput = document.getElementById('pap_activities');
            const papSubactivitiesInput = document.getElementById('pap_subactivities');

            if (papProgramInput) papProgramInput.value = String(matchedPap.program || '');
            if (papProjectInput) papProjectInput.value = String(matchedPap.project || '');
            if (papActivitiesInput) papActivitiesInput.value = String(matchedPap.activities || '');
            if (papSubactivitiesInput) papSubactivitiesInput.value = String(matchedPap.subactivities || '');

            return matchedPap;
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
            // toggleRow function for program header collapse/expand
            window.toggleRow = function (contentId, iconId) {
                const table = document.getElementById("performanceTable");
                const programId = contentId.replace('content-', '');
                const rows = table.querySelectorAll(`tr[id^="content-${programId}-"]`);
                const icon = document.getElementById(iconId);

                rows.forEach(row => {
                    const isHidden = row.style.display === 'none';
                    row.style.display = isHidden ? 'table-row' : 'none';
                });

                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
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

                <form id="addIndicatorForm" method="POST" action="{{ route('admin.lands.indicators.store') }}"
                    data-update-route-template="{{ route('admin.lands.indicators.update', ':id') }}"
                    data-delete-route-template="{{ route('admin.lands.indicators.destroy', ':id') }}">
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
                                                <input class="form-check-input parent-office-checkbox black-checkbox"
                                                    type="checkbox" value="{{ $parent->id }}" id="office_{{ $parent->id }}"
                                                    data-parent-id="{{ $parent->id }}" name="office_id[]">
                                                <label class="form-check-label" for="office_{{ $parent->id }}">
                                                    {{ $parent->name }}
                                                </label>
                                            </div>
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
                        <button type="submit" class="btn btn-primary">Add Indicator</button>
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
            const selectedOffices = Array.from(document.querySelectorAll('.parent-office-checkbox:checked'))
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
                alert('Please input title.');
                return;
            }

            if (!indicatorName) {
                alert('Please input a performance indicator.');
                return;
            }

            if (selectedOffices.length === 0) {
                alert('Please select at least one office.');
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

                    const papResponse = await fetch(@json(route('admin.lands.pap.store')), {
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
                document.querySelectorAll('.parent-office-checkbox').forEach(cb => cb.checked = false);
                currentProgramIndicators = [];
                alert('PAP and indicator saved successfully.');
                setTimeout(() => {
                    location.reload();
                }, 500);
            } catch (error) {
                console.error('Save error:', error);
                alert(error?.message || 'An error occurred while saving PAP and indicator.');
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
        }
    </script>

</body>

</html>



