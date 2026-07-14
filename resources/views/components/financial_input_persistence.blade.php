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
</style>

<script>
  (() => {
    const config = {
      sector: @json($financialSector ?? ''),
      storeUrl: @json(route('financial_inputs.store', ['sector' => $financialSector ?? 'unknown'])),
      existing: @json($financials ?? []),
      existingAccomplishments: @json($financialAccomplishments ?? []),
    };

    const periodKeys = [
      'jan', 'feb', 'mar', 'q1',
      'apr', 'may', 'jun', 'q2',
      'jul', 'aug', 'sep', 'q3',
      'oct', 'nov', 'dec', 'q4',
      'annual_total',
    ];

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

        rememberSavedEntries(payloadEntries, kind);
        return { success: true, message: data.message || 'Financial inputs saved.' };
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

      const carInputs = Array.from(row.querySelectorAll(
        `.month-box[data-section="${section}"][data-car-total="1"]`
      ));
      carInputs.forEach((carInput) => {
        const column = Number(carInput.dataset.col);
        carInput.value = officeIds.reduce((sum, officeId) => {
          const officeInput = editableInputs.find(input =>
            String(input.dataset.officeId || '') === officeId && Number(input.dataset.col) === column
          );
          return sum + numericValue(officeInput?.value);
        }, 0);
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
        const targetEntries = collectChangedTargetEntries();
        const accompEntries = collectChangedAccomplishmentEntries();
        const financialTargetEntries = collectChangedEntries('financial', 'target');
        const financialAccompEntries = collectChangedEntries('financial-accomp', 'accomplishment');

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

          showTopRightSuccessAlert('Data saved successfully.');
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
