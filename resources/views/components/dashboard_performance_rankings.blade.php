@php
  $comparisonYear = (int) ($year ?? now()->year);
  $defaultMonth = $comparisonYear === (int) now()->year ? strtolower(now()->format('M')) : 'jan';
  $comparisonCards = [
    [
      'kind' => 'target',
      'title' => 'Target Performance',
      'icon' => 'fa-bullseye',
      'accent' => '#0d6efd',
      'header' => '#eff6ff',
    ],
    [
      'kind' => 'accomplishment',
      'title' => 'Accomplishment Performance',
      'icon' => 'fa-check',
      'accent' => '#198754',
      'header' => '#ecfdf5',
    ],
  ];
@endphp

<style>
  .performance-card {
    background: #fff;
    border: 1px solid #f1f5f9;
    border-top: 4px solid var(--performance-accent) !important;
    border-radius: 1rem;
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.10);
    min-width: 0;
    overflow: hidden;
  }

  .performance-card-header {
    align-items: center;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    gap: 1rem;
    justify-content: space-between;
    min-height: 7.25rem;
    padding: 1.5rem;
    background: var(--performance-header);
  }

  .performance-card-icon {
    align-items: center;
    border-radius: 9999px;
    color: #fff;
    display: inline-flex;
    flex: 0 0 auto;
    height: 2.5rem;
    justify-content: center;
    width: 2.5rem;
    background: var(--performance-accent);
  }

  .performance-card-controls {
    align-items: end;
    background: #fff;
    border-bottom: 1px solid #eef2f7;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
    padding: 1rem 1.5rem;
  }

  .performance-measure-control {
    flex: 0 1 210px;
  }

  .performance-dropdown-controls {
    align-items: end;
    display: flex;
    gap: 0.75rem;
    margin-left: auto;
  }

  .performance-dropdown-control {
    min-width: 120px;
  }

  .performance-control-label {
    color: #64748b;
    display: block;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: 0.04em;
    margin-bottom: 0.3rem;
    text-transform: uppercase;
  }

  .performance-measure-toggle {
    display: flex;
    width: 100%;
  }

  .performance-measure-toggle .btn {
    flex: 1 1 50%;
    min-width: 0;
  }

  .performance-measure-toggle .btn.active {
    background: #2563eb;
    border-color: #2563eb;
    color: #fff;
  }

  .performance-card-context {
    background: #f8fafc;
    border-bottom: 1px solid #eef2f7;
    color: #64748b;
    display: flex;
    font-size: 0.76rem;
    gap: 0.75rem;
    justify-content: space-between;
    padding: 0.65rem 1.5rem;
  }

  .performance-card-total {
    font-weight: 800;
    white-space: nowrap;
  }

  .performance-comparison-row {
    padding: 0.85rem 0;
  }

  .performance-comparison-row + .performance-comparison-row {
    border-top: 1px solid #f1f5f9;
  }

  .performance-row-heading {
    align-items: baseline;
    display: flex;
    gap: 0.75rem;
    justify-content: space-between;
  }

  .performance-row-value {
    font-size: 0.85rem;
    font-weight: 700;
    white-space: nowrap;
  }

  .performance-bar-track {
    background: #e5e7eb;
    border-radius: 9999px;
    height: 0.9rem;
    margin-top: 0.4rem;
    overflow: hidden;
  }

  .performance-bar {
    border-radius: inherit;
    height: 100%;
    transition: width 180ms ease;
  }

  [data-performance-selected-kind="target"] .performance-bar {
    background: linear-gradient(90deg, #60a5fa, #2563eb);
  }

  [data-performance-selected-kind="accomplishment"] .performance-bar {
    background: linear-gradient(90deg, #34d399, #059669);
  }

  .performance-card-total,
  .performance-row-value {
    color: var(--performance-accent);
  }

  .performance-kind-toggle {
    background: rgba(255, 255, 255, 0.82);
    border: 1px solid rgba(148, 163, 184, 0.35);
    border-radius: 9999px;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    display: inline-flex;
    gap: 0.2rem;
    padding: 0.2rem;
  }

  .performance-kind-toggle button {
    background: transparent;
    border: 0;
    border-radius: 9999px;
    color: #64748b;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 0.42rem 0.75rem;
    transition: background-color 160ms ease, color 160ms ease, box-shadow 160ms ease;
    white-space: nowrap;
  }

  .performance-kind-toggle button:hover {
    color: #0f172a;
  }

  .performance-kind-toggle button.active {
    background: var(--performance-accent);
    box-shadow: 0 2px 5px color-mix(in srgb, var(--performance-accent) 28%, transparent);
    color: #fff;
  }

  .performance-more-button {
    border-color: var(--performance-accent);
    color: var(--performance-accent);
  }

  .performance-more-button:hover,
  .performance-more-button:focus {
    background: var(--performance-accent);
    border-color: var(--performance-accent);
    color: #fff;
  }

  .performance-row-note {
    color: #64748b;
    font-size: 0.7rem;
    margin-top: 0.3rem;
  }

  .performance-card-empty {
    align-items: center;
    color: #64748b;
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    justify-content: center;
    min-height: 230px;
    padding: 2rem;
    text-align: center;
  }

  @media (max-width: 575.98px) {
    .performance-card-header {
      align-items: flex-start;
      flex-direction: column;
      min-height: auto;
      padding: 1.15rem;
    }

    .performance-kind-toggle {
      align-self: stretch;
    }

    .performance-kind-toggle button {
      flex: 1 1 50%;
    }

    .performance-card-controls {
      align-items: stretch;
      flex-direction: column;
      padding: 1rem;
    }

    .performance-measure-control,
    .performance-dropdown-controls {
      margin-left: 0;
      width: 100%;
    }

    .performance-dropdown-control {
      flex: 1 1 50%;
      min-width: 0;
    }

    .performance-card-context {
      padding-inline: 1rem;
    }
  }
</style>

<div
  id="dashboardPerformanceComparison"
  class="grid grid-cols-1 lg:grid-cols-2 gap-6 animate-fade-in"
  data-comparison-year="{{ $comparisonYear }}"
>
  @foreach($comparisonCards as $card)
    <article
      class="performance-card"
      data-performance-card="{{ $loop->iteration }}"
      data-initial-kind="{{ $card['kind'] }}"
      data-performance-selected-kind="{{ $card['kind'] }}"
      style="--performance-accent: {{ $card['accent'] }}; --performance-header: {{ $card['header'] }};"
    >
      <div class="performance-card-header">
        <h3 class="text-xl font-bold text-gray-900 d-flex align-items-center gap-3 mb-0">
          <span class="performance-card-icon">
            <i class="fa-solid {{ $card['icon'] }}" data-performance-icon></i>
          </span>
          <span data-performance-title>{{ $card['title'] }}</span>
        </h3>
        <div class="performance-kind-toggle" role="group" aria-label="Select target or accomplishment data">
          <button
            type="button"
            class="{{ $card['kind'] === 'target' ? 'active' : '' }}"
            data-performance-kind="target"
            aria-pressed="{{ $card['kind'] === 'target' ? 'true' : 'false' }}"
          >
            <i class="fa-solid fa-bullseye me-1"></i>Target
          </button>
          <button
            type="button"
            class="{{ $card['kind'] === 'accomplishment' ? 'active' : '' }}"
            data-performance-kind="accomplishment"
            aria-pressed="{{ $card['kind'] === 'accomplishment' ? 'true' : 'false' }}"
          >
            <i class="fa-solid fa-check me-1"></i>Accomplishment
          </button>
        </div>
      </div>

      <div class="performance-card-controls">
        <div class="performance-measure-control">
          <span class="performance-control-label">Measure</span>
          <div class="btn-group performance-measure-toggle" role="group" aria-label="Select {{ strtolower($card['title']) }} measure">
            <button
              type="button"
              class="btn btn-outline-primary btn-sm active"
              data-performance-measure="physical"
              aria-pressed="true"
            >Physical</button>
            <button
              type="button"
              class="btn btn-outline-primary btn-sm"
              data-performance-measure="financial"
              aria-pressed="false"
            >Financial</button>
          </div>
        </div>

        <div class="performance-dropdown-controls">
          <div class="performance-dropdown-control">
            <label for="{{ $card['kind'] }}Frequency" class="performance-control-label">Frequency</label>
            <select id="{{ $card['kind'] }}Frequency" class="form-select form-select-sm" data-performance-frequency>
              <option value="monthly" selected>Monthly</option>
              <option value="quarterly">Quarterly</option>
            </select>
          </div>

          <div class="performance-dropdown-control">
            <label for="{{ $card['kind'] }}Period" class="performance-control-label" data-performance-period-label>Month</label>
            <select
              id="{{ $card['kind'] }}Period"
              class="form-select form-select-sm"
              data-performance-period
              aria-label="Select {{ strtolower($card['title']) }} month"
            ></select>
          </div>
        </div>
      </div>

      <div class="performance-card-context" aria-live="polite">
        <span data-performance-context></span>
        <span class="performance-card-total" data-performance-total>0</span>
      </div>

      <div class="px-4 py-2" data-performance-list></div>

      <div class="pb-4 text-center">
        <button
          type="button"
          class="btn performance-more-button btn-sm px-4 d-none"
          data-performance-more
          aria-expanded="false"
        >See more</button>
      </div>
    </article>
  @endforeach
</div>

<script>
  (() => {
    const root = document.getElementById('dashboardPerformanceComparison');
    if (!root || root.dataset.initialized === 'true') return;
    root.dataset.initialized = 'true';

    const comparisonData = @json($performanceComparison ?? []);
    const year = Number(root.dataset.comparisonYear);
    const defaultMonth = @json($defaultMonth);
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
  })();
</script>
