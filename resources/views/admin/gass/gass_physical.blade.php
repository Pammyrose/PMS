<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (GASS) – 2025 - DENR PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<style>
    :root {
        --green: #10b981;
        --amber: #f59e0b;
        --red: #ef4444;
        --gray: #6b7280;
        --border: #e5e7eb;
        --input-bg: #f9fafb;
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: system-ui, -apple-system, sans-serif;
        background: #f8fafc;
        color: #1f2937;
        padding: 20px;
        line-height: 1.5;
    }

    .header {
        background: rgb(255, 255, 255);
        border-radius: 12px;
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        padding: 16px 24px;
        margin-bottom: 24px;
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        align-items: center;
        justify-content: space-between;
    }

    .header h1 {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
    }

    .filters {
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    select,
    button {
        padding: 8px 16px;
        border: 1px solid var(--border);
        border-radius: 6px;
        font-size: 0.95rem;
        background: white;
        cursor: pointer;
    }

    button {
        background: var(--green);
        color: white;
        border: none;
        font-weight: 500;
    }

    button:hover {
        background: #059669;
    }

    .card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .table-container {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.88rem;
    }

    th,
    td {
        padding: 10px 6px;
        text-align: center;
        border-bottom: 1px solid var(--border);
        white-space: nowrap;
    }

    th {
        background-color: rgb(43, 92, 255);
        font-weight: 600;
        color: white;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    td:first-child,
    th:first-child {
        text-align: left;
        min-width: 220px;
        font-weight: 500;
    }

    .indicator {
        color: var(--gray);
        font-size: 0.9rem;
    }

    .cell-green {
        background: rgba(16, 185, 129, 0.18);
        font-weight: 600;
    }

    .cell-amber {
        background: rgba(245, 158, 11, 0.18);
        font-weight: 600;
    }

    .cell-red {
        background: rgba(239, 68, 68, 0.18);
        font-weight: 600;
    }

    input[type="number"] {
        width: 70px;
        padding: 4px 2px;
        border: 1px solid #d1d5db;
        border-radius: 4px;
        background: var(--input-bg);
        text-align: center;
        font-size: 0.95rem;
    }

    input[type="number"]:focus {
        outline: none;
        border-color: var(--green);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.2);
    }

    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }

    .hidden {
        display: none !important;
    }

    .legend {
        display: flex;
        flex-wrap: wrap;
        gap: 24px;
        margin: 24px 0;
        font-size: 0.9rem;
        color: var(--gray);
        justify-content: center;
    }

    .legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .dot {
        width: 12px;
        height: 12px;
        border-radius: 50%;
    }

    .dot-green {
        background: var(--green);
    }

    .dot-amber {
        background: var(--amber);
    }

    .dot-red {
        background: var(--red);
    }
</style>

<body>

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-2 bg-gradient-to-b from-gray-50 to-white">
            <div class="min-h-screen flex flex-col">

                <div class="header">
                    <h1>
                        Physical Performance (GASS)
                    </h1>
                    <div class="filters">
                        <select name="office_id" form="physicalForm">
                            <option value="1">Office: GASS</option>
                        </select>
                        <select>
                            <option>All Divisions</option>
                        </select>
                        <select name="year" form="physicalForm">
                            <option value="2025" selected>Year: 2025</option>
                            <option value="2026">Year: 2026</option>
                        </select>
                        <select id="periodSelect">
                            <option value="monthly">Monthly</option>
                            <option value="quarterly">Quarterly</option>
                            <option value="semiannual">Semi-Annual</option>
                            <option value="annual">Annual</option>
                        </select>
                        <button type="button">Generate Report</button>
                        <button type="submit" form="physicalForm" style="background:#6b7280;">Save</button>
                    </div>
                </div>

                <!-- FORM START -->
                <form id="physicalForm" method="POST" action="{{ route('admin.gass.physical.save') }}">
                    @csrf

                    <div class="card">
                        <div class="table-container">
                            <table id="performanceTable">
                                <thead class="bg-blue-600">
                                    <tr id="monthlyHeader">
                                        <th>Programs / Activities</th>
                                        <th>Performance Indicator</th>
                                        <th>JAN</th>
                                        <th>FEB</th>
                                        <th>MAR</th>
                                        <th>APR</th>
                                        <th>MAY</th>
                                        <th>JUN</th>
                                        <th>JUL</th>
                                        <th>AUG</th>
                                        <th>SEP</th>
                                        <th>OCT</th>
                                        <th>NOV</th>
                                        <th>DEC</th>
                                        <th>TOTAL</th>
                                        <th>REMARKS</th>
                                    </tr>

                                    <tr id="quarterlyHeader" class="hidden">
                                        <th>Programs / Activities</th>
                                        <th>Performance Indicator</th>
                                        <th>1st QTR</th>
                                        <th>2nd QTR</th>
                                        <th>3rd QTR</th>
                                        <th>4th QTR</th>
                                        <th>TOTAL</th>
                                        <th>REMARKS</th>
                                    </tr>

                                    <tr id="semiannualHeader" class="hidden">
                                        <th>Programs / Activities</th>
                                        <th>Performance Indicator</th>
                                        <th>1st Half (Jan–Jun)</th>
                                        <th>2nd Half (Jul–Dec)</th>
                                        <th>TOTAL</th>
                                        <th>REMARKS</th>
                                    </tr>

                                    <tr id="annualHeader" class="hidden">
                                        <th>Programs / Activities</th>
                                        <th>Performance Indicator</th>
                                        <th>ANNUAL TOTAL</th>
                                        <th>REMARKS</th>
                                    </tr>
                                    
                                </thead>

                                <tbody>
                                    @forelse($programs as $prog)
                                        @php
                                            $entry = $existing[$prog->id] ?? null;
                                            $rowIndex = $loop->index;
                                        @endphp

                                        <tr>

                                            <td>
                                                <input type="hidden" name="entries[{{ $rowIndex }}][programs_id]"
                                                    value="{{ $prog->id }}">
                                                <input type="hidden" name="entries[{{ $rowIndex }}][target]"
                                                    value="{{ $prog->target ?? 0 }}">
                                                <input type="hidden" name="entries[{{ $rowIndex }}][year]" value="2025">
                                                <input type="hidden" name="entries[{{ $rowIndex }}][period_type]"
                                                    class="period-type-field" value="monthly">
                                                <strong>{{ $prog->title ?: $prog->activities ?: $prog->project ?: 'TITLE' . $prog->id }}</strong>

                                                @if($prog->subactivities)
                                                    <br><small class="text-gray-600 pl-4">
                                                        {{ str_replace("\n", " • ", trim($prog->subactivities)) }}
                                                    </small>
                                                @endif
                                            </td>

                                            <td class="indicator">
                                                <input type="hidden" name="entries[{{ $rowIndex }}][performance_indicator]"
                                                    value="{{ $entry?->performance_indicator ?? '' }}">
                                                {{ $entry?->performance_indicator ?? 'Not specified' }}
                                            </td>

                                            <!-- Monthly -->
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][jan]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.jan", $entry?->jan ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][feb]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.feb", $entry?->feb ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][mar]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.mar", $entry?->mar ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][apr]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.apr", $entry?->apr ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][may]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.may", $entry?->may ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][jun]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.jun", $entry?->jun ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][jul]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.jul", $entry?->jul ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][aug]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.aug", $entry?->aug ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][sep]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.sep", $entry?->sep ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][oct]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.oct", $entry?->oct ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][nov]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.nov", $entry?->nov ?? 0) }}">
                                            </td>
                                            <td class="monthly-col">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][dec]"
                                                    class="month-input"
                                                    value="{{ old("entries.$rowIndex.dec", $entry?->dec ?? 0) }}">
                                            </td>

                                            <td class="total monthly-total"><strong>0</strong></td>

                                            <!-- Quarterly (hidden by default) -->
                                            <td class="qtr-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][q1]"
                                                    class="qtr-input"
                                                    value="{{ old("entries.$rowIndex.q1", $entry?->q1 ?? 0) }}">
                                            </td>
                                            <td class="qtr-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][q2]"
                                                    class="qtr-input"
                                                    value="{{ old("entries.$rowIndex.q2", $entry?->q2 ?? 0) }}">
                                            </td>
                                            <td class="qtr-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][q3]"
                                                    class="qtr-input"
                                                    value="{{ old("entries.$rowIndex.q3", $entry?->q3 ?? 0) }}">
                                            </td>
                                            <td class="qtr-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][q4]"
                                                    class="qtr-input"
                                                    value="{{ old("entries.$rowIndex.q4", $entry?->q4 ?? 0) }}">
                                            </td>
                                            <td class="total qtr-total hidden"><strong>0</strong></td>

                                            <!-- Semi-annual -->
                                            <td class="semi-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][first_half]"
                                                    class="semi-input"
                                                    value="{{ old("entries.$rowIndex.first_half", $entry?->first_half ?? 0) }}">
                                            </td>
                                            <td class="semi-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][second_half]"
                                                    class="semi-input"
                                                    value="{{ old("entries.$rowIndex.second_half", $entry?->second_half ?? 0) }}">
                                            </td>
                                            <td class="total semi-total hidden"><strong>0</strong></td>

                                            <!-- Annual -->
                                            <td class="annual-col hidden">
                                                <input type="number" min="0" name="entries[{{ $rowIndex }}][annual_total]"
                                                    class="annual-input"
                                                    value="{{ old("entries.$rowIndex.annual_total", $entry?->annual_total ?? 0) }}">
                                            </td>

                                            <!-- Remarks -->
                                            <td>
                                                <input type="text" name="entries[{{ $rowIndex }}][remarks]"
                                                    placeholder="Remarks..." style="width:180px;"
                                                    value="{{ old("entries.$rowIndex.remarks", $entry?->remarks ?? '') }}">
                                            </td>
                                        </tr>

                                    @empty
                                        <tr>
                                            <td colspan="16" class="text-center py-12 text-gray-500 italic">
                                                No programs loaded for this view.<br>
                                                <small>(Select a program from the GASS overview page)</small>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>

                            <div class="legend">
                                <div class="legend-item"><span class="dot dot-green"></span> ≥ 100% / On Track</div>
                                <div class="legend-item"><span class="dot dot-amber"></span> 85–99% / Needs Attention
                                </div>
                                <div class="legend-item"><span class="dot dot-red"></span>
                                    < 85% / Delayed</div>
                                </div>
                            </div>
                        </div>
                </form>
                <!-- FORM END -->

            </div>
        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Your existing JavaScript remains unchanged
        document.getElementById('toggleSidebar')?.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });

        const periodSelect = document.getElementById('periodSelect');

        function updateView() {
            const view = periodSelect.value;

            document.querySelectorAll('.period-type-field').forEach(field => {
                field.value = view;
            });

            document.getElementById('monthlyHeader').classList.toggle('hidden', view !== 'monthly');
            document.getElementById('quarterlyHeader').classList.toggle('hidden', view !== 'quarterly');
            document.getElementById('semiannualHeader').classList.toggle('hidden', view !== 'semiannual');
            document.getElementById('annualHeader').classList.toggle('hidden', view !== 'annual');

            document.querySelectorAll('.monthly-col, .monthly-total').forEach(el => {
                el.classList.toggle('hidden', view !== 'monthly');
            });

            document.querySelectorAll('.qtr-col, .qtr-total').forEach(el => {
                el.classList.toggle('hidden', view !== 'quarterly');
            });

            document.querySelectorAll('.semi-col, .semi-total').forEach(el => {
                el.classList.toggle('hidden', view !== 'semiannual');
            });

            document.querySelectorAll('.annual-col').forEach(el => {
                el.classList.toggle('hidden', view !== 'annual');
            });
        }

        function calculateTotals() {
            document.querySelectorAll('tr[data-target]').forEach(row => {
                const target = parseFloat(row.dataset.target) || 0;
                let value = 0;
                const view = periodSelect.value;

                if (view === 'monthly') {
                    row.querySelectorAll('.month-input').forEach(inp => value += Number(inp.value) || 0);
                    const cell = row.querySelector('.monthly-total');
                    if (cell) {
                        cell.querySelector('strong').textContent = value;
                        applyColor(cell, value, target);
                    }
                } else if (view === 'quarterly') {
                    row.querySelectorAll('.qtr-input').forEach(inp => value += Number(inp.value) || 0);
                    const cell = row.querySelector('.qtr-total');
                    if (cell) {
                        cell.querySelector('strong').textContent = value;
                        applyColor(cell, value, target);
                    }
                } else if (view === 'semiannual') {
                    row.querySelectorAll('.semi-input').forEach(inp => value += Number(inp.value) || 0);
                    const cell = row.querySelector('.semi-total');
                    if (cell) {
                        cell.querySelector('strong').textContent = value;
                        applyColor(cell, value, target);
                    }
                } else if (view === 'annual') {
                    const inp = row.querySelector('.annual-input');
                    if (inp) {
                        value = Number(inp.value) || 0;
                        applyColor(inp.parentElement, value, target);
                    }
                }
            });
        }

        function applyColor(cell, value, target) {
            if (target <= 0) return;
            const percent = (value / target) * 100;
            cell.classList.remove('cell-green', 'cell-amber', 'cell-red');
            if (percent >= 100) cell.classList.add('cell-green');
            else if (percent >= 85) cell.classList.add('cell-amber');
            else cell.classList.add('cell-red');
        }

        document.querySelectorAll('input[type="number"]').forEach(input => {
            input.addEventListener('input', calculateTotals);
        });

        periodSelect.addEventListener('change', () => {
            updateView();
            calculateTotals();
        });

        // Initialize
        periodSelect.value = 'monthly';
        updateView();
        calculateTotals();
    </script>
</body>

</html>