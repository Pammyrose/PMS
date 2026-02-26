<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (GASS) - PMS</title>

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
                (GASS) - Physical Performance
            </div>

            <div class="bg-white rounded shadow p-3">
                <!-- TABS -->
                <div class="flex items-center mt-4">
                    <div class="flex gap-6">
                        <a href="{{ route('gass_physical') }}">
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
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal"
                            data-bs-target="#addIndicatorModal">
                            <i class="fa fa-plus me-1"></i> Add Indicators
                        </button>

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
                                    <th class="px-4 py-3" style="min-width:180px">Office / Unit</th>
                                    <th class="px-4 py-3" style="min-width:160px">Type of Indicator</th>
                                    <!-- month headers added dynamically -->
                                </tr>
                                <tr class="group-row" id="groupHeaders"></tr>
                            </thead>

                            <tbody class="text-gray-800">
                                @foreach($programs as $program)
                                    @php
                                        $hasIndicatorData = isset($indicators[$program->id]) && $indicators[$program->id]->count() > 0;
                                    @endphp
                                                    <tr class="program-header group" data-program-id="{{ $program->id }}" onclick="toggleRow('content-{{ $program->id }}', 'icon-{{ $program->id }}')">
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
                                                        <i class="fa-solid fa-circle-check text-success me-2" title="Indicator data available"></i>
                                                    @else
                                                        <i class="fa-solid fa-circle-xmark text-danger me-2" title="No indicator data yet"></i>
                                                    @endif
                                                    <i id="icon-{{ $program->id }}" class="fa-solid fa-chevron-down program-toggle-icon transition-transform group-hover:text-indigo-600"></i>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                    @if($hasIndicatorData)
                                        @php
                                            $indicatorCount = $indicators[$program->id]->count();
                                        @endphp
                                        @foreach($indicators[$program->id] as $indicator)
                                            <tr class="data-row @if($loop->first) first-indicator-row @endif" data-row-id="{{ $program->id }}" data-indicator-id="{{ $indicator->id }}" data-indicator-type="{{ $indicator->indicator_type ?? '' }}" id="content-{{ $program->id }}-{{ $loop->index }}" @if($loop->first) style="display:none;" @endif>
                                                @if($loop->first)
                                                    <td class="px-4 py-3 pl-5 text-primary fw-medium" rowspan="{{ $indicatorCount }}">
                                                        {{ $program->activities }}<br>
                                                        <span class="ms-4 small">{{ $program->subactivities }}</span>
                                                    </td>
                                                @endif
                                                <td class="px-4 py-3">
                                                    {{ $indicator->name ?? 'N/A' }}
                                                </td>
                                                <td class="px-4 py-3 small">
                                                    @php
                                                        $officeIds = collect($indicator->office_id ?? [])
                                                            ->map(fn ($id) => (int) $id)
                                                            ->filter()
                                                            ->values()
                                                            ->all();

                                                        $selectedParents = collect($offices ?? [])->filter(
                                                            fn ($parent) => in_array((int) $parent->id, $officeIds, true)
                                                        );
                                                    @endphp

                                                    @if($selectedParents->isNotEmpty())
                                                        @foreach($selectedParents as $parent)
                                                            <strong>{{ $parent->name }}</strong><br>
                                                            @if($parent->children && $parent->children->count() > 0)
                                                                @foreach($parent->children as $child)
                                                                    <span class="ms-3">• {{ $child->name }}</span><br>
                                                                @endforeach
                                                            @endif
                                                            @if(!$loop->last)
                                                                <br>
                                                            @endif
                                                        @endforeach
                                                    @else
                                                        N/A
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3 text-capitalize">
                                                    {{ $indicator->indicator_type ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="data-row first-indicator-row" data-row-id="{{ $program->id }}" data-indicator-type="" id="content-{{ $program->id }}-0" style="display:none;">
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
        const targetStoreUrl = @json(route('admin.gass_physical.targets.store'));
        const accompStoreUrl = @json(route('admin.gass_physical.accomplishments.store'));
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
            // Ensure first 4 fixed columns are reserved in the group header
            if (groupHeader.children.length === 0) {
                for (let i = 0; i < 4; i++) {
                    const emptyTh = document.createElement("th");
                    groupHeader.appendChild(emptyTh);
                }
            }

            // Add group title (colspan = number of dynamic period columns)
            const thGroup = document.createElement("th");
            thGroup.colSpan = COL_COUNT;
            thGroup.className = `group-header group-${type}`;
            thGroup.textContent = title;
            groupHeader.appendChild(thGroup);

            // Add month/quarter/annual headers
            PERIODS.forEach(p => {
                const th = document.createElement("th");
                th.textContent = p.label;
                th.classList.add("month-header", "text-center");
                if (p.type === "quarter") th.classList.add("quarter");
                if (p.type === "annual") th.classList.add("annual");

                // ← This is the only added logic
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
                const existingRowData = indicatorId ? sourceData[String(indicatorId)] : null;
                const indicatorType = getIndicatorTypeForRow(row);

                PERIODS.forEach((period, idx) => {
                    const td = document.createElement("td");
                    td.classList.add("p-1", "text-center");

                    const input = document.createElement("input");
                    input.type = "number";
                    input.className = `month-box ${sectionType}-box`;
                    input.value = "0";
                    input.min = "0";
                    input.step = "any";
                    input.dataset.section = sectionType;
                    input.dataset.col = idx;

                    const periodKey = PERIOD_KEYS[idx] || null;
                    if (existingRowData && periodKey && Object.prototype.hasOwnProperty.call(existingRowData, periodKey)) {
                        input.value = existingRowData[periodKey] ?? 0;
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

                    td.appendChild(input);
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
                const monthInputs = Array.from(allInputs)
                    .filter(i => i.dataset.section === sectionType && PERIODS[Number(i.dataset.col)]?.type === 'month');

                if (monthInputs.length === 12) {
                    const indicatorType = getIndicatorTypeForRow(row);
                    updateSection(monthInputs, allInputs, sectionType, indicatorType);
                }
            });
        }

        function getSectionPayloadForRow(row, sectionType) {
            const indicatorId = Number(row.dataset.indicatorId || 0);
            const programId = Number(row.dataset.rowId || 0);
            if (!indicatorId || !programId) return null;

            const inputs = Array.from(row.querySelectorAll('.month-box'))
                .filter(i => i.dataset.section === sectionType);

            if (inputs.length !== PERIOD_KEYS.length) return null;

            const entry = {
                program_id: programId,
                indicator_id: indicatorId,
                office_id: currentOfficeId || null,
                year: currentYear,
            };

            PERIOD_KEYS.forEach((key, index) => {
                const value = Number(inputs[index]?.value);
                entry[key] = Number.isFinite(value) ? value : 0;
            });

            return entry;
        }

        function collectSectionEntries(sectionType) {
            return Array.from(document.querySelectorAll('tbody tr[data-row-id]'))
                .map(row => getSectionPayloadForRow(row, sectionType))
                .filter(Boolean);
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

            // Group by section
            const targetInputs = Array.from(allInputs).filter(i => i.dataset.section === 'target' && PERIODS[Number(i.dataset.col)]?.type === 'month');
            const accompInputs = Array.from(allInputs).filter(i => i.dataset.section === 'accomp' && PERIODS[Number(i.dataset.col)]?.type === 'month');

            // Update targets if present
            if (targetInputs.length === 12) {
                updateSection(targetInputs, allInputs, 'target', indicatorType);
            }

            // Update accomplishments if present
            if (accompInputs.length === 12) {
                updateSection(accompInputs, allInputs, 'accomp', indicatorType);
            }
        }

        function getIndicatorTypeForRow(row) {
            const rawType = String(row?.dataset?.indicatorType || '').trim().toLowerCase();
            if (rawType === 'semi-comulative') return 'semi-comulative';
            if (rawType === 'non-comulative') return 'non-comulative';
            return 'comulative';
        }

        function getSectionColInput(allInputs, section, colIndex) {
            return Array.from(allInputs).find(i => i.dataset.section === section && Number(i.dataset.col) === colIndex) || null;
        }

        function updateSection(monthInputs, allInputs, section, indicatorType = 'comulative') {
            const values = monthInputs.map(inp => Number(inp.value) || 0);

            let q1 = 0;
            let q2 = 0;
            let q3 = 0;
            let q4 = 0;
            let annual = 0;

            if (indicatorType === 'non-comulative') {
                q1 = values[2] || 0;
                q2 = values[5] || 0;
                q3 = values[8] || 0;
                q4 = values[11] || 0;
                annual = q4;
            } else if (indicatorType === 'semi-comulative') {
                q1 = Number(getSectionColInput(allInputs, section, 3)?.value) || 0;
                q2 = Number(getSectionColInput(allInputs, section, 7)?.value) || 0;
                q3 = Number(getSectionColInput(allInputs, section, 11)?.value) || 0;
                q4 = Number(getSectionColInput(allInputs, section, 15)?.value) || 0;
                annual = q1 + q2 + q3 + q4;
            } else {
                q1 = values[0] + values[1] + values[2];
                q2 = values[3] + values[4] + values[5];
                q3 = values[6] + values[7] + values[8];
                q4 = values[9] + values[10] + values[11];
                annual = q1 + q2 + q3 + q4;
            }

            const q1Input = getSectionColInput(allInputs, section, 3);
            const q2Input = getSectionColInput(allInputs, section, 7);
            const q3Input = getSectionColInput(allInputs, section, 11);
            const q4Input = getSectionColInput(allInputs, section, 15);
            const annualInput = getSectionColInput(allInputs, section, 16);

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

        // Handle Add Indicator Form Submission
        document.addEventListener('DOMContentLoaded', function() {
            // toggleRow function for program header collapse/expand
            window.toggleRow = function(contentId, iconId) {
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

            const form = document.getElementById('addIndicatorForm');
            if (!form) {
                console.error('Form not found');
                return;
            }
            form.addEventListener('submit', function (e) {
            e.preventDefault();

            // Get all selected office checkboxes
            const selectedOffices = Array.from(document.querySelectorAll('.parent-office-checkbox:checked'))
                .map(checkbox => checkbox.value);

            if (selectedOffices.length === 0) {
                alert('Please select at least one office.');
                return;
            }

            const programId = document.getElementById('modal_program_id').value;
            const indicatorName = document.getElementById('modal_indicator_name').value.trim();
            const indicatorType = document.getElementById('modal_indicator_type').value;
            if (!indicatorName) {
                alert('Please input a performance indicator.');
                return;
            }
            if (!indicatorType) {
                alert('Please select indicator type.');
                return;
            }
            const form = this;
            const token = document.querySelector('input[name="_token"]').value;
            const actionUrl = form.getAttribute('action');

            const formData = new FormData();
            formData.append('_token', token);
            formData.append('program_id', programId);
            formData.append('indicator_name', indicatorName);
            formData.append('indicator_type', indicatorType);
            selectedOffices.forEach(officeId => formData.append('office_id[]', officeId));

            fetch(actionUrl, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: formData
            })
                .then(async response => {
                    if (!response.ok) {
                        const text = await response.text();
                        throw new Error(`HTTP ${response.status}: ${text}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        const modal = bootstrap.Modal.getInstance(document.getElementById('addIndicatorModal'));
                        if (modal) modal.hide();
                        document.getElementById('addIndicatorForm').reset();
                        document.getElementById('indicator_id').value = '';
                        document.querySelectorAll('.parent-office-checkbox').forEach(cb => cb.checked = false);
                        currentProgramIndicators = [];
                        alert('Indicators saved successfully for ' + selectedOffices.length + ' office(s)!');
                        setTimeout(() => {
                            location.reload();
                        }, 500);
                    } else {
                        alert('Error: Failed to save indicators');
                    }
                })
                .catch(error => {
                    console.error('Save error:', error);
                    alert('An error occurred while saving indicators. Check browser console for details.');
                });
            });
        });
    </script>

    <script>
        // Make indicators data available to JavaScript
        const indicatorsData = {!! json_encode($indicatorsForJs ?? []) !!};
    </script>

    <!-- Add Indicator Modal -->
    <div class="modal fade" id="addIndicatorModal" tabindex="-1" aria-labelledby="addIndicatorModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addIndicatorModalLabel">Add Performance Indicator</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <form id="addIndicatorForm" method="POST" action="{{ route('admin.gass_physical.indicators.store') }}" data-update-route-template="{{ route('admin.gass_physical.indicators.update', ':id') }}" data-delete-route-template="{{ route('admin.gass_physical.indicators.destroy', ':id') }}">
                    @csrf
                    <input type="hidden" id="indicator_id" name="indicator_id" value="">

                    <div class="modal-body">
                        <!-- select Program/PAP -->
                        <div class="mb-2">
                            <label for="modal_program_id" class="form-label fw-bold">Title</label>
                            <select name="program_id" id="modal_program_id" class="form-control form-control-lg"
                                required>
                                <option value="">-- Select Program --</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}"
                                        data-program="{{ htmlspecialchars($program->program, ENT_QUOTES) }}"
                                        data-project="{{ htmlspecialchars($program->project, ENT_QUOTES) }}"
                                        data-activities="{{ htmlspecialchars($program->activities, ENT_QUOTES) }}"
                                        data-subactivities="{{ htmlspecialchars($program->subactivities, ENT_QUOTES) }}">
                                        {{ $program->title }}
                                    </option>
                                @endforeach
                            </select>
                            @error('program_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Program (Auto-filled) -->
                        <div class="mb-2">
                            <label for="modal_program_name" class="form-label fw-bold text-sm">Program</label>
                            <input type="text" id="modal_program_name" class="form-control form-control-lg bg-gray-200"
                                readonly>
                        </div>

                        <!-- Project, Activities & Subactivities (Auto-filled) -->
                        <div class="mb-4">
                            <label for="modal_pap_details" class="form-label fw-bold text-sm">Project / Activities /
                                Subactivities</label>
                            <textarea id="modal_pap_details" class="form-control form-control-lg bg-gray-200" rows="4"
                                readonly></textarea>
                        </div>

                        <!-- Performance Indicator -->
                        <div class="mb-4">
                            <label for="modal_indicator_name" class="form-label fw-bold">Performance Indicator</label>
                            <textarea type="text" name="indicator_name" id="modal_indicator_name"
                                class="form-control form-control-lg" placeholder="Input the performance indicator"
                                required></textarea>
                            @error('indicator_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="modal_indicator_type" class="form-label fw-bold">Type of Indicator</label>
                            <select name="indicator_type" id="modal_indicator_type" class="form-control form-control-lg" required>
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">Office / Unit</label>

                            <div class="border rounded p-3" style="max-height: 320px; overflow-y: auto;">
                                <div class="row row-cols-1 row-cols-md-3 g-3">
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
                        <button type="button" class="btn btn-outline-secondary px-3"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary px-3">Add Indicator</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Handle Title Selection - Auto-fill program details and existing indicators
        document.getElementById('modal_program_id').addEventListener('change', function () {
            const selectedOption = this.options[this.selectedIndex];
            const programId = this.value;
            
            document.getElementById('modal_program_name').value = selectedOption.dataset.program || '';

            // Merge Project, Activities, and Subactivities with line breaks
            const project = selectedOption.dataset.project || '';
            const activities = selectedOption.dataset.activities || '';
            const subactivities = selectedOption.dataset.subactivities || '';

            let mergedText = '';
            if (project) mergedText += project + '\n';
            if (activities) mergedText += activities + '\n';
            if (subactivities) mergedText += '  ' + subactivities;

            document.getElementById('modal_pap_details').value = mergedText;

            // Check if this program has existing indicators
            if (programId && indicatorsData[programId] && indicatorsData[programId].length > 0) {
                currentProgramIndicators = indicatorsData[programId];
                const firstIndicator = currentProgramIndicators[0];
                const officeIdsArray = Array.isArray(firstIndicator.office_ids) && firstIndicator.office_ids.length > 0
                    ? firstIndicator.office_ids
                    : currentProgramIndicators.map(i => i.office_id).filter(Boolean);

                // Populate indicator name from existing data
                document.getElementById('modal_indicator_name').value = firstIndicator.name || '';
                document.getElementById('modal_indicator_type').value = firstIndicator.indicator_type || '';

                // Keep hidden field for compatibility (first record)
                document.getElementById('indicator_id').value = firstIndicator.id || '';

                // Check all offices that were previously saved for this program
                setOfficeCheckboxes(officeIdsArray);
            } else {
                // Clear fields if no existing indicator
                currentProgramIndicators = [];
                document.getElementById('modal_indicator_name').value = '';
                document.getElementById('modal_indicator_type').value = '';
                document.getElementById('indicator_id').value = '';
                setOfficeCheckboxes([]);
            }
        });

        // Keep office checkboxes synced with the indicator currently typed/selected
        document.getElementById('modal_indicator_name').addEventListener('input', function () {
            const programId = document.getElementById('modal_program_id').value;
            const matched = findIndicatorByName(programId, this.value);

            if (matched) {
                const officeIdsArray = Array.isArray(matched.office_ids) ? matched.office_ids : [];
                document.getElementById('indicator_id').value = matched.id || '';
                document.getElementById('modal_indicator_type').value = matched.indicator_type || '';
                setOfficeCheckboxes(officeIdsArray);
            } else {
                document.getElementById('indicator_id').value = '';
                document.getElementById('modal_indicator_type').value = '';
                setOfficeCheckboxes([]);
            }
        });
    </script>

</body>

</html>