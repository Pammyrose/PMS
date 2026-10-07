const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const periods = ['jan', 'feb', 'mar', 'q1', 'apr', 'may', 'jun', 'q2', 'jul', 'aug', 'sep', 'q3', 'oct', 'nov', 'dec', 'q4', 'annual_total'];
const monthlyCols = [0, 1, 2, 4, 5, 6, 8, 9, 10, 12, 13, 14];
const helper = fs.readFileSync('public/js/physical-totals.js', 'utf8');

test('Non-cumulative office, province and CAR annual totals survive reload in every role and sector', () => {
    for (const role of ['admin', 'regional', 'penro', 'users']) {
        for (const sector of ['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing']) {
            const source = fs.readFileSync(`resources/views/${role}/${sector}/partials/${sector}_physical_main_scripts2.blade.php`, 'utf8');
            for (const section of ['target', 'accomp']) {
                const inputs = periods.map((_, col) => ({ value: col === 10 ? 69 : 11, readOnly: true, dataset: { col: String(col), section, officeId: '8' } }));
                const group = periods.map((_, col) => ({ value: 69, dataset: { col: String(col), section, groupTotal: '1', groupKey: 'abra', groupOfficeIds: '8' } }));
                const car = periods.map((_, col) => ({ value: 69, dataset: { col: String(col), section, carTotal: '1' } }));
                const all = [...inputs, ...group, ...car];
                const context = {
                    window: {}, PERIOD_KEYS: periods,
                    parsePeriodInputValue: Number,
                    getSectionColInput: (_, __, col) => inputs[col],
                    // Simulate hydration restoring old calculated totals.
                    restoreStoredOfficePeriodValues: () => { inputs[16].value = 69; return true; },
                    getIndicatorTypeForRow: row => row.dataset.indicatorType,
                    existingTargetCarTotalsByRow: new Map(periods.map(key => [`1|2|${key}`, 69])),
                    existingAccompCarTotalsByRow: new Map(),
                    existingTargetGroupTotalsByRow: new Map(), existingAccompGroupTotalsByRow: new Map(),
                };
                const updateStart = source.indexOf('        function updateSection(');
                const updateEnd = source.indexOf('        let currentProgramIndicators', updateStart);
                const carStart = source.indexOf('        function recalculateCarTotalsForRow(');
                const carEnd = source.indexOf('        function syncMonthValueAcrossCoreRows(', carStart);
                assert.ok(updateStart >= 0 && updateEnd > updateStart && carEnd > carStart);
                vm.runInNewContext(helper + source.slice(updateStart, updateEnd) + source.slice(carStart, carEnd)
                    + '\nthis.update = updateSection; this.recalculate = recalculateCarTotalsForRow;', context);
                context.update(monthlyCols.map(col => inputs[col]), inputs, section, 'non-cumulative', '8', true);
                assert.equal(inputs[10].value, 69, `${role}/${sector}: September retains 69`);
                assert.equal(inputs[11].value, 11, `${role}/${sector}: Q3 uses repeated month`);
                assert.equal(inputs[16].value, 11, `${role}/${sector}: office annual`);
                context.recalculate({ dataset: { rowId: '1', indicatorId: '2', indicatorType: 'non-cumulative' }, querySelectorAll: () => all }, section);
                assert.equal(group[16].value, 11, `${role}/${sector}: province annual`);
                assert.equal(group[11].value, 11, `${role}/${sector}: province Q3`);
                assert.equal(car[11].value, 11, `${role}/${sector}: CAR Q3`);
                assert.equal(car[16].value, 11, `${role}/${sector}: CAR annual`);
                context.update(monthlyCols.map(col => inputs[col]), inputs, section, 'cumulative', '8');
                assert.equal(inputs[16].value, 190, 'Cumulative still sums months');
                context.update(monthlyCols.map(col => inputs[col]), inputs, section, 'semi-cumulative', '8');
                assert.equal(inputs[16].value, 91, 'Semi-cumulative still uses highest quarter');
                context.update(monthlyCols.map(col => inputs[col]), inputs, 'financial', 'non-cumulative', '8');
                assert.equal(inputs[16].value, 190, 'Financial still sums months');
            }
        }
    }
});

test('Summary uses the same non-cumulative annual calculation, including zeros and decimals', () => {
    const source = fs.readFileSync('resources/views/components/financial_input_persistence.blade.php', 'utf8');
    const start = source.indexOf('    const summaryValueFromMonthlyValues =');
    const end = source.indexOf('    const summaryValue =', start);
    const context = { window: {}, summaryQuarterIndex: 2, summaryMonthIndex: 11, summaryIndicatorType: () => 'non-cumulative' };
    vm.runInNewContext(helper + source.slice(start, end) + '\nthis.calculate = summaryValueFromMonthlyValues;', context);
    const months = Array(12).fill(11); months[8] = 69;
    assert.equal(context.calculate({}, {}, months, { key: 'annual' }), 11);
    assert.equal(context.calculate({}, {}, months, { key: 'quarter' }), 11);
    assert.equal(context.window.pmsMostFrequentTotal([0, 0, 2, 0]), 0);
    assert.equal(context.window.pmsMostFrequentTotal([1.5, 1.5, 9, 1.5]), 1.5);
});
