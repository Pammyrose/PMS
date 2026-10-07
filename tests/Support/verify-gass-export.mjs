// Compare the generated workbook with the actual UI JavaScript, not a second
// copy of the PHP export implementation. Run verify-gass-export.php first.
import fs from 'node:fs';
import assert from 'node:assert/strict';

const payload = JSON.parse(fs.readFileSync('storage/app/gass-export-verification.json', 'utf8'));
const source = fs.readFileSync('resources/views/components/financial_input_persistence.blade.php', 'utf8');
const extract = name => {
  const start = source.indexOf(`    const ${name} =`);
  assert.ok(start >= 0, `UI function ${name} exists`);
  const end = source.indexOf('\n    const ', start + 1);
  return source.slice(start, end);
};
const decode = value => value.replace(/&quot;/g, '"').replace(/&#0?39;/g, "'").replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&amp;/g, '&');
const text = value => decode(value.replace(/<[^>]+>/g, '')).replace(/\s+/g, ' ').trim();
const csv = value => value ? value.split(',').map(Number) : [];
const rows = [...payload.html.matchAll(/<tr\b([^>]*)>([\s\S]*?)<\/tr>/g)].map(match => {
  const dataset = {};
  for (const [, key, value] of match[1].matchAll(/data-([\w-]+)="([^"]*)"/g)) {
    dataset[key.replace(/-([a-z])/g, (_, letter) => letter.toUpperCase())] = decode(value);
  }
  return { dataset, html: match[2], attributes: match[1], querySelector: () => null };
});
const physicalRows = rows.filter(row => row.dataset.rowId);
const environment = {
  sources: payload.sources,
  month: payload.month,
  document: { querySelectorAll: () => physicalRows },
  getAssignedOfficesForRow: row => csv(row.dataset.inputOfficeIds).map((id, i) => ({ id, name: row.dataset.inputOfficeNames.split('|')[i] })),
  getInputBreakIndicesForRow: row => csv(row.dataset.inputBreakIndices),
  getInputGroupPenroFlagsForRow: row => csv(row.dataset.inputGroupPenroFlags),
  getIndicatorTypeForRow: row => {
    const type = row.dataset.indicatorType.toLowerCase();
    return type.startsWith('non') ? 'non-cumulative' : type.startsWith('semi') ? 'semi-cumulative' : 'cumulative';
  },
};
const functions = ['summaryStoredValue', 'summaryColumnValue', 'summaryIndicatorType', 'summaryValueFromMonthlyValues', 'summaryValue', 'summaryAggregateValue', 'normalizeDefaultOfficeUnit', 'officeIdsForDefaultFinancialUnit', 'defaultFinancialUnitValue'];
const ui = new Function('env', `
  const {sources, month, document, getAssignedOfficesForRow, getInputBreakIndicesForRow, getInputGroupPenroFlagsForRow, getIndicatorTypeForRow} = env;
  const numericValue = value => Number(value) || 0;
  const periodKeys = ['jan','feb','mar','q1','apr','may','jun','q2','jul','aug','sep','q3','oct','nov','dec','q4','annual_total'];
  const summaryMonthColumns = [0,1,2,4,5,6,8,9,10,12,13,14];
  const summaryMonthIndex = month - 1;
  const summaryQuarterIndex = Math.floor(summaryMonthIndex / 3);
  const summaryPeriods = [{key:'annual'},{key:'quarter'},{key:'to-date'}];
  const summaryPeriodsForSection = () => summaryPeriods;
  const summarySections = [
    {key:'physical-target', inputSection:'target', source:()=>sources.physical_target},
    {key:'physical-accomplishment', inputSection:'accomp', source:()=>sources.physical_accomplishment},
    {key:'financial-target', inputSection:'financial', source:()=>sources.financial_target},
    {key:'financial-accomplishment', inputSection:'financial-accomp', source:()=>sources.financial_accomplishment}
  ];
  ${functions.map(extract).join('\n')}
  return {summarySections, summaryAggregateValue, defaultFinancialUnitValue};
`)(environment);

let sheetRow = 15;
let checked = 0;
let financialBlocks = 0;
const ratio = (a, b) => b > 0 ? a / b : 0;
const check = (column, expected) => {
  const actual = payload.sheet[sheetRow]?.[column];
  if (typeof expected === 'number') {
    assert.ok(actual !== undefined && Math.abs(Number(actual) - expected) <= 0.000051, `${column}${sheetRow}: Excel=${actual}, UI=${expected}`);
  } else {
    assert.equal(actual ?? '', expected, `${column}${sheetRow}`);
  }
  checked++;
};
for (const row of rows) {
  if (row.attributes.includes('default-office-unit-row')) {
    financialBlocks++;
    for (const [, unit] of row.html.matchAll(/data-default-office-unit="([^"]*)"/g)) {
      const target = ['annual','quarter','to-date'].map(period => ui.defaultFinancialUnitValue(row, 'financial-target', period, unit));
      const accomp = ['quarter','to-date'].map(period => ui.defaultFinancialUnitValue(row, 'financial-accomplishment', period, unit));
      check('C', unit);
      for (const col of 'DEFGHIJ') check(col, '');
      const values = [...target, ...accomp, ratio(target[2],target[0]), ratio(accomp[1],target[0]), ratio(accomp[1],target[2])];
      [...'LMNOPQRS'].forEach((column, i) => check(column, values[i]));
      sheetRow++;
    }
  } else if (row.dataset.rowId) {
    const ids = csv(row.dataset.inputOfficeIds);
    const breaks = [...csv(row.dataset.inputBreakIndices), ids.length - 1];
    const flags = csv(row.dataset.inputGroupPenroFlags);
    let start = 0;
    const provinces = breaks.flatMap((end, index) => {
      const group = ids.slice(start, end + 1); start = end + 1;
      return flags[index] ? [group] : [];
    });
    let officeIndex = 0;
    let provinceIndex = 0;
    for (const [, classes, label] of row.html.matchAll(/<div class="(office-line(?:\s[^\"]*)?)">([\s\S]*?)<\/div>/g)) {
      const lineIds = classes.includes('car-office-line') ? ids : classes.includes('group-total-office-line') ? provinces[provinceIndex++] : ids.slice(officeIndex, ++officeIndex);
      check('C', text(label));
      const values = ui.summarySections.slice(0,2).map(section => ['annual','quarter','to-date'].map(key => ui.summaryAggregateValue(row, section, lineIds, {key})));
      const [target, accomp] = values;
      const physical = [...target, accomp[1], accomp[2], ratio(accomp[2],target[2]), ratio(accomp[2],target[0])];
      [...'DEFGHIJ'].forEach((column, i) => check(column, physical[i]));
      for (const col of 'KLMNOPQRS') check(col, '');
      sheetRow++;
    }
  } else if (row.attributes.includes('sub-activity-label-row')) {
    check('A', text(row.html)); sheetRow++;
  }
}
assert.equal(sheetRow - 1, Math.max(...Object.keys(payload.sheet).map(Number)), 'No additional Excel rows');
console.log(JSON.stringify({uiIndicators: physicalRows.length, financialBlocks, comparedCells: checked, mismatches: 0}, null, 2));
