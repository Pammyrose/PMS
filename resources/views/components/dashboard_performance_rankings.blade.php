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
  data-default-month="{{ $defaultMonth }}"
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

<script type="application/json" id="dashboardComparisonData">@json($performanceComparison ?? [])</script>
