const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');

const periods = ['jan', 'feb', 'mar', 'q1', 'apr', 'may', 'jun', 'q2', 'jul', 'aug', 'sep', 'q3', 'oct', 'nov', 'dec', 'q4', 'annual_total'];

test('Editing October to zero updates CAR and keeps it updated during later hydration in every sector and role', () => {
    for (const role of ['admin', 'regional', 'penro', 'users']) {
        for (const sector of ['gass', 'sto', 'enf', 'pa', 'engp', 'lands', 'soilcon', 'nra', 'paria', 'cobb', 'continuing']) {
            const source = fs.readFileSync(`resources/views/${role}/${sector}/partials/${sector}_physical_main_scripts2.blade.php`, 'utf8');
            const start = source.indexOf('        function recalculateCarTotalsForRow(');
            const end = source.indexOf('        function syncMonthValueAcrossCoreRows(', start);
            assert.ok(start >= 0 && end > start);

            for (const section of ['target', 'accomp']) {
                const makeInput = (col, value, aggregate = false) => ({
                    value,
                    dataset: { col: String(col), section, officeId: aggregate ? '' : '8', ...(aggregate ? { carTotal: '1' } : {}) },
                });
                const officeInputs = periods.map((_, col) => makeInput(col, [12, 15, 16].includes(col) ? 2 : 0));
                const carInputs = periods.map((_, col) => makeInput(col, [12, 15, 16].includes(col) ? 2 : 0, true));
                const inputs = [...officeInputs, ...carInputs];
                const row = { dataset: { rowId: '1', indicatorId: '2', indicatorType: 'cumulative' }, querySelectorAll: () => inputs };
                const map = new Map(periods.map((key, col) => [`1|2|${key}`, Number(carInputs[col].value)]));
                const context = {
                    PERIOD_KEYS: periods,
                    getIndicatorTypeForRow: () => 'cumulative',
                    parsePeriodInputValue: value => Number(value),
                    existingTargetCarTotalsByRow: section === 'target' ? map : new Map(),
                    existingAccompCarTotalsByRow: section === 'accomp' ? map : new Map(),
                    existingTargetGroupTotalsByRow: new Map(), existingAccompGroupTotalsByRow: new Map(),
                };
                vm.runInNewContext(source.slice(start, end) + '\nthis.recalculate = recalculateCarTotalsForRow;', context);
                context.recalculate(row, section);
                assert.equal(Number(carInputs[12].value), 2);

                officeInputs[12].value = 0;
                officeInputs[15].value = 0;
                officeInputs[16].value = 0;
                context.recalculate(row, section, false);
                assert.equal(Number(carInputs[12].value), 0, `${role}/${sector}/${section}: October`);
                assert.equal(Number(carInputs[15].value), 0, `${role}/${sector}/${section}: Q4`);
                assert.equal(Number(carInputs[16].value), 0, `${role}/${sector}/${section}: annual`);
                context.recalculate(row, section);
                assert.equal(Number(carInputs[12].value), 0, `${role}/${sector}/${section}: hydration must preserve the edit`);

                if (section === 'accomp') {
                    map.set('1|2|oct', 2);
                    context.recalculate(row, section);
                    assert.equal(Number(carInputs[12].value), 0, `${role}/${sector}: saved zero must override stale CAR on reload`);
                }

                const otherOffice = periods.map((_, col) => ({ ...makeInput(col, [12, 15, 16].includes(col) ? 3 : 0), dataset: { col: String(col), section, officeId: '9' } }));
                inputs.push(...otherOffice);
                context.recalculate(row, section, false);
                assert.equal(Number(carInputs[12].value), 3, 'Other offices must still contribute to CAR');
            }
        }
    }
});
