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
                            <button onclick="toggleTargetColumns()" class="btn btn-primary btn-sm" id="targetBtn">
                                <i class="fa fa-plus me-1"></i> Targets
                            </button>
                            <button onclick="toggleAccompColumns()" class="btn btn-success btn-sm" id="accompBtn">
                                <i class="fa fa-plus me-1"></i> Accomplishments
                            </button>
                        </div>
                    </div>


                    <div class="table-container">
                        <table class="text-sm" id="performanceTable">

                            <thead>
                                <tr class="bg-primary text-white">
                                    <th class="px-4 py-3" style="min-width:300px">Programs/Activities/Projects (P/A/Ps)
                                    </th>
                                    <th class="px-4 py-3" style="min-width:240px">Performance Indicators</th>
                                    <th class="px-4 py-3" style="min-width:180px">Office / Unit</th>
                                    <!-- month headers added dynamically -->
                                </tr>
                                <tr class="group-row" id="groupHeaders"></tr>
                            </thead>

                            <tbody class="text-gray-800">
                                @foreach($programs as $program)
                                    <tr class="bg-gray-100">
                                        <td class="px-4 py-3" colspan="3">
                                            {{ $program->title }}<br><br>
                                            {{ $program->program }}<br>
                                            <div class="fw-normal ps-4">{{ $program->project }}</div>
                                        </td>
                                    </tr>

                                    @if(isset($indicators[$program->id]) && $indicators[$program->id]->count() > 0)
                                        @php
                                            $indicatorCount = $indicators[$program->id]->count();
                                        @endphp
                                        @foreach($indicators[$program->id] as $indicator)
                                            <tr class="data-row" data-row-id="{{ $program->id }}">
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
                                            </tr>
                                        @endforeach
                                    @else
                                        <tr class="data-row" data-row-id="{{ $program->id }}">
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
            // Ensure first 3 fixed columns are reserved in the group header
            if (groupHeader.children.length === 0) {
                for (let i = 0; i < 3; i++) {
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

                    if (period.type !== "month") {
                        input.readOnly = true;
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

            document.getElementById("performanceTable").addEventListener('input', updateTotals);
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
            if (!input.classList.contains('month-box') || input.readOnly) return;

            const row = input.closest('tr');
            if (!row) return;

            const allInputs = row.querySelectorAll('.month-box');

            // Group by section
            const targetInputs = Array.from(allInputs).filter(i => i.dataset.section === 'target' && !i.readOnly);
            const accompInputs = Array.from(allInputs).filter(i => i.dataset.section === 'accomp' && !i.readOnly);

            // Update targets if present
            if (targetInputs.length === 12) {
                updateSection(targetInputs, allInputs, 'target');
            }

            // Update accomplishments if present
            if (accompInputs.length === 12) {
                updateSection(accompInputs, allInputs, 'accomp');
            }
        }

        function updateSection(monthInputs, allInputs, section) {
            const values = monthInputs.map(inp => Number(inp.value) || 0);

            const q1 = values[0] + values[1] + values[2];
            const q2 = values[3] + values[4] + values[5];
            const q3 = values[6] + values[7] + values[8];
            const q4 = values[9] + values[10] + values[11];
            const annual = q1 + q2 + q3 + q4;

            // Find readonly inputs for this section
            const sectionReadonly = Array.from(allInputs)
                .filter(i => i.dataset.section === section && i.readOnly);

            if (sectionReadonly.length >= 5) {
                sectionReadonly[0].value = q1;
                sectionReadonly[1].value = q2;
                sectionReadonly[2].value = q3;
                sectionReadonly[3].value = q4;
                sectionReadonly[4].value = annual;
            }
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
            if (!indicatorName) {
                alert('Please input a performance indicator.');
                return;
            }
            const form = this;
            const token = document.querySelector('input[name="_token"]').value;
            const actionUrl = form.getAttribute('action');

            const formData = new FormData();
            formData.append('_token', token);
            formData.append('program_id', programId);
            formData.append('indicator_name', indicatorName);
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

                // Keep hidden field for compatibility (first record)
                document.getElementById('indicator_id').value = firstIndicator.id || '';

                // Check all offices that were previously saved for this program
                setOfficeCheckboxes(officeIdsArray);
            } else {
                // Clear fields if no existing indicator
                currentProgramIndicators = [];
                document.getElementById('modal_indicator_name').value = '';
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
                setOfficeCheckboxes(officeIdsArray);
            } else {
                document.getElementById('indicator_id').value = '';
                setOfficeCheckboxes([]);
            }
        });
    </script>

</body>

</html>