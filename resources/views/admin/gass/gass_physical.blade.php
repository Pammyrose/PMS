<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (GASS) – 2025 - DENR PMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        :root {
            --month-bg:    #f1f5f9;
            --quarter-bg:  #dbeafe;
            --annual-bg:   #e2e8f0;
            --header-blue: #1e40af;
            --border:      #cbd5e1;
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

        .group-target { background: #d1fae5; color: #065f46; }
        .group-accomp { background: #dbeafe; color: #1e40af; }

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
            background: #16958d !important;  /* pastel pink */
            color: white !important;       /* dark pink text for contrast */
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
            box-shadow: 0 0 0 3px rgba(59,130,246,0.2);
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

        .target-total   { background: #d1fae5 !important; }
        .annual-target  { background: #a7f3d0 !important; font-weight: 600; }

        .quarter-total { background: var(--quarter-bg) !important; }
        .annual-total  { background: var(--annual-bg)  !important; font-weight: 600; }

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

        th, td {
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
    </style>
</head>
<body class="bg-light">

@include('components.nav')

<div class="d-flex">
    @include('components.sidebar')

    <main class="flex-grow-1 p-3">

        <div class="year-header">
            Physical Performance Targets & Accomplishments – <span id="currentYear">2025</span>
        </div>

        <div class="bg-white rounded shadow p-3">

            <div class="text-end mb-3 d-flex gap-2 justify-content-end">
                <button onclick="toggleTargetColumns()" class="btn btn-primary btn-sm" id="targetBtn">
                    <i class="fa fa-plus me-1"></i> Add Targets
                </button>
                <button onclick="toggleAccompColumns()" class="btn btn-success btn-sm" id="accompBtn">
                    <i class="fa fa-plus me-1"></i> Add Accomplishments
                </button>
            </div>

            <div class="table-container">
                <table class="text-sm" id="performanceTable">

                    <thead>
                        <tr class="bg-primary text-white">
                            <th class="px-4 py-3" style="min-width:300px">Programs/Activities/Projects (P/A/Ps)</th>
                            <th class="px-4 py-3" style="min-width:240px">Performance Indicators</th>
                            <th class="px-4 py-3" style="min-width:180px">Office / Unit</th>
                            <!-- month headers added dynamically -->
                        </tr>
                        <tr class="group-row" id="groupHeaders"></tr>
                    </thead>

                    <tbody class="text-gray-800">
                        <tr class="bg-gray-100">
                            <td class="px-4 py-3" colspan="3">
                                GENERAL ADMINISTRATION AND SUPPORT SERVICES (GASS)<br><br>
                                GENERAL MANAGEMENT AND SUPERVISION (GMS)<br>
                                <div class="fw-normal ps-4">A. REPAIR AND MAINTENANCE AND INSURANCE</div>
                            </td>
                        </tr>

                        <tr class="data-row" data-row-id="1">
                            <td class="px-4 py-3 pl-5 text-primary fw-medium">
                                1. Repair and Maintenance of Property including hiring of Civil Engineer<br>
                                <span class="ms-4 small">1.1 Maintenance of Buildings and Other Structures</span>
                            </td>
                            <td class="px-4 py-3">
                                Other building maintained (no.)
                            </td>
                            <td class="px-4 py-3 small">
                                PENRO Ifugao<br>
                                Buguias • Alfonso Lista • Lamut<br>
                                IFUGAO PENRO
                            </td>
                        </tr>

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
    { label: "JAN",  type: "month"   },
    { label: "FEB",  type: "month"   },
    { label: "MAR",  type: "month"   },
    { label: "Q1",   type: "quarter" },
    { label: "APR",  type: "month"   },
    { label: "MAY",  type: "month"   },
    { label: "JUN",  type: "month"   },
    { label: "Q2",   type: "quarter" },
    { label: "JUL",  type: "month"   },
    { label: "AUG",  type: "month"   },
    { label: "SEP",  type: "month"   },
    { label: "Q3",   type: "quarter" },
    { label: "OCT",  type: "month"   },
    { label: "NOV",  type: "month"   },
    { label: "DEC",  type: "month"   },
    { label: "Q4",   type: "quarter" },
    { label: "ANNUAL", type: "annual" }
];

const COL_COUNT = PERIODS.length; // 17

let targetsVisible = false;
let accompVisible  = false;

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
        document.getElementById("targetBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Add Targets';
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
        document.getElementById("accompBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Add Accomplishments';
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
        if (p.type === "annual")  th.classList.add("annual");

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
</script>

</body>
</html>