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
            --green: #10b981;
            --border: #e5e7eb;
            --quarter-bg: #e0e7ff;
            --annual-bg: #e5e7eb;
        }

        .year-header {
            font-size: 1.4rem;
            font-weight: 700;
            color: #1e40af;
            text-align: center;
            padding: 12px 0;
            background: linear-gradient(to right, #eff6ff, #dbeafe);
            border-bottom: 2px solid #3b82f6;
        }

        /* Month & Quarter headers */
        .month-header {
            background: #2f5be7;
            color: white;
            text-align: center;
            font-weight: 600;
            font-size: 11px;
            padding: 4px 2px;
            min-width: 42px;
            border: 1px solid #1e40af;
        }

        .month-header.quarter {
            background: #64748b;
            min-width: 54px;
            font-size: 11.5px;
        }

        .month-header.annual {
            background: #374151;
            min-width: 64px;
            font-size: 12px;
        }

        /* Input fields */
        .month-box {
            width: 100%;
            height: 24px;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            text-align: center;
            font-size: 12px;
            padding: 0;
        }

        .month-box[readonly] {
            background: #e2e8f0;
            color: #475569;
            font-weight: 500;
        }

        .month-box:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 2px rgba(59,130,246,0.3);
        }

        .table-container {
            overflow-x: auto;
            margin-top: 1rem;
        }

        table {
            border-collapse: collapse;
            width: 100%;
            min-width: 1200px; /* prevents too much squeezing on small screens */
        }

        th, td {
            border: 1px solid #d1d5db;
            vertical-align: middle;
        }

        thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background: inherit;
        }

        .quarter-total {
            background: var(--quarter-bg) !important;
        }

        .annual-total {
            background: var(--annual-bg) !important;
            font-weight: 600;
        }
    </style>
</head>
<body>

@include('components.nav')

<div class="d-flex">
    @include('components.sidebar')

    <main class="flex-grow-1 p-3 bg-gradient-to-b from-gray-50 to-white">

        <div class="year-header">
            Physical Performance Targets & Accomplishments – <span id="currentYear">2025</span>
        </div>

        <div class="bg-white rounded-lg shadow mt-3 p-3">

            <!-- BUTTON -->
            <div class="text-end mb-3">
                <button onclick="addPhysicalSet()" class="btn btn-success btn-sm" id="addBtn">
                    <i class="fa fa-plus"></i> Add Physical Performance Columns
                </button>
            </div>

            <div class="table-container">
                <table class="text-sm" id="performanceTable">

                    <thead>
                        <tr class="bg-blue-800 text-white">
                            <th class="px-4 py-3" style="min-width:280px">Programs/Activities/Projects (P/A/Ps)</th>
                            <th class="px-4 py-3" style="min-width:220px">Performance Indicators</th>
                            <th class="px-4 py-3" style="min-width:160px">Office</th>
                            <!-- Month columns will be added here -->
                        </tr>
                    </thead>

                    <tbody class="text-gray-700">
                        <tr class="bg-gray-100 font-semibold">
                            <td class="px-4 py-3" colspan="3">
                                GENERAL ADMINISTRATION AND SUPPORT SERVICES (GASS)<br>
                                GENERAL MANAGEMENT AND SUPERVISION (GMS)<br>
                                A. REPAIR AND MAINTENANCE AND INSURANCE
                            </td>
                        </tr>

                        <tr class="hover:bg-gray-50 data-row">
                            <td class="px-4 py-3 pl-5 text-blue-700 font-medium">
                                1. Repair and Maintenance of Property including hiring of Civil Engineer<br>
                                <span class="ms-4">1.1 Maintenance of Buildings and Other Structures</span>
                            </td>
                            <td class="px-4 py-3">
                                Other building maintained (no.)
                            </td>
                            <td class="px-4 py-3">
                                PENRO Ifugao<br>
                                Buguias<br>
                                IFUGAO<br>
                                PENRO<br>
                                Alfonso Lista<br>
                                Lamut
                            </td>
                        </tr>

                        <!-- More rows can be added here -->
                    </tbody>
                </table>
            </div>
        </div>

    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
let columnsAdded = false;

function addPhysicalSet() {
    if (columnsAdded) return;
    columnsAdded = true;
    document.getElementById("addBtn").disabled = true;
    document.getElementById("addBtn").innerHTML = '<i class="fa fa-check"></i> Columns Added';

    const table = document.getElementById("performanceTable");
    const headerRow = table.querySelector("thead tr");

    const months = [
        { label: "JAN",  type: "month" },
        { label: "FEB",  type: "month" },
        { label: "MAR",  type: "month" },
        { label: "1ST Q", type: "quarter" },
        { label: "APR",  type: "month" },
        { label: "MAY",  type: "month" },
        { label: "JUN",  type: "month" },
        { label: "2ND Q", type: "quarter" },
        { label: "JUL",  type: "month" },
        { label: "AUG",  type: "month" },
        { label: "SEP",  type: "month" },
        { label: "3RD Q", type: "quarter" },
        { label: "OCT",  type: "month" },
        { label: "NOV",  type: "month" },
        { label: "DEC",  type: "month" },
        { label: "4TH Q", type: "quarter" },
        { label: "ANNUAL", type: "annual" }
    ];

    // Add header columns
    months.forEach(m => {
        const th = document.createElement("th");
        th.textContent = m.label;
        th.classList.add("month-header", "text-center", "border");
        
        if (m.type === "quarter") th.classList.add("quarter");
        if (m.type === "annual")  th.classList.add("annual");

        headerRow.appendChild(th);
    });

    // Add input cells to every tbody row
    document.querySelectorAll("tbody tr").forEach(row => {
        months.forEach(m => {
            const td = document.createElement("td");
            td.classList.add("p-1", "text-center", "border");

            const input = document.createElement("input");
            input.type = "number";
            input.className = "month-box";
            input.value = "0";
            input.min = "0";

            if (m.type !== "month") {
                input.readOnly = true;
                td.classList.add(m.type === "quarter" ? "quarter-total" : "annual-total");
            }

            td.appendChild(input);
            row.appendChild(td);
        });
    });
}
</script>

</body>
</html>