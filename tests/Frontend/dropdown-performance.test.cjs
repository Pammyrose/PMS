const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

test('Hiding Summary removes program financial totals while preserving other visible sections', () => {
    const source = fs.readFileSync('resources/views/components/financial_input_persistence.blade.php', 'utf8');
    const start = source.indexOf('    window.toggleSummaryColumns = function () {');
    const end = source.indexOf('    const entryChanged =', start);
    assert.ok(start >= 0 && end > start);
    const cells = [];
    const addCell = (section, row) => {
        const cell = { section, row, removed: false, remove() { this.removed = true; } };
        cells.push(cell);
        return cell;
    };
    const financial = addCell('financial', 'indicator');
    const accomplishment = addCell('accomp', 'indicator');
    const addSummaryCells = () => {
        addCell('summary', 'indicator');
        addCell('summary', 'default-office');
    };
    addSummaryCells();
    let groupVisible = true;
    const group = { remove() { groupVisible = false; } };
    const groupRow = {
        querySelectorAll: () => groupVisible ? [group] : [],
        replaceChildren() { groupVisible = false; },
    };
    const button = { innerHTML: 'Hide Summary', classList: { replace() {} } };
    const table = {
        querySelector: () => ({}),
        querySelectorAll: selector => {
            assert.equal(selector, 'tbody td[data-dynamic-section="summary"]');
            return cells.filter(cell => cell.section === 'summary' && !cell.removed);
        },
    };
    const context = {
        window: {}, summaryVisible: true, targetsVisible: false, financialVisible: true,
        accompVisible: true, pendingVisible: false, monthInputsVisible: false,
        document: { getElementById: id => ({ performanceTable: table, groupHeaders: groupRow, summaryBtn: button })[id] },
        removePhysicalPerformanceTitleRow() {}, showPhysicalPerformanceTitleRow() {},
        addSummaryHeaders() { groupVisible = true; }, addSummaryCells,
        removeSectionColumns() {
            cells.filter(cell => cell.section === 'summary' && cell.row === 'indicator').forEach(cell => cell.remove());
        },
        refreshMonthButtonState() {}, refreshSummaryCards() {},
    };
    vm.runInNewContext(source.slice(start, end), context);

    for (let cycle = 0; cycle < 3; cycle++) {
        context.window.toggleSummaryColumns();
        assert.equal(context.summaryVisible, false);
        assert.equal(cells.filter(cell => cell.section === 'summary' && !cell.removed).length, 0);
        assert.equal(financial.removed, false);
        assert.equal(accomplishment.removed, false);
        assert.match(button.innerHTML, /> Summary$/);
        context.window.toggleSummaryColumns();
        assert.equal(context.summaryVisible, true);
        assert.equal(cells.filter(cell => cell.section === 'summary' && !cell.removed).length, 2);
    }
});

test('Repeated viewport initialization keeps one financial summary per program row', () => {
    const source = fs.readFileSync('resources/views/components/financial_input_persistence.blade.php', 'utf8');
    const start = source.indexOf('    const addDefaultFinancialSummaryCells = () => {');
    const end = source.indexOf('    const refreshDefaultFinancialSummaryInputs =', start);
    assert.ok(start >= 0 && end > start);

    const node = () => ({
        children: [], dataset: {}, classList: { add() {} },
        appendChild(child) { this.children.push(child); },
        setAttribute() {},
    });
    const programRow = () => ({
        ...node(),
        querySelector() { return this.children.find(cell => cell.dataset.dynamicSection === 'summary') || null; },
        querySelectorAll() { return ['CAR', 'RO', 'ABRA'].map(unit => ({ dataset: { defaultOfficeUnit: unit } })); },
    });
    const firstRow = programRow();
    const rows = [firstRow];
    const sections = [
        { key: 'physical-target', periods: [{ key: 'annual' }, { key: 'to-date' }] },
        { key: 'financial-target', periods: [{ key: 'annual' }, { key: 'quarter' }, { key: 'to-date' }] },
        { key: 'financial-accomplishment', periods: [{ key: 'quarter' }, { key: 'to-date' }] },
    ];
    const context = {
        document: { querySelectorAll: () => rows, createElement: node },
        summarySections: sections,
        summaryPeriodsForSection: section => section.periods,
        createDefaultFinancialSummaryInput: () => ({ value: '0' }),
        insertBeforeRemarks: (row, cell) => row.appendChild(cell),
    };
    vm.runInNewContext(source.slice(start, end) + '\nthis.initialize = addDefaultFinancialSummaryCells;', context);

    context.initialize();
    assert.equal(firstRow.children.length, 7);
    const financialInput = firstRow.children[2].children[0].children[0].children[0];
    financialInput.value = '3092';
    for (let scroll = 0; scroll < 10; scroll++) context.initialize();
    assert.equal(firstRow.children.length, 7, 'Scrolling must not append duplicate columns');
    assert.equal(financialInput.value, '3092', 'Existing totals must stay in place');

    const nextRow = programRow();
    rows.push(nextRow);
    context.initialize();
    assert.equal(firstRow.children.length, 7);
    assert.equal(nextRow.children.length, 7, 'New program rows still need their summary columns');

    firstRow.children = [];
    context.initialize();
    assert.equal(firstRow.children.length, 7, 'Summary columns can be rebuilt after toggling them off');
});

function dashboardHarness() {
    const handlers = {};
    const windowHandlers = {};
    const timers = new Map();
    const requests = [];
    const updates = [];
    const history = [];
    const status = {};
    const attributes = new Map();
    const form = { action: 'https://pms.test/dashboard', values: [['year', '2026'], ['sector', 'gass']] };
    const main = {
        addEventListener: (name, handler) => { handlers[name] = handler; },
        querySelectorAll: () => [],
        setAttribute: (key, value) => attributes.set(key, value),
        removeAttribute: key => attributes.delete(key),
        replaceChildren: content => updates.push(content.html),
    };
    const window = {
        location: { href: form.action, assign() { throw new Error('Unexpected page navigation'); } },
        history: { pushState: (_state, _title, url) => history.push(url) },
        addEventListener: (name, handler) => { windowHandlers[name] = handler; },
        initializeDashboardPerformance() {}, initializeDashboardTrend() {},
    };
    let timerId = 0;
    vm.runInNewContext(fs.readFileSync('public/js/dashboard-filters.js', 'utf8'), {
        window, URL, URLSearchParams, AbortController,
        console: { warn() {} },
        FormData: class { constructor(value) { return value.values; } },
        setTimeout: callback => { timers.set(++timerId, callback); return timerId; },
        clearTimeout: id => timers.delete(id),
        fetch: (url, options) => new Promise(resolve => requests.push({ url, options, resolve })),
        document: {
            querySelector: () => main,
            getElementById: id => id === 'dashboardFilterStatus' ? status : form,
            createElement: () => {
                const content = { querySelector: () => form };
                return { content, set innerHTML(value) { content.html = value; } };
            },
        },
    });
    return {
        requests, updates, history, status, attributes, windowHandlers, handlers,
        change(sector) {
            form.values[1][1] = sector;
            handlers.change({ target: { closest: () => form, matches: () => true } });
        },
        tick() { const pending = [...timers.values()]; timers.clear(); pending.forEach(callback => callback()); },
    };
}

const flush = () => new Promise(resolve => setImmediate(resolve));
const response = html => ({ ok: true, redirected: false, text: async () => html });

test('Dashboard details load on opening, reuse results, retry failures, and ignore removed modals', async () => {
    const app = dashboardHarness();
    const body = {
        dataset: { dashboardList: 'indicator' }, isConnected: true,
        setAttribute() {}, removeAttribute() {},
    };
    const open = () => app.handlers['show.bs.modal']({ target: { querySelector: () => body } });
    assert.equal(app.requests.length, 0);
    open(); open();
    assert.equal(app.requests.length, 1, 'Repeated opens must share the in-flight request');
    assert.equal(app.requests[0].options.headers['X-Dashboard-List'], 'indicator');
    assert.equal(new URL(app.requests[0].url).searchParams.get('year'), '2026');
    app.requests[0].resolve({ ok: false });
    await flush();
    assert.match(body.innerHTML, /Retry/);
    open();
    app.requests[1].resolve(response('Scoped indicators'));
    await flush();
    assert.equal(body.innerHTML, 'Scoped indicators');
    open();
    assert.equal(app.requests.length, 2, 'Loaded records must be reused until filters replace the modal');
    body.dataset.loaded = 'false';
    open();
    body.isConnected = false;
    app.requests[2].resolve(response('Stale indicators'));
    await flush();
    assert.doesNotMatch(body.innerHTML, /Stale/);
});

test('Rapid dashboard filter selections make one partial request and keep the page in place', async () => {
    const app = dashboardHarness();
    app.change('gass');
    app.change('sto');
    app.tick();
    assert.equal(app.requests.length, 1);
    assert.equal(new URL(app.requests[0].url).searchParams.get('sector'), 'sto');
    assert.equal(app.requests[0].options.headers['X-Dashboard-Partial'], '1');
    app.requests[0].resolve(response('STO results'));
    await flush();
    assert.deepEqual(app.updates, ['STO results']);
    assert.equal(app.history.length, 1);
    assert.equal(app.attributes.has('aria-busy'), false);
});

test('Older dashboard responses cannot replace newer results, and failures preserve visible data', async () => {
    const app = dashboardHarness();
    app.change('gass'); app.tick();
    app.change('sto'); app.tick();
    assert.equal(app.requests[0].options.signal.aborted, true);
    app.requests[1].resolve(response('Newest results'));
    await flush();
    app.requests[0].resolve(response('Old results'));
    await flush();
    assert.deepEqual(app.updates, ['Newest results']);
    app.windowHandlers.popstate(); app.tick();
    app.requests[2].resolve(response('Back results'));
    await flush();
    assert.equal(app.history.length, 1, 'Back navigation must not create a new history entry.');
    app.change('enf'); app.tick();
    app.requests[3].resolve({ ok: false, status: 500 });
    await flush();
    assert.deepEqual(app.updates, ['Newest results', 'Back results']);
    assert.match(app.status.textContent, /Could not update/);
    assert.equal(app.attributes.has('aria-busy'), false);
});

test('PAP options preserve order, deduplicate, and skip rebuilding unchanged filters', () => {
    const context = {
        window: {},
        document: {
            createDocumentFragment: () => ({ children: [], appendChild(option) { this.children.push(option); } }),
            createElement: () => ({}),
        },
    };
    vm.runInNewContext(fs.readFileSync('public/js/pap-options.js', 'utf8'), context);
    let rebuilds = 0;
    let values;
    const datalist = { replaceChildren(fragment) { rebuilds++; values = fragment.children.map(option => option.value); } };
    const source = [
        { parent: 'A', name: 'First' }, { parent: 'A', name: ' first ' },
        { parent: 'A', name: 'Second' }, { parent: 'B', name: 'Other' },
    ];
    const populate = parent => context.window.pmsPopulatePapOptions(datalist, source, 'name', [parent], item => item.parent === parent);
    populate('A');
    assert.deepEqual(values, ['First', 'Second']);
    populate('A');
    assert.equal(rebuilds, 1);
    populate('B');
    assert.deepEqual(values, ['Other']);
    assert.equal(rebuilds, 2);
});

test('Target matching scans each row section once and separates office, CAR, and group inputs', () => {
    const source = fs.readFileSync('resources/views/components/financial_input_persistence.blade.php', 'utf8');
    const start = source.indexOf('    let targetInputIndexes =');
    const end = source.indexOf('    const targetValueForAccomplishment', start);
    const context = {};
    vm.runInNewContext(source.slice(start, end) + '\nthis.find = liveTargetInput;', context);
    const input = (col, extra = {}) => ({ dataset: { col: String(col), officeId: '7', ...extra } });
    const office = input(0);
    const car = input(0, { carTotal: '1', officeId: '' });
    const group = input(0, { groupTotal: '1', groupKey: 'PENRO', officeId: '' });
    let scans = 0;
    const row = { querySelectorAll() { scans++; return [office, car, group]; } };
    for (let i = 0; i < 1000; i++) assert.equal(context.find(row, input(0), 'target'), office);
    assert.equal(scans, 1);
    assert.equal(context.find(row, input(0, { carTotal: '1', officeId: '' }), 'target'), car);
    assert.equal(context.find(row, input(0, { groupTotal: '1', groupKey: 'PENRO', officeId: '' }), 'target'), group);
    assert.equal(context.find(row, input(0, { officeId: '8' }), 'target'), null);
    assert.equal(context.find(row, input(1), 'target'), null);
});

test('Every sector and role defers collapsed inputs and skips rows already initialized', () => {
    for (const role of ['admin', 'regional', 'penro', 'users']) {
        for (const sector of ['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing']) {
            const source = fs.readFileSync(`resources/views/${role}/${sector}/partials/${sector}_physical_main_scripts2.blade.php`, 'utf8');
            const start = source.indexOf('        function addInputCells(');
            const end = source.indexOf('        function recalculateSectionRows(', start);
            let initialized = 0;
            const row = (display, existing = false) => ({
                style: { display }, classList: { contains: () => false },
                dataset: { rowId: '1', indicatorId: '1' }, querySelector: () => existing ? {} : null,
            });
            const collapsed = Array.from({ length: 1000 }, () => row('none'));
            const context = {
                window: { pmsRowsNearViewport: rows => Array.from(rows) },
                PERIODS: [], PERIOD_KEYS: [], existingTargetsByIndicator: {}, existingAccompByIndicator: {},
                totalsListenerRegistered: true,
                document: { querySelectorAll: () => collapsed },
                getIndicatorTypeForRow: () => 'cumulative',
                getAssignedOfficesForRow: () => { initialized++; return []; },
                getInputBreakIndicesForRow: () => [], getInputGroupPenroFlagsForRow: () => [],
                recalculateSectionRows() {}, recalculateCarTotalsForSection() {}, applyMonthInputVisibility() {}, refreshSummaryCards() {},
            };
            vm.runInNewContext(source.slice(start, end) + '\nthis.build = addInputCells;', context);
            context.build('target');
            assert.equal(initialized, 0, `${role}/${sector}: collapsed inputs must not be built`);
            context.build('target', [row('none'), row('', true), row('')]);
            assert.equal(initialized, 1, `${role}/${sector}: only the new expanded row should initialize`);
        }
    }
});

test('Expanded rows far outside the table viewport defer their input fields', () => {
    const context = {
        window: { innerHeight: 800 },
        document: { querySelector: () => ({ closest: () => ({ getBoundingClientRect: () => ({ top: 120, bottom: 600 }) }) }) },
    };
    vm.runInNewContext(fs.readFileSync('public/js/performance-viewport.js', 'utf8'), context);
    const row = (top, bottom, display = '') => ({
        style: { display }, classList: { contains: () => false }, getBoundingClientRect: () => ({ top, bottom }),
    });
    const visible = row(150, 450);
    const upcoming = row(790, 1100);
    const distant = row(2000, 2300);
    const collapsed = row(150, 450, 'none');
    const selected = context.window.pmsRowsNearViewport([visible, upcoming, distant, collapsed]);
    assert.equal(selected.length, 2);
    assert.equal(selected[0], visible);
    assert.equal(selected[1], upcoming);
});
