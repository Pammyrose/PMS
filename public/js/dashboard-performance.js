window.initializeDashboardPerformance = function () {
    const root = document.getElementById('dashboardPerformanceComparison');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const comparisonData = JSON.parse(document.getElementById('dashboardComparisonData').textContent);
    const year = Number(root.dataset.comparisonYear);
    const defaultMonth = root.dataset.defaultMonth;
    const monthOptions = [
      ['jan', 'January'], ['feb', 'February'], ['mar', 'March'],
      ['apr', 'April'], ['may', 'May'], ['jun', 'June'],
      ['jul', 'July'], ['aug', 'August'], ['sep', 'September'],
      ['oct', 'October'], ['nov', 'November'], ['dec', 'December'],
    ];
    const quarterOptions = [
      ['q1', 'Quarter 1'],
      ['q2', 'Quarter 2'],
      ['q3', 'Quarter 3'],
      ['q4', 'Quarter 4'],
    ];
    const monthKeys = monthOptions.map(([key]) => key);
    const visibleRowLimit = 4;
    const states = new Map();
    const numberFormatter = new Intl.NumberFormat('en-PH', { maximumFractionDigits: 2 });
    const currencyFormatter = new Intl.NumberFormat('en-PH', {
      style: 'currency',
      currency: 'PHP',
      maximumFractionDigits: 0,
    });

    function optionsFor(frequency) {
      return frequency === 'monthly' ? monthOptions : quarterOptions;
    }

    function selectedPeriodName(state) {
      return optionsFor(state.frequency).find(([key]) => key === state.period)?.[1] || '';
    }

    function rowValue(row, kind, state) {
      return Number(row?.[kind]?.[state.period] || 0);
    }

    function formatValue(value, metric) {
      const numericValue = Number(value || 0);

      if (metric === 'financial') {
        return currencyFormatter.format(numericValue);
      }

      return numberFormatter.format(numericValue);
    }

    function updatePeriodSelect(card, state) {
      const select = card.querySelector('[data-performance-period]');
      const label = card.querySelector('[data-performance-period-label]');
      const options = optionsFor(state.frequency);

      label.textContent = state.frequency === 'monthly' ? 'Month' : 'Quarter';
      select.setAttribute('aria-label', `Select ${state.kind} ${state.frequency === 'monthly' ? 'month' : 'quarter'}`);
      select.innerHTML = options.map(([key, optionLabel]) => (
        `<option value="${key}" ${key === state.period ? 'selected' : ''}>${optionLabel}</option>`
      )).join('');
    }

    function scaleMaximum(state, rows) {
      return rows.reduce(
        (maximum, row) => Math.max(maximum, rowValue(row, state.kind, state)),
        0,
      );
    }

    function renderCard(card) {
      const state = states.get(card);
      const rows = Array.isArray(comparisonData[state.metric]) ? comparisonData[state.metric] : [];
      const list = card.querySelector('[data-performance-list]');
      const total = rows.reduce((sum, row) => sum + rowValue(row, state.kind, state), 0);
      const maximum = scaleMaximum(state, rows);
      const displayedRows = state.expanded ? rows : rows.slice(0, visibleRowLimit);
      const moreButton = card.querySelector('[data-performance-more]');

      card.querySelector('[data-performance-context]').textContent = `${state.metric === 'financial' ? 'Financial amounts' : 'Physical input cells'} • ${selectedPeriodName(state)} ${year}`;
      card.querySelector('[data-performance-total]').textContent = formatValue(total, state.metric);

      if (rows.length === 0) {
        list.innerHTML = `
          <div class="performance-card-empty">
            <i class="fa-regular fa-folder-open fs-3"></i>
            <span>No data available for this selection.</span>
          </div>
        `;
      } else {
        list.innerHTML = displayedRows.map((row) => {
          const value = rowValue(row, state.kind, state);
          const target = rowValue(row, 'target', state);
          const accomplishment = rowValue(row, 'accomplishment', state);
          const progress = target > 0 ? (accomplishment / target) * 100 : 0;
          const width = maximum > 0 ? Math.min(100, (value / maximum) * 100) : 0;
          const note = state.metric === 'physical'
            ? state.kind === 'target'
              ? 'Selected-period target input cells'
              : `${numberFormatter.format(progress)}% of target input cells`
            : state.kind === 'target'
              ? 'Selected period target'
              : `${numberFormatter.format(progress)}% of target`;

          return `
            <div class="performance-comparison-row">
              <div class="performance-row-heading">
                <span class="text-sm font-medium text-gray-800">${row.label}</span>
                <span class="performance-row-value">${formatValue(value, state.metric)}</span>
              </div>
              <div class="performance-bar-track" aria-hidden="true">
                <div class="performance-bar" style="width: ${width}%"></div>
              </div>
              <div class="performance-row-note">${note}</div>
            </div>
          `;
        }).join('');
      }

      moreButton.classList.toggle('d-none', rows.length <= visibleRowLimit);
      moreButton.textContent = state.expanded ? 'See less' : 'See more';
      moreButton.setAttribute('aria-expanded', String(state.expanded));
    }

    function renderAllCards() {
      root.querySelectorAll('[data-performance-card]').forEach(renderCard);
    }

    function applyCardKind(card, state) {
      const isTarget = state.kind === 'target';

      card.dataset.performanceSelectedKind = state.kind;
      card.style.setProperty('--performance-accent', isTarget ? '#0d6efd' : '#198754');
      card.style.setProperty('--performance-header', isTarget ? '#eff6ff' : '#ecfdf5');
      card.querySelector('[data-performance-title]').textContent = isTarget
        ? 'Target Performance'
        : 'Accomplishment Performance';
      card.querySelector('[data-performance-icon]').className = isTarget
        ? 'fa-solid fa-bullseye'
        : 'fa-solid fa-check';
      card.querySelectorAll('[data-performance-kind]').forEach((button) => {
        const isActive = button.dataset.performanceKind === state.kind;
        button.classList.toggle('active', isActive);
        button.setAttribute('aria-pressed', String(isActive));
      });
    }

    root.querySelectorAll('[data-performance-card]').forEach((card) => {
      const kind = card.dataset.initialKind;
      const state = {
        kind,
        metric: 'physical',
        frequency: 'monthly',
        period: monthKeys.includes(defaultMonth) ? defaultMonth : 'jan',
        expanded: false,
      };
      states.set(card, state);
      applyCardKind(card, state);
      updatePeriodSelect(card, state);

      card.querySelectorAll('[data-performance-kind]').forEach((button) => {
        button.addEventListener('click', () => {
          state.kind = button.dataset.performanceKind;
          state.expanded = false;
          applyCardKind(card, state);
          renderCard(card);
        });
      });

      card.querySelectorAll('[data-performance-measure]').forEach((button) => {
        button.addEventListener('click', () => {
          state.metric = button.dataset.performanceMeasure;
          state.expanded = false;

          card.querySelectorAll('[data-performance-measure]').forEach((item) => {
            const isActive = item === button;
            item.classList.toggle('active', isActive);
            item.setAttribute('aria-pressed', String(isActive));
          });

          renderCard(card);
        });
      });

      card.querySelector('[data-performance-frequency]').addEventListener('change', (event) => {
        const previousMonthIndex = state.frequency === 'monthly'
          ? Math.max(monthKeys.indexOf(state.period), 0)
          : Math.max((Number(state.period.slice(1)) - 1) * 3, 0);

        state.frequency = event.target.value;
        state.period = state.frequency === 'monthly'
          ? monthKeys[previousMonthIndex]
          : `q${Math.floor(previousMonthIndex / 3) + 1}`;
        state.expanded = false;
        updatePeriodSelect(card, state);
        renderCard(card);
      });

      card.querySelector('[data-performance-period]').addEventListener('change', (event) => {
        state.period = event.target.value;
        state.expanded = false;
        renderCard(card);
      });

      card.querySelector('[data-performance-more]').addEventListener('click', () => {
        state.expanded = !state.expanded;
        renderCard(card);
      });
    });

    renderAllCards();
  
};
