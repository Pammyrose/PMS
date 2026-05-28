    
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Physical Performance (GASS) - PMS</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/gass/gass_physical.css') }}">
</head>

<body class="bg-light">

    @include('components.nav')

    <div class="d-flex">
        @include('components.sidebar')

        <main class="flex-grow-1 p-3">

            @include('admin.gass.partials.gass_physical_header')

            <div class="bg-white rounded shadow p-3">
                @include('admin.gass.partials.gass_physical_tabs')
                @include('admin.gass.partials.gass_physical_summary')
                @include('admin.gass.partials.gass_physical_toolbar')

                    <div class="table-container">
                        <table class="text-sm" id="performanceTable">

                            <thead>
                                <tr class="text-md text-white">
                                    <th class="px-4 py-3"
                                        style="min-width:300px; background: linear-gradient(to right, #2563eb, #1e40af); color: #fff; border-bottom: 3px solid #3b82f6;">
                                        Programs/Activities/Projects (P/A/Ps)
                                    </th>
                                    <th class="px-4 py-3"
                                        style="min-width:240px; background: linear-gradient(to right, #2563eb, #1e40af); color: #fff; border-bottom: 3px solid #3b82f6;">
                                        Performance Indicators</th>
                                    <th class="px-4 py-3"
                                        style="min-width:180px; background: linear-gradient(to right, #2563eb, #1e40af); color: #fff; border-bottom: 3px solid #3b82f6;">
                                        Office / Unit</th>
                                    <!-- month headers added dynamically -->
                                </tr>
                                <tr class="group-row" id="groupHeaders"></tr>
                            </thead>

                            <tbody class="text-gray-800">
                                @include('admin.gass.partials.gass_physical_table_rows')
</tbody>
                        </table>
                    </div>
                </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
            document.addEventListener('DOMContentLoaded', function () {
            const penroNames = ['benguet', 'ifugao', 'mt.province', 'apayao', 'abra', 'kalinga', 'ro'];
            // Add all known CENRO locations here (lowercase)
            const cenroNames = [
                'bangued', 'bucay', 'lagangilang', 'licuan-baay', 'malibcong', 'manabo', 'penarrubia', 'pidigan', 'pilar', 'sal-lapadan', 'san juan', 'san quintin', 'tubo', 'villaviciosa',
                'balbalan', 'lubuagan', 'pasil', 'pinukpuk', 'rizal', 'tabuk', 'tanudan', 'tinglayan',
                'calanasan', 'conner', 'florida blanca', 'kabugao', 'luna', 'pudtol', 'santa marcela',
                'aguinaldo', 'alfonso lista', 'asipulo', 'hingyon', 'hungduan', 'kiangan', 'lagawe', 'lamut', 'mayoyao', 'tinoc',
                'atok', 'bakun', 'buguias', 'itogon', 'kabayan', 'kapangan', 'kibungan', 'la trinidad', 'mankayan', 'sablan', 'tuba', 'tublay',
                'barlig', 'bauko', 'besao', 'bontoc', 'natonin', 'paracelis', 'sabangan', 'sadanga', 'sagada', 'tadian',
                'baguio', 'city', 'cenro' // keep 'cenro' for any generic matches
            ];
            const selectAllPenroRo = document.getElementById('selectAllPenroRo');
            const selectAllCenro = document.getElementById('selectAllCenro');
            function isPenroOrRo(name) {
                name = name.toLowerCase();
                return penroNames.some(penro => name.includes(penro)) || (name.includes('penro') && !name.includes('cenro'));
            }
            function isCenro(name) {
                name = name.toLowerCase();
                // If it matches any known CENRO location or contains 'cenro', and is not a PENRO/RO
                const penroOnly = ['benguet', 'ifugao', 'mt.province', 'apayao', 'abra', 'kalinga', 'ro'];
                if (penroOnly.some(penro => name.includes(penro)) && !name.includes('cenro')) return false;
                if (name.includes('penro') && !name.includes('cenro')) return false;
                return cenroNames.some(cenro => name.includes(cenro));
            }
            function setCheckboxesByType(typeFn, checked) {
                document.querySelectorAll('.office-checkbox').forEach(cb => {
                    const label = cb.closest('.form-check').querySelector('label');
                    if (label && typeFn(label.textContent || '')) {
                        cb.checked = checked;
                    }
                });
            }
            if (selectAllPenroRo) {
                selectAllPenroRo.addEventListener('change', function () {
                    setCheckboxesByType(isPenroOrRo, this.checked);
                });
            }
            if (selectAllCenro) {
                selectAllCenro.addEventListener('change', function () {
                    setCheckboxesByType(isCenro, this.checked);
                });
            }

            // Set year to current year when modal opens
            const addIndicatorModal = document.getElementById('addIndicatorModal');
            if (addIndicatorModal) {
                addIndicatorModal.addEventListener('show.bs.modal', function () {
                    const yearInput = document.getElementById('pap_year');
                    if (yearInput && !yearInput.value) {
                        yearInput.value = new Date().getFullYear();
                    }
                });
            }
        });

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
        let summaryVisible = false;
        let accompVisible = false;
        let remarksVisible = false;
        let monthInputsVisible = false;
        let totalsListenerRegistered = false;
        function toggleSummaryColumns() {
            const table = document.getElementById("performanceTable");
            const headerRow = table.querySelector("thead tr:not(.group-row)");
            const groupRow = document.getElementById("groupHeaders");
            const hadVisibleSection = summaryVisible || targetsVisible || accompVisible;

            if (!summaryVisible) {
                summaryVisible = true;
                if (!hadVisibleSection) {
                    monthInputsVisible = false;
                }
                document.getElementById("summaryBtn").innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Summary';
                document.getElementById("summaryBtn").classList.replace("btn-info", "btn-outline-info");

                addColumns(headerRow, groupRow, "Summary", "summary");
                addInputCells("summary");
                refreshMonthButtonState();
                refreshSummaryCards();
            } else {
                summaryVisible = false;
                document.getElementById("summaryBtn").innerHTML = '<i class="fa fa-chart-bar me-1"></i> Summary';
                document.getElementById("summaryBtn").classList.replace("btn-outline-info", "btn-info");

                removeSectionColumns(groupRow, headerRow, 'summary');
                refreshMonthButtonState();
                refreshSummaryCards();
            }
        }

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

            Object.entries(sourceData || {}).forEach(([programId, indicators]) => {
                Object.entries(indicators || {}).forEach(([indicatorId, offices]) => {
                    Object.entries(offices || {}).forEach(([officeId, officeData]) => {
                        MONTH_COLS.forEach(colIndex => {
                            const monthKey = PERIOD_KEYS[colIndex];
                            if (!monthKey) return;

                            const value = Number(officeData?.[monthKey] ?? 0);
                            const safeValue = Number.isFinite(value) ? value : 0;
                            const key = `${programId}|${indicatorId}|${officeId}|${monthKey}`;

                            result.set(key, safeValue);
                        });
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
                const programId = String(row?.dataset?.rowId || '').trim();
                const indicatorId = String(row?.dataset?.indicatorId || '').trim();

                if (!programId || !indicatorId || !officeId || !monthKey) return;

                const value = Number(input.value);
                const safeValue = Number.isFinite(value) ? value : 0;
                const key = `${programId}|${indicatorId}|${officeId}|${monthKey}`;

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

            refreshSummaryInputs();
        }

        function refreshSummaryInputs() {
            if (!summaryVisible) return;

            document.querySelectorAll('tbody tr[data-row-id]').forEach(row => {
                updateSummaryInputsForRow(row);
            });
        }

        function updateSummaryInputsForRow(row) {
            if (!row) return;

            const summaryInputs = row.querySelectorAll('.month-box.summary-box');
            if (summaryInputs.length === 0) return;

            const programId = Number(row.dataset.rowId || 0);
            const indicatorId = Number(row.dataset.indicatorId || 0);
            const allInputs = row.querySelectorAll('.month-box');

            summaryInputs.forEach(input => {
                const sectionType = String(input.dataset.summarySection || '').trim();
                const periodIndex = Number(input.dataset.summaryPeriodIndex);
                const periodKey = String(input.dataset.summaryPeriodKey || '').trim();
                const officeId = String(input.dataset.officeId || '').trim();
                if (!sectionType || !periodKey) return;

                let value = Number.NaN;

                if (Number.isInteger(periodIndex)) {
                    const sourceInput = getSectionColInput(allInputs, sectionType, periodIndex, officeId || null);
                    if (sourceInput) {
                        const parsed = Number(sourceInput.value);
                        if (Number.isFinite(parsed)) {
                            value = parsed;
                        }
                    }
                }

                if (!Number.isFinite(value)) {
                    value = getStoredValueForSummary(sectionType, programId, indicatorId, officeId, periodKey);
                }

                input.value = Number.isFinite(value) ? value : 0;
            });
        }

        function getStoredValueForSummary(sectionType, programId, indicatorId, officeId, periodKey) {
            if (!programId || !indicatorId || !periodKey) return 0;

            const source = sectionType === 'target'
                ? existingTargetsByIndicator
                : existingAccompByIndicator;

            const programEntry = source[String(programId)] || {};
            const indicatorEntry = programEntry[String(indicatorId)] || {};
            const officeKey = String(officeId || '').trim();
            const officeEntry = officeKey ? (indicatorEntry[officeKey] || null) : null;
            const rawValue = officeEntry && Object.prototype.hasOwnProperty.call(officeEntry, periodKey)
                ? officeEntry[periodKey]
                : 0;
            const value = Number(rawValue);

            return Number.isFinite(value) ? value : 0;
        }

        function applyMonthInputVisibility() {
            document.querySelectorAll('th[data-period-type="month"]').forEach(cell => {
                // Always show month columns for summary section
                if (cell.dataset.dynamicSection === 'summary') {
                    cell.style.display = '';
                } else {
                    cell.style.display = monthInputsVisible ? '' : 'none';
                }
            });

            document.querySelectorAll('td[data-period-type="month"]').forEach(cell => {
                if (cell.dataset.dynamicSection === 'summary') {
                    cell.style.display = '';
                } else {
                    cell.style.display = monthInputsVisible ? '' : 'none';
                }
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
                monthBtn.style.display = 'none';
            } else {
                monthBtn.style.display = '';
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

            const remarksBtn = document.getElementById('remarksBtn');
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
                if (remarksBtn) remarksBtn.style.display = '';
            } else {
                accompVisible = false;
                document.getElementById("accompBtn").innerHTML = '<i class="fa fa-plus me-1"></i> Accomplishments';
                document.getElementById("accompBtn").classList.replace("btn-outline-success", "btn-success");

                removeSectionColumns(groupRow, headerRow, 'accomp');
                refreshMonthButtonState();
                refreshSummaryCards();
                if (remarksBtn) remarksBtn.style.display = 'none';
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
                for (let i = 0; i < 3; i++) {
                    const emptyTh = document.createElement("th");
                    groupHeader.appendChild(emptyTh);
                }
            }

            // Determine which columns to show for summary
            let periodIndexes = [];
            if (type === 'summary') {
                // Find current month and quarter
                const now = new Date();
                const currentMonth = now.getMonth(); // 0-based
                let monthCol = PERIODS.findIndex((p, idx) => p.type === 'month' && idx === currentMonth);
                if (monthCol === -1) monthCol = 0;
                let quarterCol = 0;
                if (currentMonth <= 2) quarterCol = 3; // Q1
                else if (currentMonth <= 5) quarterCol = 7; // Q2
                else if (currentMonth <= 8) quarterCol = 11; // Q3
                else quarterCol = 15; // Q4
                let annualCol = PERIODS.length - 1;
                // Order: annual, quarter, month
                periodIndexes = [annualCol, quarterCol, monthCol];
            }

            // For summary, double the columns for targets and accomplishments
            const thGroup = document.createElement("th");
            thGroup.colSpan = type === 'summary' ? 6 : COL_COUNT;
            thGroup.className = `group-header group-${type}`;
            thGroup.textContent = title;
            const remarksGroup = groupHeader.querySelector('.group-remarks');
            if (remarksGroup) {
                groupHeader.insertBefore(thGroup, remarksGroup);
            } else {
                groupHeader.appendChild(thGroup);
            }

            if (type === 'summary') {
                // Order: annual, quarter, month for targets, then annual, quarter, month for accomp
                const summaryOrder = ['annual', 'quarter', 'month'];
                // Target columns first
                summaryOrder.forEach(periodType => {
                    const idx = periodIndexes.find(i => PERIODS[i].type === periodType);
                    if (idx !== undefined && idx !== -1) {
                        const p = PERIODS[idx];
                        const thTarget = document.createElement("th");
                        thTarget.classList.add("month-header", "text-center");
                        thTarget.classList.add(`dynamic-header-${type}`);
                        thTarget.dataset.dynamicSection = type;
                        thTarget.dataset.periodType = p.type;
                        thTarget.innerHTML = p.label + ' Target';
                        if (p.type === "quarter") thTarget.classList.add("quarter");
                        if (p.type === "annual") thTarget.classList.add("annual");
                        const remarksHeader = mainHeader.querySelector('th[data-dynamic-section="remarks"]');
                        if (remarksHeader) {
                            mainHeader.insertBefore(thTarget, remarksHeader);
                        } else {
                            mainHeader.appendChild(thTarget);
                        }
                    }
                });
                // Accomplishment columns next
                summaryOrder.forEach(periodType => {
                    const idx = periodIndexes.find(i => PERIODS[i].type === periodType);
                    if (idx !== undefined && idx !== -1) {
                        const p = PERIODS[idx];
                        const thAccomp = document.createElement("th");
                        thAccomp.classList.add("month-header", "text-center");
                        thAccomp.classList.add(`dynamic-header-${type}`);
                        thAccomp.dataset.dynamicSection = type;
                        thAccomp.dataset.periodType = p.type;
                        thAccomp.innerHTML = p.label + ' Accomp';
                        if (p.type === "quarter") thAccomp.classList.add("quarter");
                        if (p.type === "annual") thAccomp.classList.add("annual");
                        const remarksHeader = mainHeader.querySelector('th[data-dynamic-section="remarks"]');
                        if (remarksHeader) {
                            mainHeader.insertBefore(thAccomp, remarksHeader);
                        } else {
                            mainHeader.appendChild(thAccomp);
                        }
                    }
                });
            } else {
                PERIODS.forEach((p, idx) => {
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
        }

        function addRemarksColumn(mainHeader, groupHeader) {
            if (mainHeader.querySelector('th[data-dynamic-section="remarks"]')) {
                return;
            }

            if (groupHeader.children.length === 0) {
                for (let i = 0; i < 3; i++) {
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

        function createSpacerElement(tagName, baseClass) {
            const spacer = document.createElement(tagName);
            spacer.className = `${baseClass} remarks-spacer`.trim();
            spacer.tabIndex = -1;
            spacer.readOnly = true;
            spacer.setAttribute('aria-hidden', 'true');

            if (tagName === 'textarea') {
                spacer.rows = 1;
            } else if (tagName === 'input') {
                spacer.type = 'text';
            }

            return spacer;
        }

        function buildAlignedOfficeLines({
            officeEntries,
            groupBreakIndices = [],
            groupPenroFlags = [],
            spacerFactory,
            renderOfficeInput
        }) {
            const normalizedEntries = officeEntries.length > 0
                ? officeEntries
                : [{ id: currentOfficeId || null, name: 'Office' }];

            const wrapper = document.createElement('div');
            wrapper.className = 'office-lines';

            const addSpacerLine = () => {
                if (typeof spacerFactory !== 'function') return;
                const spacerElement = spacerFactory();
                if (!spacerElement) return;

                const spacerLine = document.createElement('div');
                spacerLine.className = 'input-line';
                spacerLine.appendChild(spacerElement);
                wrapper.appendChild(spacerLine);
            };

            addSpacerLine();

            const groupRanges = [];
            let rangeStart = 0;
            const sortedBreaks = [...groupBreakIndices]
                .map(index => Number(index))
                .filter(index => Number.isInteger(index) && index >= 0)
                .sort((left, right) => left - right);

            sortedBreaks.forEach(breakIndex => {
                if (breakIndex >= rangeStart && breakIndex < normalizedEntries.length) {
                    groupRanges.push({ start: rangeStart, end: breakIndex });
                    rangeStart = breakIndex + 1;
                }
            });

            if (rangeStart < normalizedEntries.length) {
                groupRanges.push({ start: rangeStart, end: normalizedEntries.length - 1 });
            }

            const groupStartToIndex = new Map();
            groupRanges.forEach((range, idx) => {
                groupStartToIndex.set(range.start, idx);
            });

            normalizedEntries.forEach((office, officeIndex) => {
                const currentGroupIndex = groupStartToIndex.get(officeIndex);
                if (currentGroupIndex !== undefined && Boolean(groupPenroFlags[currentGroupIndex])) {
                    addSpacerLine();
                }

                const inputElement = renderOfficeInput(office, officeIndex, {
                    groupIndex: currentGroupIndex,
                    groupRange: currentGroupIndex !== undefined ? groupRanges[currentGroupIndex] : null,
                    entries: normalizedEntries
                });

                if (!inputElement) {
                    return;
                }

                const inputLine = document.createElement('div');
                inputLine.className = 'input-line';
                inputLine.appendChild(inputElement);
                wrapper.appendChild(inputLine);
            });

            return wrapper;
        }

        function addRemarksCells() {
            document.querySelectorAll('tbody tr[data-row-id]').forEach(row => {
                const existingRemarksCell = row.querySelector('td[data-dynamic-section="remarks"]');
                if (existingRemarksCell) {
                    existingRemarksCell.remove();
                }

                const programId = row.dataset.rowId;
                const indicatorId = row.dataset.indicatorId;
                const existingRowDataByOffice = (programId && indicatorId)
                    ? (((existingAccompByIndicator[String(programId)] || {})[String(indicatorId)]) || {})
                    : {};
                const assignedOffices = getAssignedOfficesForRow(row);
                const groupBreakIndices = getInputBreakIndicesForRow(row);
                const groupPenroFlags = getInputGroupPenroFlagsForRow(row);

                const td = document.createElement('td');
                td.classList.add('p-1');
                td.dataset.dynamicSection = 'remarks';

                const officeEntries = assignedOffices.length > 0
                    ? assignedOffices
                    : [{ id: currentOfficeId || null, name: 'Office' }];

                const wrapper = buildAlignedOfficeLines({
                    officeEntries,
                    groupBreakIndices,
                    groupPenroFlags,
                    spacerFactory: () => createSpacerElement('textarea', 'remarks-box'),
                    renderOfficeInput: (office) => {
                        const officeId = Number(office?.id || 0) || null;
                        const officeData = officeId ? existingRowDataByOffice[String(officeId)] : null;

                        const input = document.createElement('textarea');
                        input.className = 'remarks-box';
                        input.placeholder = 'Add comment';
                        input.rows = 1;
                        input.value = String(officeData?.remarks || '');
                        input.dataset.section = 'remarks';
                        input.dataset.officeId = officeId ? String(officeId) : '';

                        return input;
                    }
                });

                td.appendChild(wrapper);
                row.appendChild(td);
            });

            refreshGroupHeaderColspans();
        }

        function addInputCells(sectionType) {
            // For summary, only show current quarter, this month, and annual columns
            let periodIndexes = [];
            if (sectionType === 'summary') {
                const now = new Date();
                const currentMonth = now.getMonth();
                let monthCol = PERIODS.findIndex((p, idx) => p.type === 'month' && idx === currentMonth);
                if (monthCol === -1) monthCol = 0;
                let quarterCol = 0;
                if (currentMonth <= 2) quarterCol = 3;
                else if (currentMonth <= 5) quarterCol = 7;
                else if (currentMonth <= 8) quarterCol = 11;
                else quarterCol = 15;
                let annualCol = PERIODS.length - 1;
                // Order: annual, quarter, month
                periodIndexes = [annualCol, quarterCol, monthCol];
            }
            document.querySelectorAll("tbody tr[data-row-id]").forEach(row => {
                const programId = row.dataset.rowId;
                const indicatorId = row.dataset.indicatorId;
                const sourceData = sectionType === 'target'
                    ? existingTargetsByIndicator
                    : sectionType === 'accomp'
                        ? existingAccompByIndicator
                        : existingTargetsByIndicator; // For summary, use targets as base
                const programSourceData = programId ? (sourceData[String(programId)] || {}) : {};
                const existingRowDataByOffice = indicatorId ? (programSourceData[String(indicatorId)] || {}) : {};
                const indicatorType = getIndicatorTypeForRow(row);
                const assignedOffices = getAssignedOfficesForRow(row);
                const groupBreakIndices = getInputBreakIndicesForRow(row);
                const groupPenroFlags = getInputGroupPenroFlagsForRow(row);

            if (sectionType === 'summary') {
                // Order: annual, quarter, month for targets, then annual, quarter, month for accomp
                const summaryOrder = ['annual', 'quarter', 'month'];
                const officeEntries = assignedOffices.length > 0
                    ? assignedOffices
                    : [{ id: currentOfficeId || null, name: 'Office' }];
                const summarySpacerFactory = () => createSpacerElement('input', 'month-box');
                const targetProgramData = programId ? (existingTargetsByIndicator[String(programId)] || {}) : {};
                const accompProgramData = programId ? (existingAccompByIndicator[String(programId)] || {}) : {};
                const targetDataByIndicator = indicatorId ? (targetProgramData[String(indicatorId)] || {}) : {};
                const accompDataByIndicator = indicatorId ? (accompProgramData[String(indicatorId)] || {}) : {};

                const buildSummaryInput = ({ office, officeDataSource, periodKey, periodIndex, sectionLabel }) => {
                    const officeId = Number(office?.id || 0) || null;
                    const officeData = officeId ? officeDataSource[String(officeId)] : null;
                    const input = document.createElement('input');
                    input.type = 'number';
                    input.className = 'month-box summary-box';
                    input.style.width = '100%';
                    input.value = officeData && periodKey ? (officeData[periodKey] ?? 0) : 0;
                    input.readOnly = true;
                    input.dataset.summarySection = sectionLabel;
                    input.dataset.summaryPeriodKey = periodKey || '';
                    input.dataset.summaryPeriodIndex = Number.isInteger(periodIndex) ? String(periodIndex) : '';
                    input.dataset.officeId = officeId ? String(officeId) : '';
                    return input;
                };
                // Target columns first
                summaryOrder.forEach(periodType => {
                    const idx = periodIndexes.find(i => PERIODS[i].type === periodType);
                    if (idx !== undefined && idx !== -1) {
                        const period = PERIODS[idx];
                        const periodKey = PERIOD_KEYS[idx] || null;
                        const tdTarget = document.createElement("td");
                        tdTarget.classList.add("p-1", "text-center", `dynamic-cell-${sectionType}`);
                        tdTarget.dataset.dynamicSection = sectionType;
                        tdTarget.dataset.periodType = period.type;
                        const wrapperTarget = buildAlignedOfficeLines({
                            officeEntries,
                            groupBreakIndices,
                            groupPenroFlags,
                            spacerFactory: summarySpacerFactory,
                            renderOfficeInput: (office) => {
                                return buildSummaryInput({
                                    office,
                                    officeDataSource: targetDataByIndicator,
                                    periodKey,
                                    periodIndex: idx,
                                    sectionLabel: 'target',
                                });
                            }
                        });
                        tdTarget.appendChild(wrapperTarget);
                        const remarksCell = row.querySelector('td[data-dynamic-section="remarks"]');
                        if (remarksCell) {
                            row.insertBefore(tdTarget, remarksCell);
                        } else {
                            row.appendChild(tdTarget);
                        }
                    }
                });
                // Accomplishment columns next
                summaryOrder.forEach(periodType => {
                    const idx = periodIndexes.find(i => PERIODS[i].type === periodType);
                    if (idx !== undefined && idx !== -1) {
                        const period = PERIODS[idx];
                        const periodKey = PERIOD_KEYS[idx] || null;
                        const tdAccomp = document.createElement("td");
                        tdAccomp.classList.add("p-1", "text-center", `dynamic-cell-${sectionType}`);
                        tdAccomp.dataset.dynamicSection = sectionType;
                        tdAccomp.dataset.periodType = period.type;
                        const wrapperAccomp = buildAlignedOfficeLines({
                            officeEntries,
                            groupBreakIndices,
                            groupPenroFlags,
                            spacerFactory: summarySpacerFactory,
                            renderOfficeInput: (office) => {
                                return buildSummaryInput({
                                    office,
                                    officeDataSource: accompDataByIndicator,
                                    periodKey,
                                    periodIndex: idx,
                                    sectionLabel: 'accomp',
                                });
                            }
                        });
                        tdAccomp.appendChild(wrapperAccomp);
                        const remarksCell = row.querySelector('td[data-dynamic-section="remarks"]');
                        if (remarksCell) {
                            row.insertBefore(tdAccomp, remarksCell);
                        } else {
                            row.appendChild(tdAccomp);
                        }
                    }
                });
            } else {
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

                        // For summary, always readOnly
                        if (sectionType === 'summary' || period.type !== "month") {
                            input.readOnly = true;
                            td.classList.add(
                                period.type === "quarter"
                                    ? (sectionType === "target" ? "target-total" : sectionType === 'summary' ? "quarter-total" : "quarter-total")
                                    : (sectionType === "target" ? "annual-target" : sectionType === 'summary' ? "annual-total" : "annual-total")
                            );
                        } else if (indicatorType === 'semi-cumulative') {
                            input.readOnly = period.type === 'annual';
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
            }
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
            const rowId = Number(row.dataset.rowId || 0);
            const programId = Number(row.dataset.programId || rowId || 0);
            if (!indicatorId || !programId || !rowId) return [];

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
                    row_id: rowId,
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

        function getStoredEntryByOffice(sourceByIndicator, programId, indicatorId, officeId) {
            const programKey = String(programId || '').trim();
            const indicatorKey = String(indicatorId || '').trim();
            const officeKey = String(officeId || '').trim();
            if (!programKey || !indicatorKey || !officeKey) return null;

            return sourceByIndicator?.[programKey]?.[indicatorKey]?.[officeKey] || null;
        }

        function hasAnyNonZeroPeriod(entry) {
            return PERIOD_KEYS.some(key => {
                const value = Number(entry?.[key] ?? 0);
                return Number.isFinite(value) && value !== 0;
            });
        }

        function hasPeriodDifferences(entry, storedEntry) {
            if (!storedEntry) {
                return hasAnyNonZeroPeriod(entry);
            }

            return PERIOD_KEYS.some(key => {
                const left = Number(entry?.[key] ?? 0);
                const right = Number(storedEntry?.[key] ?? 0);
                const safeLeft = Number.isFinite(left) ? left : 0;
                const safeRight = Number.isFinite(right) ? right : 0;
                return safeLeft !== safeRight;
            });
        }

        function hasEntryChanged(sectionType, entry) {
            const rowId = String(entry?.row_id || entry?.program_id || '').trim();
            const indicatorId = String(entry?.indicator_id || '').trim();
            const officeId = String(entry?.office_id || '').trim();
            if (!rowId || !indicatorId || !officeId) return false;

            const sourceByIndicator = sectionType === 'target'
                ? existingTargetsByIndicator
                : existingAccompByIndicator;
            const storedEntry = getStoredEntryByOffice(sourceByIndicator, rowId, indicatorId, officeId);

            const periodChanged = hasPeriodDifferences(entry, storedEntry);
            if (sectionType === 'target') {
                return periodChanged;
            }

            const incomingRemarks = String(entry?.remarks || '').trim();
            const storedRemarks = String(storedEntry?.remarks || '').trim();
            const remarksChanged = storedEntry
                ? incomingRemarks !== storedRemarks
                : incomingRemarks !== '';

            return periodChanged || remarksChanged;
        }

        function applySavedEntriesToExisting(sectionType, savedEntries = []) {
            if (!Array.isArray(savedEntries) || savedEntries.length === 0) return;

            const sourceByIndicator = sectionType === 'target'
                ? existingTargetsByIndicator
                : existingAccompByIndicator;

            savedEntries.forEach(entry => {
                const rowKey = String(entry?.row_id || entry?.program_id || '').trim();
                const indicatorKey = String(entry?.indicator_id || '').trim();
                const officeKey = String(entry?.office_id || '').trim();
                if (!rowKey || !indicatorKey || !officeKey) return;

                if (!sourceByIndicator[rowKey]) {
                    sourceByIndicator[rowKey] = {};
                }

                if (!sourceByIndicator[rowKey][indicatorKey]) {
                    sourceByIndicator[rowKey][indicatorKey] = {};
                }

                const normalized = {};
                PERIOD_KEYS.forEach(key => {
                    const value = Number(entry?.[key] ?? 0);
                    normalized[key] = Number.isFinite(value) ? value : 0;
                });

                if (sectionType === 'accomp') {
                    normalized.remarks = String(entry?.remarks || '').trim();
                }

                sourceByIndicator[rowKey][indicatorKey][officeKey] = {
                    ...(sourceByIndicator[rowKey][indicatorKey][officeKey] || {}),
                    ...normalized,
                };
            });
        }

        function collectChangedTargetEntries() {
            return collectSectionEntries('target').filter(entry => hasEntryChanged('target', entry));
        }

        function collectChangedAccomplishmentEntries() {
            return collectAccomplishmentEntries().filter(entry => hasEntryChanged('accomp', entry));
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
                    const rowId = Number(row.dataset.rowId || 0);
                    const programId = Number(row.dataset.programId || rowId || 0);
                    if (!indicatorId || !programId || !rowId) return [];

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

                    const existingByOffice = ((existingAccompByIndicator[String(rowId)] || {})[String(indicatorId)]) || {};

                    return Array.from(officeIds).map(officeId => {
                        const entry = {
                            program_id: programId,
                            row_id: rowId,
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
                precomputedEntries = null,
            } = options;

            const isTarget = sectionType === 'target';
            const shouldBeVisible = isTarget ? targetsVisible : (accompVisible || remarksVisible);

            if (requireVisible && !shouldBeVisible) {
                if (showAlerts) {
                    showTopRightErrorAlert(`Please open ${isTarget ? 'Targets' : 'Accomplishments'} first before saving.`);
                }
                return { success: false, skipped: true, message: 'Section is not visible.' };
            }

            const entries = Array.isArray(precomputedEntries)
                ? precomputedEntries
                : (isTarget
                    ? collectChangedTargetEntries()
                    : collectChangedAccomplishmentEntries());
            if (entries.length === 0) {
                return { success: true, skipped: true, message: 'No rows to save.' };
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

                applySavedEntriesToExisting(isTarget ? 'target' : 'accomp', entries);

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
            const saveAllBtn = document.getElementById('saveAllBtn');
            const originalSaveBtnHtml = saveAllBtn ? saveAllBtn.innerHTML : '';

            const targetEntries = collectChangedTargetEntries();
            const accompEntries = collectChangedAccomplishmentEntries();

            if (targetEntries.length === 0 && accompEntries.length === 0) {
                showTopRightErrorAlert('No input rows available to save.');
                return;
            }

            if (saveAllBtn) {
                saveAllBtn.disabled = true;
                saveAllBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Saving...';
            }

            try {
                const [targetResult, accompResult] = await Promise.all([
                    saveSectionEntries('target', {
                        requireVisible: false,
                        showAlerts: false,
                        precomputedEntries: targetEntries,
                    }),
                    saveSectionEntries('accomp', {
                        requireVisible: false,
                        showAlerts: false,
                        precomputedEntries: accompEntries,
                    }),
                ]);

                const results = [targetResult, accompResult];
                const failed = results.filter(result => !result.success);
                if (failed.length > 0) {
                    showTopRightErrorAlert('Some entries failed to save. Please try again.');
                    return;
                }

                if (results.every(result => result.skipped)) {
                    showTopRightErrorAlert('No input rows available to save.');
                    return;
                }

                showTopRightSuccessAlert('Data saved successfully.');
            } finally {
                if (saveAllBtn) {
                    saveAllBtn.disabled = false;
                    saveAllBtn.innerHTML = originalSaveBtnHtml;
                }
            }
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
            const sourceInputs = sectionInputs.filter(input => input.dataset.carTotal !== '1' && input.dataset.groupTotal !== '1');
            const groupInputs = sectionInputs.filter(input => input.dataset.groupTotal === '1');
            const carInputs = sectionInputs.filter(input => input.dataset.carTotal === '1');
            if (carInputs.length === 0 && groupInputs.length === 0) return;

            const monthColIndices = [0, 1, 2, 4, 5, 6, 8, 9, 10, 12, 13, 14];
            const quarterColIndices = [3, 7, 11, 15];

            const getValuesForCol = (colIndex, officeSet = null) => {
                return sourceInputs
                    .filter(input => Number(input.dataset.col) === colIndex)
                    .filter(input => !officeSet || officeSet.has(String(input.dataset.officeId || '')))
                    .map(input => {
                        const value = Number(input.value);
                        return Number.isFinite(value) ? value : 0;
                    });
            };

            const aggregateValues = (values) => {
                if (values.length === 0) return 0;

                if (indicatorType === 'non-cumulative') {
                    return values.reduce((sum, value) => sum + value, 0);
                }

                return values.reduce((sum, value) => sum + value, 0);
            };

            const buildComputedTotals = (officeSet = null) => {
                const totals = {};

                monthColIndices.forEach(colIndex => {
                    totals[colIndex] = aggregateValues(getValuesForCol(colIndex, officeSet));
                });

                if (indicatorType === 'semi-cumulative') {
                    totals[3] = (totals[0] || 0) + (totals[1] || 0) + (totals[2] || 0);
                    totals[7] = (totals[4] || 0) + (totals[5] || 0) + (totals[6] || 0);
                    totals[11] = (totals[8] || 0) + (totals[9] || 0) + (totals[10] || 0);
                    totals[15] = (totals[12] || 0) + (totals[13] || 0) + (totals[14] || 0);
                    totals[16] = totals[3] + totals[7] + totals[11] + totals[15];
                    return totals;
                }

                const q1 = indicatorType === 'non-cumulative'
                    ? Math.max(totals[0] || 0, totals[1] || 0, totals[2] || 0)
                    : (totals[0] || 0) + (totals[1] || 0) + (totals[2] || 0);

                const q2 = indicatorType === 'non-cumulative'
                    ? Math.max(totals[4] || 0, totals[5] || 0, totals[6] || 0)
                    : (totals[4] || 0) + (totals[5] || 0) + (totals[6] || 0);

                const q3 = indicatorType === 'non-cumulative'
                    ? Math.max(totals[8] || 0, totals[9] || 0, totals[10] || 0)
                    : (totals[8] || 0) + (totals[9] || 0) + (totals[10] || 0);

                const q4 = indicatorType === 'non-cumulative'
                    ? Math.max(totals[12] || 0, totals[13] || 0, totals[14] || 0)
                    : (totals[12] || 0) + (totals[13] || 0) + (totals[14] || 0);

                totals[3] = q1;
                totals[7] = q2;
                totals[11] = q3;
                totals[15] = q4;
                totals[16] = indicatorType === 'non-cumulative'
                    ? Math.max(q1, q2, q3, q4)
                    : q1 + q2 + q3 + q4;

                return totals;
            };

            const applyTotalsToInputs = (inputs, totals) => {
                inputs.forEach(input => {
                    const colIndex = Number(input.dataset.col);
                    if (!Number.isInteger(colIndex)) return;

                    if (Object.prototype.hasOwnProperty.call(totals, colIndex)) {
                        input.value = totals[colIndex];
                    }
                });
            };

            const groupedInputsByKey = groupInputs.reduce((acc, input) => {
                const key = String(input.dataset.groupKey || '').trim();
                if (!key) return acc;
                if (!acc[key]) acc[key] = [];
                acc[key].push(input);
                return acc;
            }, {});

            Object.values(groupedInputsByKey).forEach(inputs => {
                const officeSet = new Set(
                    String(inputs[0]?.dataset?.groupOfficeIds || '')
                        .split(',')
                        .map(value => value.trim())
                        .filter(Boolean)
                );

                const totals = buildComputedTotals(officeSet);
                applyTotalsToInputs(inputs, totals);
            });

            const carTotals = buildComputedTotals(null);
            applyTotalsToInputs(carInputs, carTotals);
        }

        function syncMonthValueAcrossCoreRows(sourceInput) {
            const sourceRow = sourceInput.closest('tr[data-sync-key]');
            const syncKey = String(sourceRow?.dataset?.syncKey || '').trim();
            if (!syncKey) return [];

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

                const candidateRow = candidate.closest('tr[data-sync-key]');
                if (!candidateRow) return;
                if (String(candidateRow.dataset.syncKey || '').trim() !== syncKey) return;

                candidate.value = sourceInput.value;
                touchedRows.add(candidateRow);
            });

            return Array.from(touchedRows);
        }

        function getIndicatorTypeForRow(row) {
            const rawType = String(row?.dataset?.indicatorType || '')
                .trim()
                .toLowerCase()
                .replace(/[_\s]+/g, '-')
                .replace(/-+/g, '-');

            if (rawType === 'semi-cumulative' || rawType === 'semi-comulative' || rawType === 'semicumulative') {
                return 'semi-cumulative';
            }

            if (rawType === 'non-cumulative' || rawType === 'non-comulative' || rawType === 'noncumulative') {
                return 'non-cumulative';
            }

            return 'cumulative';
        }

        function getSectionColInput(allInputs, section, colIndex, officeId = null) {
            return Array.from(allInputs).find(i =>
                i.dataset.section === section
                && Number(i.dataset.col) === colIndex
                && String(i.dataset.officeId || '') === String(officeId || '')
            ) || null;
        }

        function updateSection(monthInputs, allInputs, section, indicatorType = 'cumulative', officeId = null) {
            const values = monthInputs.map(inp => Number(inp.value) || 0);

            let q1 = 0;
            let q2 = 0;
            let q3 = 0;
            let q4 = 0;
            let annual = 0;

            if (indicatorType === 'non-cumulative') {
                q1 = Math.max(values[0] || 0, values[1] || 0, values[2] || 0);
                q2 = Math.max(values[3] || 0, values[4] || 0, values[5] || 0);
                q3 = Math.max(values[6] || 0, values[7] || 0, values[8] || 0);
                q4 = Math.max(values[9] || 0, values[10] || 0, values[11] || 0);
                annual = Math.max(q1, q2, q3, q4);
            } else if (indicatorType === 'semi-cumulative') {
                q1 = values[0] + values[1] + values[2];
                q2 = values[3] + values[4] + values[5];
                q3 = values[6] + values[7] + values[8];
                q4 = values[9] + values[10] + values[11];
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
            const papSubSubactivitiesInput = document.getElementById('pap_subsubactivities');
            const papLevel6Input = document.getElementById('pap_level_6');
            const papLevel7Input = document.getElementById('pap_level_7');
            const papLevel8Input = document.getElementById('pap_level_8');

            if (programInput) programInput.value = String(matchedPap.program || '');
            if (projectInput) projectInput.value = String(matchedPap.project || '');
            if (papActivitiesInput) papActivitiesInput.value = String(matchedPap.activities || '');
            if (papSubactivitiesInput) papSubactivitiesInput.value = String(matchedPap.subactivities || '');
            if (papSubSubactivitiesInput) papSubSubactivitiesInput.value = String(matchedPap.subsubactivities || '');
            
            // Show and populate dynamic levels if they exist
            if (matchedPap.level_6) {
                showNextPapLevel(6);
                if (papLevel6Input) papLevel6Input.value = String(matchedPap.level_6);
            }
            if (matchedPap.level_7) {
                showNextPapLevel(7);
                if (papLevel7Input) papLevel7Input.value = String(matchedPap.level_7);
            }
            if (matchedPap.level_8) {
                showNextPapLevel(8);
                if (papLevel8Input) papLevel8Input.value = String(matchedPap.level_8);
            }

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
            const subsubactivities = normalizePapField(document.getElementById('pap_subsubactivities')?.value);
            const level6 = normalizePapField(document.getElementById('pap_level_6')?.value);
            const level7 = normalizePapField(document.getElementById('pap_level_7')?.value);
            const level8 = normalizePapField(document.getElementById('pap_level_8')?.value);

            if (!title && !program && !project && !activities && !subactivities && !subsubactivities && !level6 && !level7 && !level8) {
                return null;
            }

            return (papPrefillData || []).find(item =>
                normalizePapField(item?.title) === title
                && normalizePapField(item?.program) === program
                && normalizePapField(item?.project) === project
                && normalizePapField(item?.activities) === activities
                && normalizePapField(item?.subactivities) === subactivities
                && normalizePapField(item?.subsubactivities) === subsubactivities
                && normalizePapField(item?.level_6) === level6
                && normalizePapField(item?.level_7) === level7
                && normalizePapField(item?.level_8) === level8
            ) || null;
        }

        function getSelectedIndicatorFromPapMatch(matchedPap) {
            const indicatorNameInput = document.getElementById('modal_indicator_name');
            const normalizedIndicatorName = normalizePapField(indicatorNameInput?.value);
            const hasTypedIndicatorName = normalizedIndicatorName !== '';

            if (!matchedPap || !Array.isArray(matchedPap.indicators) || matchedPap.indicators.length === 0) {
                return null;
            }

            return hasTypedIndicatorName
                ? (matchedPap.indicators.find(i => normalizePapField(i?.name) === normalizedIndicatorName) || null)
                : (matchedPap.indicators.find(i => String(i?.name || '').trim() !== '') || matchedPap.indicators[0] || null);
        }

        function applyModalPrefillFromExistingPap() {
            const matchedPap = findMatchingPapFromModal();
            const indicatorIdInput = document.getElementById('indicator_id');
            const indicatorNameInput = document.getElementById('modal_indicator_name');
            const indicatorTypeInput = document.getElementById('modal_indicator_type');
            const indicatorTypeToggle = document.getElementById('use_indicator_type');
            const normalizedIndicatorName = normalizePapField(indicatorNameInput?.value);
            const hasTypedIndicatorName = normalizedIndicatorName !== '';

            if (!matchedPap || !Array.isArray(matchedPap.indicators) || matchedPap.indicators.length === 0) {
                if (indicatorIdInput) {
                    indicatorIdInput.value = '';
                    delete indicatorIdInput.dataset.rowId;
                }
                if (!hasTypedIndicatorName && indicatorNameInput) {
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

            const selectedIndicator = getSelectedIndicatorFromPapMatch(matchedPap);

            if (!selectedIndicator) {
                const hadLinkedIndicator = Boolean(String(indicatorIdInput?.value || '').trim());

                if (indicatorIdInput) {
                    indicatorIdInput.value = '';
                    delete indicatorIdInput.dataset.rowId;
                }

                if (hadLinkedIndicator) {
                    if (indicatorTypeInput) {
                        indicatorTypeInput.value = '';
                    }
                    if (indicatorTypeToggle) {
                        indicatorTypeToggle.checked = false;
                    }
                    toggleIndicatorTypeDropdown();
                    setOfficeCheckboxes([]);
                }

                return;
            }

            if (indicatorNameInput) {
                indicatorNameInput.value = String(selectedIndicator.name || '').trim();
            }

            if (indicatorTypeInput) {
                indicatorTypeInput.value = String(selectedIndicator.indicator_type_id || '').trim();
            }

            if (indicatorTypeToggle) {
                indicatorTypeToggle.checked = Boolean(indicatorTypeInput?.value);
            }

            toggleIndicatorTypeDropdown();

            if (indicatorIdInput) {
                indicatorIdInput.value = String(selectedIndicator.id || '').trim();
                indicatorIdInput.dataset.rowId = String(selectedIndicator.row_id || matchedPap?.row_id || '').trim();
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

            const indicatorNameField = document.getElementById('modal_indicator_name');
            if (indicatorNameField) {
                indicatorNameField.addEventListener('input', function () {
                    clearTimeout(modalPrefillTimer);
                    modalPrefillTimer = setTimeout(() => {
                        applyModalPrefillFromExistingPap();
                    }, 180);
                });

                indicatorNameField.addEventListener('change', function () {
                    clearTimeout(modalPrefillTimer);
                    applyModalPrefillFromExistingPap();
                });
            }

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
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
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

                <form id="addIndicatorForm" method="POST" action="{{ route('admin.gass_physical.indicators.store') }}"
                    data-update-route-template="{{ route('admin.gass_physical.indicators.update', ':id') }}"
                    data-delete-route-template="{{ route('admin.gass_physical.indicators.destroy', ':id') }}">
                    @csrf
                    <input type="hidden" id="indicator_id" name="indicator_id" value="">

                    <div class="modal-body">
                        <h4
                            class="font-extrabold text-2xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent pb-1 border-b-2 border-blue-200 inline-block">
                            P/A/P
                        </h4>
                        <div class="row g-3 mb-2">
                            <div class="col-12 col-md-6">
                                <label for="pap_title" class="form-label fw-bold small">Title</label>
                                <input type="text" id="pap_title" class="form-control form-control-sm py-2"
                                    style="font-size: 0.875rem;" list="pap_title_options" required>
                                <datalist id="pap_title_options">
                                    @foreach(($papTitles ?? []) as $existingTitle)
                                        <option value="{{ $existingTitle }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="pap_year" class="form-label fw-bold small">Year</label>
                                <input type="number" id="pap_year" class="form-control form-control-sm py-2"
                                    style="font-size: 0.875rem;" min="2000" max="2099" placeholder="YYYY">
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="pap_program" class="form-label fw-bold small">Program <label class="text-red-500 text-[10px]">(N/A if not applicable)</label></label>
                                <input type="text" id="pap_program" class="form-control form-control-sm py-2"
                                    style="font-size: 0.875rem;" required>
                            </div>

                            <div class="col-12">
                                <label for="pap_project" class="form-label fw-bold small">Project <label class="text-red-500 text-[10px]">(N/A if not applicable)</label></label>
                                <input type="text" id="pap_project" class="form-control form-control-sm py-2"
                                    list="pap_project_options" style="font-size: 0.875rem;" required>
                                <datalist id="pap_project_options">
                                    @foreach(($papProjects ?? []) as $existingProject)
                                        <option value="{{ $existingProject }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pap_activities" class="form-label fw-bold small">Activity</label>
                                <input type="text" id="pap_activities" class="form-control form-control-sm py-2"
                                    style="font-size: 0.875rem;" list="pap_activity_options">
                                <datalist id="pap_activity_options">
                                    @foreach(($papActivities ?? []) as $existingActivity)
                                        <option value="{{ $existingActivity }}"></option>
                                    @endforeach
                                </datalist>
                            </div> 

                            <div class="col-12 col-md-6">
                                <label for="pap_subactivities" class="form-label fw-bold small">Sub-activity</label>
                                <input type="text" id="pap_subactivities" class="form-control form-control-sm py-2"
                                    style="font-size: 0.875rem;" list="pap_subactivity_options" maxlength="255">
                                <datalist id="pap_subactivity_options">
                                    @foreach(($papSubactivities ?? []) as $existingSubactivity)
                                        <option value="{{ $existingSubactivity }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="pap_subsubactivities" class="form-label fw-bold small">Sub-Sub-activity</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="pap_subsubactivities" class="form-control form-control-sm py-2"
                                        style="font-size: 0.875rem;" list="pap_subsubactivity_options" maxlength="255">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add_level_6_btn" 
                                        onclick="showNextPapLevel(6)" title="Add Sub-Sub-Sub-activity">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                                <datalist id="pap_subsubactivity_options">
                                    @foreach(($papSubSubactivities ?? []) as $existingSubSubactivity)
                                        <option value="{{ $existingSubSubactivity }}"></option>
                                    @endforeach
                                </datalist>
                            </div>

                            <!-- Level 6: Sub-Sub-Sub-activity -->
                            <div class="col-12 col-md-6" id="pap_level_6_container" style="display: none;">
                                <label for="pap_level_6" class="form-label fw-bold small">Sub-Sub-Sub-activity</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="pap_level_6" class="form-control form-control-sm py-2"
                                        style="font-size: 0.875rem;" maxlength="255">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add_level_7_btn" 
                                        onclick="showNextPapLevel(7)" title="Add Sub-Sub-Sub-Sub-activity">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
                            </div>

                            <!-- Level 7: Sub-Sub-Sub-Sub-activity -->
                            <div class="col-12 col-md-6" id="pap_level_7_container" style="display: none;">
                                <label for="pap_level_7" class="form-label fw-bold small">Sub-Sub-Sub-Sub-activity</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" id="pap_level_7" class="form-control form-control-sm py-2"
                                        style="font-size: 0.875rem;" maxlength="255">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="add_level_8_btn" 
                                        onclick="showNextPapLevel(8)" title="Add Sub-Sub-Sub-Sub-Sub-activity">
                                        <i class="bi bi-plus-lg"></i>
                                    </button>
                                </div>
            </div>

                            <!-- Level 8: Sub-Sub-Sub-Sub-Sub-activity -->
                            <div class="col-12 col-md-6" id="pap_level_8_container" style="display: none;">
                                <label for="pap_level_8" class="form-label fw-bold small">Sub-Sub-Sub-Sub-Sub-activity</label>
                                <input type="text" id="pap_level_8" class="form-control form-control-sm py-2"
                                    style="font-size: 0.875rem;" maxlength="255">
                            </div>

                        </div>
                        <h4
                            class="font-extrabold text-2xl bg-gradient-to-r from-blue-600 to-indigo-600 bg-clip-text text-transparent pb-1 border-b-2 border-blue-200 inline-block">
                            Indicator
                        </h4>
                        <!-- Performance Indicator -->
                        <div class="mb-2">
                            <label for="modal_indicator_name" class="form-label fw-bold">Performance Indicator <label class="text-red-500 text-[10px]">(N/A if not applicable)</label></label>
                            <textarea type="text" name="indicator_name" id="modal_indicator_name"
                                class="form-control form-control-lg" placeholder="Input the performance indicator"
                                required></textarea>
                            @error('indicator_name')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="use_indicator_type"
                                style="border-color: #000;">
                            <label class="form-check-label fw-bold" for="use_indicator_type">Choose indicator
                                type</label>
                        </div>

                        <div class="mb-2" id="indicator_type_wrapper" style="display: none;">
                            <label for="modal_indicator_type" class="form-label fw-bold">Type of Indicator</label>
                            <select name="indicator_type_id" id="modal_indicator_type"
                                class="form-control form-control-lg">
                                <option value="">-- Select Type --</option>
                                @foreach(($indicatorTypeOptions ?? []) as $typeOption)
                                    <option value="{{ data_get($typeOption, 'id') }}">{{ data_get($typeOption, 'name') }}
                                    </option>
                                @endforeach
                            </select>
                            @error('indicator_type_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Office -->
                        <div>
                            <label class="form-label fw-bold">Office / Unit</label>

                            <div class="mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input black-checkbox" type="checkbox" id="selectAllPenroRo">
                                    <label class="form-check-label fw-bold" for="selectAllPenroRo">Select All PENROs & RO</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input black-checkbox" type="checkbox" id="selectAllCenro">
                                    <label class="form-check-label fw-bold" for="selectAllCenro">Select All CENROs</label>
                                </div>
                            </div>

                            <div>
                                <div class="row row-cols-1 row-cols-md-3">
                                    @forelse(($offices ?? []) as $parent)
                                        <div class="col">
                                            <div class="form-check">
                                                <input class="form-check-input office-checkbox black-checkbox"
                                                    type="checkbox" value="{{ $parent->id }}" id="office_{{ $parent->id }}"
                                                    name="office_id[]">
                                                <label class="form-check-label" for="office_{{ $parent->id }}">
                                                    @php
                                                        $penroNames = ['benguet', 'ifugao', 'mt.province', 'apayao', 'abra', 'kalinga', 'ro'];
                                                        $isPenro = stripos($parent->name, 'PENRO') !== false;
                                                        foreach ($penroNames as $penro) {
                                                            if (stripos($parent->name, $penro) !== false) {
                                                                $isPenro = true;
                                                                break;
                                                            }
                                                        }
                                                    @endphp
                                                    @if($isPenro)
                                                        <strong>{{ $parent->name }}</strong>
                                                    @else
                                                        {{ $parent->name }}
                                                    @endif
                                                </label>
                                            </div>

                                            @foreach(($parent->children ?? []) as $child)
                                                <div class="form-check">
                                                    <input class="form-check-input office-checkbox black-checkbox"
                                                        type="checkbox" value="{{ $child->id }}" id="office_{{ $child->id }}"
                                                        name="office_id[]">
                                                    <label class="form-check-label" for="office_{{ $child->id }}">
                                                        @php
                                                            $penroNames = ['benguet', 'ifugao', 'mt.province', 'apayao', 'abra', 'kalinga'];
                                                            $isPenro = stripos($child->name, 'PENRO') !== false;
                                                            foreach ($penroNames as $penro) {
                                                                if (stripos($child->name, $penro) !== false) {
                                                                    $isPenro = true;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp
                                                        @if($isPenro)
                                                            <strong>{{ $child->name }}</strong>
                                                        @else
                                                            {{ $child->name }}
                                                        @endif
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
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function showNextPapLevel(level) {
            const container = document.getElementById(`pap_level_${level}_container`);
            const button = document.getElementById(`add_level_${level}_btn`);
            
            if (container) {
                container.style.display = '';
                if (button) {
                    button.style.display = 'none'; // Hide the button that was clicked
                }
            }
        }

        // Function to reset PAP levels (hide all dynamic levels)
        function resetPapLevels() {
            for (let level = 6; level <= 8; level++) {
                const container = document.getElementById(`pap_level_${level}_container`);
                const input = document.getElementById(`pap_level_${level}`);
                const button = document.getElementById(`add_level_${level}_btn`);
                
                if (container) container.style.display = 'none';
                if (input) input.value = '';
                if (button) button.style.display = '';
            }
        }

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
            const papSubSubactivities = document.getElementById('pap_subsubactivities')?.value?.trim() || '';
            const papLevel6 = document.getElementById('pap_level_6')?.value?.trim() || '';
            const papLevel7 = document.getElementById('pap_level_7')?.value?.trim() || '';
            const papLevel8 = document.getElementById('pap_level_8')?.value?.trim() || '';
            const papYear = document.getElementById('pap_year')?.value?.trim() || '';
            const indicatorId = String(document.getElementById('indicator_id')?.value || '').trim();

            const indicatorName = document.getElementById('modal_indicator_name').value.trim();
            const indicatorTypeToggle = document.getElementById('use_indicator_type');
            const indicatorTypeId = (indicatorTypeToggle?.checked ? document.getElementById('modal_indicator_type').value : '').trim();

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
                const matchedIndicator = getSelectedIndicatorFromPapMatch(matchedPap);
                const indicatorIdInput = document.getElementById('indicator_id');
                const selectedIndicatorRowId = String(matchedIndicator?.row_id || indicatorIdInput?.dataset?.rowId || '').trim();
                let programId = matchedPap?.id ? String(matchedPap.id) : '';
                let rowId = selectedIndicatorRowId || (matchedPap?.row_id ? String(matchedPap.row_id) : '');

                // Always create/update PAP (including year) when PAP fields are filled
                if (papTitle) {
                    const papFormData = new FormData();
                    papFormData.append('_token', token);
                    papFormData.append('title', papTitle);
                    papFormData.append('program', papProgram);
                    papFormData.append('project', papProject);
                    papFormData.append('activities', papActivities);
                    papFormData.append('subactivities', papSubactivities);
                    papFormData.append('subsubactivities', papSubSubactivities);
                    papFormData.append('level_6', papLevel6);
                    papFormData.append('level_7', papLevel7);
                    papFormData.append('level_8', papLevel8);
                    if (papYear) {
                        papFormData.append('year', papYear);
                    }

                    const papResponse = await fetch(@json(route('admin.gass_physical.pap.store')), {
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
                    rowId = String(papData?.pap?.row_id || papData?.pap?.id || papData.pap.id);
                }

                const formData = new FormData();
                formData.append('_token', token);
                formData.append('program_id', programId);
                if (rowId) {
                    formData.append('row_id', rowId);
                }
                formData.append('indicator_name', indicatorName);
                if (indicatorTypeId) {
                    formData.append('indicator_type_id', indicatorTypeId);
                }
                selectedOffices.forEach(officeId => formData.append('office_id[]', officeId));

                const updateRouteTemplate = form.dataset.updateRouteTemplate || '';
                const shouldUpdateExistingIndicator = Boolean(
                    indicatorId
                    && programId
                    && rowId
                    && matchedIndicator
                    && String(matchedIndicator.id || '') === indicatorId
                );

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
                resetPapLevels(); // Reset dynamic PAP levels
                const indicatorIdField = document.getElementById('indicator_id');
                if (indicatorIdField) {
                    indicatorIdField.value = '';
                    delete indicatorIdField.dataset.rowId;
                }
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
                const applyStickyHeaderOffsets = () => {
                    const appHeader = document.getElementById('appHeader');
                    const headerRow = document.querySelector('#performanceTable thead tr:not(.group-row)');

                    const stickyTop = appHeader ? appHeader.getBoundingClientRect().height : 0;
                    const firstHeaderHeight = headerRow ? headerRow.getBoundingClientRect().height : 46;

                    document.documentElement.style.setProperty('--table-sticky-top', `${Math.round(stickyTop)}px`);
                    document.documentElement.style.setProperty('--table-header-row-height', `${Math.round(firstHeaderHeight)}px`);
                };

                applyStickyHeaderOffsets();
                window.addEventListener('resize', applyStickyHeaderOffsets);
            });

            refreshSummaryCards();
        }

        // Delete confirmation – runs immediately, outside any conditional block
        (function () {
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

                if (typeof form.requestSubmit === 'function') {
                    form.requestSubmit();
                } else {
                    form.submit();
                }
            });
        })();
    </script>

</body>

</html>

