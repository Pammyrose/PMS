<style>
  #performanceTable th.month-header[data-dynamic-section="financial"]:not(.annual) {
    color: #fff !important;
  }

  #performanceTable .group-financial-accomp {
    background: #f5f3ff;
    color: #000;
  }

  #performanceTable th.month-header[data-dynamic-section="financial-accomp"] {
    background: #ddd6fe !important;
    border-color: #c4b5fd !important;
    color: #000 !important;
  }

  #performanceTable th.month-header.quarter[data-dynamic-section="financial-accomp"] {
    background: #c4b5fd !important;
    color: #000 !important;
  }

  #performanceTable th.month-header.annual[data-dynamic-section="financial-accomp"] {
    background: #a78bfa !important;
    color: #000 !important;
  }

  #performanceTable th.summary-header {
    width: 96px !important;
    min-width: 96px !important;
    max-width: 96px !important;
    padding: 6px 4px !important;
    color: #fff !important;
    line-height: 1.1;
    white-space: normal;
  }

  #performanceTable td[data-dynamic-section="summary"] {
    width: 96px !important;
    min-width: 96px !important;
    max-width: 96px !important;
    padding-left: 6px !important;
    padding-right: 6px !important;
  }

  #performanceTable td[data-dynamic-section="summary"] .month-box.summary-box {
    width: 82px !important;
    min-width: 82px !important;
    max-width: 82px !important;
  }

  #performanceTable th.summary-header[data-summary-period="annual"] {
    background: #1f3a63 !important;
  }

  #performanceTable th.summary-header[data-summary-period="quarter"] {
    background: #f59e0b !important;
  }

  #performanceTable th.summary-header[data-summary-period="to-date"] {
    background: #4f70c9 !important;
  }

  #performanceTable th.summary-section-start,
  #performanceTable td.summary-section-start {
    border-left: 3px solid #0f2747 !important;
  }

  #performanceTable th.summary-header .tiny-period {
    color: inherit;
    opacity: .95;
  }

  #performanceTable .group-summary[data-summary-kind="physical-target"] {
    background: #c9f7df !important;
    color: #065f46 !important;
  }

  #performanceTable th.summary-header[data-summary-kind="physical-target"] {
    background: #c9f7df !important;
    color: #065f46 !important;
  }

  #performanceTable .group-summary[data-summary-kind="physical-accomplishment"] {
    background: #dbeafe !important;
    color: #1e3a8a !important;
  }

  #performanceTable th.summary-header[data-summary-kind="physical-accomplishment"] {
    background: #dbeafe !important;
    color: #1e3a8a !important;
  }

  #performanceTable .group-summary[data-summary-kind="financial-target"] {
    background: #fef3c7 !important;
    color: #92400e !important;
  }

  #performanceTable th.summary-header[data-summary-kind="financial-target"] {
    background: #fef3c7 !important;
    color: #92400e !important;
  }

  #performanceTable .group-summary[data-summary-kind="financial-accomplishment"] {
    background: #ede9fe !important;
    color: #5b21b6 !important;
  }

  #performanceTable th.summary-header[data-summary-kind="financial-accomplishment"] {
    background: #ede9fe !important;
    color: #5b21b6 !important;
  }
</style>

<script>
  (() => {
    const config = {
      sector: @json($financialSector ?? ''),
      storeUrl: @json(route('financial_inputs.store', ['sector' => $financialSector ?? 'unknown'])),
      existing: @json($financials ?? []),
      existingAccomplishments: @json($financialAccomplishments ?? []),
      accomplishmentsOnly: @json(auth()->user()?->requiresPenroApproval() ?? false),
    };

    window.requiresPenroApproval = config.accomplishmentsOnly;

    const lockTargetInputs = (root = document) => {
      if (!config.accomplishmentsOnly) return;

      const selector = '.month-box[data-section="target"], .month-box[data-section="financial"]';
      const inputs = [
        ...(root.matches?.(selector) ? [root] : []),
        ...(root.querySelectorAll?.(selector) || []),
      ];

      inputs.forEach(input => {
          input.readOnly = true;
          input.setAttribute('aria-readonly', 'true');
          input.title = 'Targets are read-only for user accounts.';
          input.classList.add('bg-light');
      });
    };

    if (config.accomplishmentsOnly) {
      document.querySelectorAll('[data-bs-target="#addIndicatorModal"]')
        .forEach(button => button.classList.add('d-none'));
      lockTargetInputs();
      new MutationObserver(mutations => {
        mutations.forEach(mutation => mutation.addedNodes.forEach(node => {
          if (node.nodeType === Node.ELEMENT_NODE) lockTargetInputs(node);
        }));
      }).observe(document.body, { childList: true, subtree: true });
    }

    const periodKeys = [
      'jan', 'feb', 'mar', 'q1',
      'apr', 'may', 'jun', 'q2',
      'jul', 'aug', 'sep', 'q3',
      'oct', 'nov', 'dec', 'q4',
      'annual_total',
    ];

    const touchedPeriods = new Map();

    const touchedEntryKey = (section, rowId, indicatorId, officeId) =>
      [section, rowId, indicatorId, officeId].map(value => String(value || '').trim()).join('|');

    const attachTouchedPeriods = (entries, section) => entries.map(entry => ({
      ...entry,
      changed_periods: Array.from(touchedPeriods.get(touchedEntryKey(
        section,
        entry?.row_id || entry?.program_id,
        entry?.indicator_id,
        entry?.office_id,
      )) || []),
    }));

    document.getElementById('performanceTable')?.addEventListener('input', event => {
      const input = event.target;
      const section = String(input?.dataset?.section || '');
      if (!input?.classList?.contains('month-box') || !['accomp', 'financial-accomp'].includes(section)) return;
      if (input.readOnly || input.dataset.carTotal === '1' || input.dataset.groupTotal === '1') return;

      const row = input.closest('tr[data-row-id]');
      const period = periodKeys[Number(input.dataset.col)];
      const officeId = String(input.dataset.officeId || '').trim();
      if (!row || !period || !officeId) return;

      const key = touchedEntryKey(section, row.dataset.rowId, row.dataset.indicatorId, officeId);
      const periods = touchedPeriods.get(key) || new Set();
      periods.add(period);
      touchedPeriods.set(key, periods);
    }, true);

    const storedEntry = (entry, kind) => {
      const rowId = String(entry?.row_id || entry?.program_id || '').trim();
      const indicatorId = String(entry?.indicator_id || '').trim();
      const officeId = String(entry?.office_id || '').trim();
      const source = kind === 'accomplishment' ? config.existingAccomplishments : config.existing;

      return source?.[rowId]?.[indicatorId]?.[officeId] || null;
    };

    const numericValue = (value) => {
      if (typeof parsePeriodInputValue === 'function') {
        return parsePeriodInputValue(value);
      }

      const parsed = Number(String(value ?? '').replace(/,/g, '').replace(/%/g, '').trim());
      return Number.isFinite(parsed) ? parsed : 0;
    };

    const summaryMonthIndex = Math.max(0, Math.min(11, Number(@json(now()->month)) - 1));
    const summaryMonthColumns = [0, 1, 2, 4, 5, 6, 8, 9, 10, 12, 13, 14];
    const summaryQuarterColumns = [3, 7, 11, 15];
    const summaryQuarterIndex = Math.floor(summaryMonthIndex / 3);
    const summaryPeriods = [
      { key: 'annual', label: 'ANNUAL', column: 16 },
      { key: 'quarter', label: `Q${summaryQuarterIndex + 1}`, column: summaryQuarterColumns[summaryQuarterIndex] },
      { key: 'to-date', label: 'TO DATE', column: null },
    ];
    const summarySections = [
      {
        key: 'physical-target',
        label: 'Physical Target',
        inputSection: 'target',
        source: () => existingTargetsByIndicator,
      },
      {
        key: 'physical-accomplishment',
        label: 'Physical Accomplishment',
        inputSection: 'accomp',
        source: () => existingAccompByIndicator,
      },
      {
        key: 'financial-target',
        label: 'Financial Target',
        inputSection: 'financial',
        source: () => config.existing,
      },
      {
        key: 'financial-accomplishment',
        label: 'Financial Accomplishment',
        inputSection: 'financial-accomp',
        source: () => config.existingAccomplishments,
      },
    ];

    const summaryStoredValue = (source, rowId, indicatorId, officeId, column) => {
      const periodKey = periodKeys[column] || '';
      if (!periodKey) return 0;

      return numericValue(source?.[String(rowId)]?.[String(indicatorId)]?.[String(officeId)]?.[periodKey]);
    };

    const summaryColumnValue = (row, summarySection, officeId, column) => {
      const liveInput = row.querySelector(
        `.month-box[data-section="${summarySection.inputSection}"][data-office-id="${officeId}"][data-col="${column}"]`
      );

      if (liveInput) return numericValue(liveInput.value);

      return summaryStoredValue(
        summarySection.source(),
        row.dataset.rowId || row.dataset.programId,
        row.dataset.indicatorId,
        officeId,
        column
      );
    };

    const summaryIndicatorType = (row, summarySection) => {
      const isFinancial = ['financial', 'financial-accomp'].includes(summarySection.inputSection);
      return isFinancial || typeof getIndicatorTypeForRow !== 'function'
        ? 'cumulative'
        : getIndicatorTypeForRow(row);
    };

    const summaryValueFromMonthlyValues = (row, summarySection, monthlyValues, period) => {
      const indicatorType = summaryIndicatorType(row, summarySection);
      const quarterValue = values => indicatorType === 'non-cumulative'
        ? Math.max(0, ...values)
        : values.reduce((total, value) => total + value, 0);
      const quarterValues = [0, 1, 2, 3].map(quarterIndex => {
        const start = quarterIndex * 3;
        return quarterValue(monthlyValues.slice(start, start + 3));
      });

      if (period.key === 'quarter') {
        return quarterValues[summaryQuarterIndex];
      }

      if (period.key === 'annual') {
        return indicatorType === 'semi-cumulative'
          ? Math.max(0, ...quarterValues)
          : quarterValues.reduce((total, value) => total + value, 0);
      }

      const valuesThroughCurrentMonth = monthlyValues.slice(0, summaryMonthIndex + 1);
      if (indicatorType === 'cumulative') {
        return valuesThroughCurrentMonth.reduce((total, value) => total + value, 0);
      }

      const quarterValuesToDate = [];
      for (let start = 0; start < valuesThroughCurrentMonth.length; start += 3) {
        quarterValuesToDate.push(quarterValue(valuesThroughCurrentMonth.slice(start, start + 3)));
      }

      return indicatorType === 'semi-cumulative'
        ? Math.max(0, ...quarterValuesToDate)
        : quarterValuesToDate.reduce((total, value) => total + value, 0);
    };

    const summaryValue = (row, summarySection, officeId, period) => {
      const monthlyValues = summaryMonthColumns.map(
        column => summaryColumnValue(row, summarySection, officeId, column)
      );

      return summaryValueFromMonthlyValues(row, summarySection, monthlyValues, period);
    };

    const summaryAggregateValue = (row, summarySection, officeIds, period) => {
      const indicatorType = summaryIndicatorType(row, summarySection);
      const monthlyValues = summaryMonthColumns.map((column) => {
        const officeValues = officeIds.map(officeId => summaryColumnValue(
          row,
          summarySection,
          officeId,
          column
        ));

        return indicatorType === 'non-cumulative'
          ? Math.max(0, ...officeValues)
          : officeValues.reduce((total, value) => total + value, 0);
      });

      return summaryValueFromMonthlyValues(row, summarySection, monthlyValues, period);
    };

    const insertBeforeRemarks = (row, cell) => {
      const remarksCell = row.querySelector('td[data-dynamic-section="remarks"]');
      if (remarksCell) {
        row.insertBefore(cell, remarksCell);
      } else {
        row.appendChild(cell);
      }
    };

    const getSummaryAggregateLines = (officeEntries, groupBreakIndices, groupPenroFlags) => {
      const sortedBreaks = [...groupBreakIndices]
        .map(index => Number(index))
        .filter(index => Number.isInteger(index) && index >= 0)
        .sort((left, right) => left - right);
      const groupRanges = [];
      let rangeStart = 0;

      sortedBreaks.forEach((breakIndex) => {
        if (breakIndex >= rangeStart && breakIndex < officeEntries.length) {
          groupRanges.push({ start: rangeStart, end: breakIndex });
          rangeStart = breakIndex + 1;
        }
      });

      if (rangeStart < officeEntries.length) {
        groupRanges.push({ start: rangeStart, end: officeEntries.length - 1 });
      }

      const officeIdsForRange = range => officeEntries
        .slice(range.start, range.end + 1)
        .map(office => String(office?.id || '').trim())
        .filter(Boolean);

      return [
        {
          kind: 'car',
          officeIds: officeEntries
            .map(office => String(office?.id || '').trim())
            .filter(Boolean),
        },
        ...groupRanges
          .map((range, groupIndex) => ({
            kind: 'province',
            officeIds: officeIdsForRange(range),
            visible: Boolean(groupPenroFlags[groupIndex]),
          }))
          .filter(aggregate => aggregate.visible),
      ];
    };

    const createSummaryInput = (section, period, options = {}) => {
      const { officeId = '', aggregate = null } = options;
      const input = document.createElement('input');
      input.type = 'number';
      input.className = 'month-box summary-box';
      input.style.width = '100%';
      input.readOnly = true;
      input.setAttribute('aria-readonly', 'true');
      input.dataset.summaryKind = section.key;
      input.dataset.summaryPeriod = period.key;

      if (aggregate) {
        input.dataset.summaryAggregate = aggregate.kind;
        input.dataset.summaryOfficeIds = aggregate.officeIds.join(',');
        input.setAttribute('aria-label', aggregate.kind === 'car' ? 'CAR total' : 'Province total');
        input.classList.add(aggregate.kind === 'car' ? 'car-total-box' : 'group-total-box');
      } else {
        input.dataset.officeId = String(officeId || '');
      }

      return input;
    };

    const addSummaryHeaders = (mainHeader, groupHeader) => {
      if (groupHeader.children.length === 0) {
        for (let index = 0; index < 2; index += 1) {
          groupHeader.appendChild(document.createElement('th'));
        }
      }

      summarySections.forEach((section) => {
        const summaryGroup = document.createElement('th');
        summaryGroup.colSpan = summaryPeriods.length;
        summaryGroup.className = 'group-header group-summary';
        summaryGroup.dataset.summaryKind = section.key;
        summaryGroup.textContent = section.label;
        const remarksGroup = groupHeader.querySelector('.group-remarks');
        if (remarksGroup) {
          groupHeader.insertBefore(summaryGroup, remarksGroup);
        } else {
          groupHeader.appendChild(summaryGroup);
        }

        summaryPeriods.forEach((period, periodIndex) => {
          const header = document.createElement('th');
          header.className = 'month-header text-center dynamic-header-summary summary-header';
          if (periodIndex === 0) header.classList.add('summary-section-start');
          header.dataset.dynamicSection = 'summary';
          header.dataset.summaryKind = section.key;
          header.dataset.summaryPeriod = period.key;
          header.textContent = period.label;

          const remarksHeader = mainHeader.querySelector('th[data-dynamic-section="remarks"]');
          if (remarksHeader) {
            mainHeader.insertBefore(header, remarksHeader);
          } else {
            mainHeader.appendChild(header);
          }
        });
      });
    };

    const addSummaryCells = () => {
      document.querySelectorAll('#performanceTable tbody tr[data-row-id]').forEach((row) => {
        const officeEntries = getAssignedOfficesForRow(row);
        const offices = officeEntries.length > 0
          ? officeEntries
          : [{ id: currentOfficeId || null, name: 'Office' }];
        const groupBreakIndices = getInputBreakIndicesForRow(row);
        const groupPenroFlags = getInputGroupPenroFlagsForRow(row);
        const summaryAggregateLines = getSummaryAggregateLines(
          offices,
          groupBreakIndices,
          groupPenroFlags
        );

        summarySections.forEach((section) => {
          summaryPeriods.forEach((period, periodIndex) => {
            const cell = document.createElement('td');
            cell.className = 'p-1 text-center dynamic-cell-summary';
            if (periodIndex === 0) cell.classList.add('summary-section-start');
            cell.dataset.dynamicSection = 'summary';
            cell.dataset.summaryKind = section.key;
            cell.dataset.summaryPeriod = period.key;

            let aggregateLineIndex = 0;

            const wrapper = buildAlignedOfficeLines({
              officeEntries: offices,
              groupBreakIndices,
              groupPenroFlags,
              spacerFactory: () => {
                const aggregate = summaryAggregateLines[aggregateLineIndex++];
                return createSummaryInput(section, period, { aggregate });
              },
              renderOfficeInput: office => createSummaryInput(section, period, {
                officeId: office?.id,
              }),
            });

            cell.appendChild(wrapper);
            insertBeforeRemarks(row, cell);
          });
        });
      });
    };

    window.refreshSummaryInputs = function () {
      if (!summaryVisible) return;

      document.querySelectorAll('#performanceTable tbody tr[data-row-id]').forEach((row) => {
        row.querySelectorAll('.month-box.summary-box').forEach((input) => {
          const section = summarySections.find(item => item.key === input.dataset.summaryKind);
          const period = summaryPeriods.find(item => item.key === input.dataset.summaryPeriod);
          const aggregateOfficeIds = String(input.dataset.summaryOfficeIds || '')
            .split(',')
            .map(value => value.trim())
            .filter(Boolean);
          const officeId = String(input.dataset.officeId || '').trim();
          if (!section || !period) {
            input.value = 0;
            return;
          }

          if (input.dataset.summaryAggregate) {
            input.value = summaryAggregateValue(row, section, aggregateOfficeIds, period);
            return;
          }

          if (!officeId) {
            input.value = 0;
            return;
          }

          input.value = summaryValue(row, section, officeId, period);
        });
      });
    };

    window.toggleSummaryColumns = function () {
      const table = document.getElementById('performanceTable');
      const headerRow = table.querySelector('thead tr:not(.group-row)');
      const groupRow = document.getElementById('groupHeaders');
      const hadVisibleSection = summaryVisible || targetsVisible || financialVisible || accompVisible || pendingVisible;

      if (!summaryVisible) {
        summaryVisible = true;
        if (!hadVisibleSection) monthInputsVisible = false;
        document.getElementById('summaryBtn').innerHTML = '<i class="fa fa-eye-slash me-1"></i> Hide Summary';
        document.getElementById('summaryBtn').classList.replace('btn-info', 'btn-outline-info');
        addSummaryHeaders(headerRow, groupRow);
        addSummaryCells();
      } else {
        summaryVisible = false;
        document.getElementById('summaryBtn').innerHTML = '<i class="fa fa-chart-bar me-1"></i> Summary';
        document.getElementById('summaryBtn').classList.replace('btn-outline-info', 'btn-info');
        removeSectionColumns(groupRow, headerRow, 'summary');
        groupRow.querySelectorAll('.group-summary').forEach(group => group.remove());
        if (groupRow.querySelectorAll('.group-header').length === 0) {
          groupRow.replaceChildren();
        }
      }

      refreshMonthButtonState();
      refreshSummaryCards();
    };

    const entryChanged = (entry, kind) => {
      const stored = storedEntry(entry, kind);

      return periodKeys.some((key) => numericValue(entry?.[key]) !== numericValue(stored?.[key]));
    };

    const collectChangedEntries = (section, kind) => {
      if (typeof collectSectionEntries !== 'function') return [];
      return collectSectionEntries(section).filter(entry => entryChanged(entry, kind));
    };

    const rememberSavedEntries = (entries, kind) => {
      const destination = kind === 'accomplishment' ? config.existingAccomplishments : config.existing;

      entries.forEach((entry) => {
        const rowId = String(entry?.row_id || entry?.program_id || '').trim();
        const indicatorId = String(entry?.indicator_id || '').trim();
        const officeId = String(entry?.office_id || '').trim();
        if (!rowId || !indicatorId || !officeId) return;

        destination[rowId] ||= {};
        destination[rowId][indicatorId] ||= {};
        destination[rowId][indicatorId][officeId] = {
          ...(destination[rowId][indicatorId][officeId] || {}),
          ...entry,
          kind,
        };
      });
    };

    const saveEntries = async (entries, kind) => {
      if (!Array.isArray(entries) || entries.length === 0) {
        return { success: true, skipped: true, message: 'No financial rows to save.' };
      }

      const token = document.querySelector('input[name="_token"]')?.value || '';
      const payloadEntries = entries.map(entry => ({ ...entry, kind }));

      try {
        const response = await fetch(config.storeUrl, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          },
          body: JSON.stringify({ entries: payloadEntries }),
        });
        const data = await response.json();

        if (!response.ok || !data.success) {
          throw new Error(data.message || 'Failed to save financial inputs.');
        }

        if (!data.pending_approval) {
          rememberSavedEntries(payloadEntries, kind);
        }
        return {
          success: true,
          pending_approval: Boolean(data.pending_approval),
          message: data.message || 'Financial inputs saved.',
        };
      } catch (error) {
        console.error('Financial input save error:', error);
        return { success: false, message: error?.message || 'Error saving financial inputs.' };
      }
    };

    const hydrateInputs = (section, source) => {
      document.querySelectorAll('#performanceTable tbody tr[data-row-id]').forEach((row) => {
        const rowId = String(row.dataset.rowId || row.dataset.programId || '').trim();
        const indicatorId = String(row.dataset.indicatorId || '').trim();
        if (!rowId || !indicatorId) return;

        row.querySelectorAll(`.month-box[data-section="${section}"]`).forEach((input) => {
          if (input.dataset.carTotal === '1' || input.dataset.groupTotal === '1') return;

          const officeId = String(input.dataset.officeId || '').trim();
          const periodKey = periodKeys[Number(input.dataset.col)] || '';
          const value = source?.[rowId]?.[indicatorId]?.[officeId]?.[periodKey];
          if (value !== undefined && value !== null) input.value = value;
        });
      });
    };

    const recalculateFinancialAccomplishmentRow = (row) => {
      const section = 'financial-accomp';
      const editableInputs = Array.from(row.querySelectorAll(`.month-box[data-section="${section}"]`))
        .filter(input => input.dataset.carTotal !== '1' && input.dataset.groupTotal !== '1');
      const officeIds = Array.from(new Set(
        editableInputs.map(input => String(input.dataset.officeId || '')).filter(Boolean)
      ));

      officeIds.forEach((officeId) => {
        const inputs = editableInputs.filter(input => String(input.dataset.officeId || '') === officeId);
        const byColumn = new Map(inputs.map(input => [Number(input.dataset.col), input]));
        const monthValue = index => numericValue(byColumn.get(index)?.value);
        const totals = {
          3: monthValue(0) + monthValue(1) + monthValue(2),
          7: monthValue(4) + monthValue(5) + monthValue(6),
          11: monthValue(8) + monthValue(9) + monthValue(10),
          15: monthValue(12) + monthValue(13) + monthValue(14),
        };
        totals[16] = totals[3] + totals[7] + totals[11] + totals[15];

        Object.entries(totals).forEach(([column, value]) => {
          const input = byColumn.get(Number(column));
          if (input) input.value = value;
        });
      });

      const groupInputs = Array.from(row.querySelectorAll(
        `.month-box[data-section="${section}"][data-group-total="1"]`
      ));
      const groupedInputs = groupInputs.reduce((groups, input) => {
        const groupKey = String(input.dataset.groupKey || '').trim();
        if (!groupKey) return groups;
        if (!groups.has(groupKey)) groups.set(groupKey, []);
        groups.get(groupKey).push(input);
        return groups;
      }, new Map());

      groupedInputs.forEach((inputs) => {
        const groupOfficeIds = new Set(
          String(inputs[0]?.dataset.groupOfficeIds || '')
            .split(',')
            .map(value => value.trim())
            .filter(Boolean)
        );
        const byColumn = new Map(inputs.map(input => [Number(input.dataset.col), input]));
        const summedMonth = column => Array.from(groupOfficeIds).reduce((sum, officeId) => {
          const officeInput = editableInputs.find(candidate =>
            String(candidate.dataset.officeId || '') === officeId
              && Number(candidate.dataset.col) === column
          );
          return sum + numericValue(officeInput?.value);
        }, 0);
        const totals = {
          0: summedMonth(0), 1: summedMonth(1), 2: summedMonth(2),
          4: summedMonth(4), 5: summedMonth(5), 6: summedMonth(6),
          8: summedMonth(8), 9: summedMonth(9), 10: summedMonth(10),
          12: summedMonth(12), 13: summedMonth(13), 14: summedMonth(14),
        };
        totals[3] = totals[0] + totals[1] + totals[2];
        totals[7] = totals[4] + totals[5] + totals[6];
        totals[11] = totals[8] + totals[9] + totals[10];
        totals[15] = totals[12] + totals[13] + totals[14];
        totals[16] = totals[3] + totals[7] + totals[11] + totals[15];

        Object.entries(totals).forEach(([column, value]) => {
          const input = byColumn.get(Number(column));
          if (input) input.value = value;
        });
      });

      const carInputs = Array.from(row.querySelectorAll(
        `.month-box[data-section="${section}"][data-car-total="1"]`
      ));
      const carByColumn = new Map(carInputs.map(input => [Number(input.dataset.col), input]));
      const summedCarMonth = column => officeIds.reduce((sum, officeId) => {
        const officeInput = editableInputs.find(input =>
          String(input.dataset.officeId || '') === officeId && Number(input.dataset.col) === column
        );
        return sum + numericValue(officeInput?.value);
      }, 0);
      const carTotals = {
        0: summedCarMonth(0), 1: summedCarMonth(1), 2: summedCarMonth(2),
        4: summedCarMonth(4), 5: summedCarMonth(5), 6: summedCarMonth(6),
        8: summedCarMonth(8), 9: summedCarMonth(9), 10: summedCarMonth(10),
        12: summedCarMonth(12), 13: summedCarMonth(13), 14: summedCarMonth(14),
      };
      carTotals[3] = carTotals[0] + carTotals[1] + carTotals[2];
      carTotals[7] = carTotals[4] + carTotals[5] + carTotals[6];
      carTotals[11] = carTotals[8] + carTotals[9] + carTotals[10];
      carTotals[15] = carTotals[12] + carTotals[13] + carTotals[14];
      carTotals[16] = carTotals[3] + carTotals[7] + carTotals[11] + carTotals[15];

      Object.entries(carTotals).forEach(([column, value]) => {
        const input = carByColumn.get(Number(column));
        if (input) input.value = value;
      });
    };

    const recalculateFinancialAccomplishments = (sourceRow = null) => {
      let rows = Array.from(document.querySelectorAll('#performanceTable tbody tr[data-row-id]'));

      if (sourceRow) {
        const coreKey = String(sourceRow.dataset.coreKey || '');
        rows = coreKey
          ? rows.filter(row => String(row.dataset.coreKey || '') === coreKey)
          : [sourceRow];
      }

      rows.forEach(recalculateFinancialAccomplishmentRow);
      if (typeof refreshSummaryCards === 'function') refreshSummaryCards();
    };

    const hydrateFinancialTarget = () => {
      hydrateInputs('financial', config.existing);

      if (typeof recalculateSectionRows === 'function') recalculateSectionRows('financial');
      if (typeof recalculateCarTotalsForSection === 'function') recalculateCarTotalsForSection('financial');
      if (typeof refreshSummaryCards === 'function') refreshSummaryCards();
    };

    const hydrateFinancialAccomplishments = () => {
      hydrateInputs('financial-accomp', config.existingAccomplishments);
      recalculateFinancialAccomplishments();
    };

    let financialTargetVisible = false;
    let financialAccompVisible = false;

    const syncFinancialVisibility = () => {
      financialVisible = financialTargetVisible || financialAccompVisible;
    };

    const refreshFinancialButton = (id, visible, label, icon) => {
      const button = document.getElementById(id);
      if (!button) return;
      button.innerHTML = visible
        ? `<i class="fa fa-eye-slash me-1"></i> Hide ${label}`
        : `<i class="fa ${icon} me-1"></i> ${label}`;
    };

    if (typeof toggleFinancialColumns === 'function') {
      toggleFinancialColumns = function () {
        const table = document.getElementById('performanceTable');
        const headerRow = table.querySelector('thead tr:not(.group-row)');
        const groupRow = document.getElementById('groupHeaders');
        const hadVisibleSection = targetsVisible || financialVisible || accompVisible || pendingVisible;

        if (!financialTargetVisible) {
          financialTargetVisible = true;
          if (!hadVisibleSection) monthInputsVisible = false;
          addColumns(headerRow, groupRow, 'Financial Target', 'financial');
          addInputCells('financial');
          hydrateFinancialTarget();
        } else {
          financialTargetVisible = false;
          removeSectionColumns(groupRow, headerRow, 'financial');
        }

        syncFinancialVisibility();
        refreshFinancialButton('financialBtn', financialTargetVisible, 'Target', 'fa-bullseye');
        refreshMonthButtonState();
        refreshSummaryCards();
      };
    }

    window.toggleFinancialAccomplishmentColumns = function () {
      const table = document.getElementById('performanceTable');
      const headerRow = table.querySelector('thead tr:not(.group-row)');
      const groupRow = document.getElementById('groupHeaders');
      const hadVisibleSection = targetsVisible || financialVisible || accompVisible || pendingVisible;

      if (!financialAccompVisible) {
        financialAccompVisible = true;
        if (!hadVisibleSection) monthInputsVisible = false;
        addColumns(headerRow, groupRow, 'Financial Accomplishment', 'financial-accomp');

        const annualHeader = headerRow.querySelector(
          'th[data-dynamic-section="financial-accomp"][data-period-type="annual"]'
        );
        if (annualHeader) annualHeader.innerHTML = 'Grand<div class="tiny-period">Total</div>';

        addInputCells('financial-accomp');
        hydrateFinancialAccomplishments();
      } else {
        financialAccompVisible = false;
        removeSectionColumns(groupRow, headerRow, 'financial-accomp');
      }

      syncFinancialVisibility();
      refreshFinancialButton(
        'financialAccompBtn',
        financialAccompVisible,
        'Accomplishment',
        'fa-list-check'
      );
      refreshMonthButtonState();
      refreshSummaryCards();
    };

    const financialButton = document.getElementById('financialBtn');
    const financialListItem = financialButton?.closest('li');
    if (financialListItem && !document.getElementById('financialMenuBtn')) {
      financialListItem.className = 'dropend';
      financialListItem.innerHTML = `
        <button class="dropdown-item dropdown-toggle" id="financialMenuBtn" type="button"
          data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fa fa-peso-sign me-1"></i> Financial
        </button>
        <ul class="dropdown-menu">
          <li>
            <button onclick="toggleFinancialColumns()" class="dropdown-item" id="financialBtn" type="button">
              <i class="fa fa-bullseye me-1"></i> Target
            </button>
          </li>
          <li>
            <button onclick="toggleFinancialAccomplishmentColumns()" class="dropdown-item"
              id="financialAccompBtn" type="button">
              <i class="fa fa-list-check me-1"></i> Accomplishment
            </button>
          </li>
        </ul>`;
    }

    document.getElementById('performanceTable')?.addEventListener('input', event => {
      const input = event.target;
      if (!input.classList?.contains('month-box') || input.dataset.section !== 'financial-accomp') return;
      const row = input.closest('tr[data-row-id]');
      if (row) recalculateFinancialAccomplishments(row);
    });

    if (typeof saveAllSectionEntries === 'function') {
      saveAllSectionEntries = async function () {
        const saveAllBtn = document.getElementById('saveAllBtn');
        const originalSaveBtnHtml = saveAllBtn ? saveAllBtn.innerHTML : '';
        const targetEntries = config.accomplishmentsOnly ? [] : collectChangedTargetEntries();
        const accompEntries = attachTouchedPeriods(collectChangedAccomplishmentEntries(), 'accomp');
        const financialTargetEntries = config.accomplishmentsOnly ? [] : collectChangedEntries('financial', 'target');
        const financialAccompEntries = attachTouchedPeriods(
          collectChangedEntries('financial-accomp', 'accomplishment'),
          'financial-accomp',
        );

        if (targetEntries.length === 0 && accompEntries.length === 0
          && financialTargetEntries.length === 0 && financialAccompEntries.length === 0) {
          showTopRightErrorAlert('No input rows available to save.');
          return;
        }

        if (saveAllBtn) {
          saveAllBtn.disabled = true;
          saveAllBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i> Saving...';
        }

        try {
          const results = await Promise.all([
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
            saveEntries(financialTargetEntries, 'target'),
            saveEntries(financialAccompEntries, 'accomplishment'),
          ]);

          if (results.some(result => !result.success)) {
            showTopRightErrorAlert('Some entries failed to save. Please try again.');
            return;
          }

          if (results.every(result => result.skipped)) {
            showTopRightErrorAlert('No input rows available to save.');
            return;
          }

          showTopRightSuccessAlert(config.accomplishmentsOnly
            ? 'Accomplishments submitted to PENRO for approval.'
            : 'Data saved successfully.');
        } finally {
          if (saveAllBtn) {
            saveAllBtn.disabled = false;
            saveAllBtn.innerHTML = originalSaveBtnHtml;
          }
        }
      };
    }
  })();
</script>
