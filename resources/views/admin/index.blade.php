<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .progress-trend-chart {
            display: grid;
            grid-template-columns: repeat(12, minmax(34px, 1fr));
            align-items: end;
            gap: 0.75rem;
            min-height: 260px;
            padding: 1rem 0.25rem 0;
        }

        .progress-trend-chart.is-quarterly {
            grid-template-columns: repeat(4, minmax(58px, 1fr));
        }

        .progress-trend-item {
            display: grid;
            grid-template-rows: 2rem 190px 1.5rem;
            gap: 0.5rem;
            min-width: 0;
            text-align: center;
        }

        .progress-trend-value {
            color: #1f2937;
            font-size: 0.8rem;
            font-weight: 700;
            line-height: 1rem;
            white-space: nowrap;
        }

        .progress-trend-bar-track {
            align-items: end;
            background: linear-gradient(180deg, #f8fafc 0%, #eef2f7 100%);
            border: 1px solid #e5e7eb;
            border-radius: 0.5rem 0.5rem 0.25rem 0.25rem;
            display: flex;
            height: 190px;
            overflow: hidden;
            position: relative;
        }

        .progress-trend-bar-track::before {
            background-image: linear-gradient(to top, rgba(148, 163, 184, 0.22) 1px, transparent 1px);
            background-size: 100% 25%;
            content: "";
            inset: 0;
            pointer-events: none;
            position: absolute;
        }

        .progress-trend-bar {
            background: linear-gradient(180deg, #60a5fa 0%, #2563eb 100%);
            border-radius: 0.45rem 0.45rem 0 0;
            min-height: 3px;
            position: relative;
            transition: height 180ms ease;
            width: 100%;
        }

        .progress-trend-label {
            color: #6b7280;
            font-size: 0.78rem;
            font-weight: 700;
            line-height: 1rem;
        }

        .progress-trend-empty {
            align-items: center;
            color: #6b7280;
            display: flex;
            justify-content: center;
            min-height: 180px;
        }

        .progress-trend-toggle .btn {
            min-width: 92px;
        }

        .progress-trend-toggle .btn.active {
            background: #2563eb;
            border-color: #2563eb;
            color: #ffffff;
        }

        @media (max-width: 768px) {
            .progress-trend-chart {
                grid-template-columns: repeat(12, minmax(48px, 1fr));
                overflow-x: auto;
            }

            .progress-trend-chart.is-quarterly {
                grid-template-columns: repeat(4, minmax(54px, 1fr));
            }

            .progress-trend-item {
                grid-template-rows: 2rem 150px 1.5rem;
            }

            .progress-trend-bar-track {
                height: 150px;
            }
        }
    </style>

</head>
<body>

    <!-- Top navigation bar (full width) -->
    @include('components.nav')

    <!-- Sidebar + Main Content (side-by-side) -->
    <div class="d-flex">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main content wrapper -->
        <main class="flex-grow-1 p-4 bg-gradient-to-b from-gray-50 to-white">


      <!-- Section Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 animate-fade-in">
        <div>
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Performance Overview</h2>
        </div>
<form 
    method="GET" 
    action="{{ route('dashboard') }}" 
    class="d-flex align-items-center gap-3 mt-3 mt-sm-0"
    id="yearFilterForm"
>
    <div class="d-flex align-items-center gap-2">
        <label
            for="dashboard_sector"
            class="form-label fw-semibold text-muted mb-0 fs-6"
        >
            Sector
        </label>

        <select
            id="dashboard_sector"
            name="sector"
            class="form-select form-select-md shadow-sm border-primary-subtle"
            style="width: 170px; min-width: 140px;"
            aria-label="Select dashboard sector"
            onchange="this.form.submit()"
        >
            @php
                $currentSector = $selectedSector ?? 'all';
                $sectors = $sectorOptions ?? collect();
            @endphp

            <option value="all" {{ $currentSector === 'all' ? 'selected' : '' }}>
                All
            </option>

            @foreach($sectors as $sector)
                <option
                    value="{{ $sector['key'] }}"
                    {{ $currentSector === $sector['key'] ? 'selected' : '' }}
                >
                    {{ $sector['label'] }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="d-flex align-items-center gap-2">
        <label 
            for="dashboard_year" 
            class="form-label fw-semibold text-muted mb-0 fs-6"
        >
            Year
        </label>

        <select 
            id="dashboard_year" 
            name="year" 
            class="form-select form-select-md shadow-sm border-primary-subtle"
            style="width: 140px; min-width: 120px;"
            aria-label="Select dashboard year"
            onchange="this.form.submit()"
        >
            @php
                $currentYear = (int) ($year ?? now()->year);
                $options = $yearOptions ?? collect(range(now()->year - 5, now()->year + 1));
            @endphp

            @foreach($options as $optionYear)
                <option 
                    value="{{ $optionYear }}" 
                    {{ $currentYear === (int) $optionYear ? 'selected' : '' }}
                >
                    {{ $optionYear }}
                </option>
            @endforeach
        </select>
    </div>
</form>
      </div>

      <div class="mb-12">
        <h3 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-3">
          <i class="fa-solid fa-chart-pie text-accent"></i> Overall Performance Snapshot
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center items-center text-center">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-1">Overall Progress</p>
            <div class="flex items-center justify-center mt-4">
              <div class="relative w-28 h-28">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle cx="50" cy="50" r="44" stroke="#3b82f6" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="{{ 276.46 * (1 - ((float) ($overallProgress ?? 0) / 100)) }}" stroke-linecap="round" fill="none" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-4xl font-extrabold text-blue-600">{{ number_format((float) ($overallProgress ?? 0), 0) }}%</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center items-center text-center">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-1">Physical Targets</p>
            <div class="flex items-center justify-center mt-4">
              <div class="relative w-28 h-28">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle cx="50" cy="50" r="44" stroke="#10b981" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="{{ 276.46 * (1 - ((float) ($physicalTargetsProgress ?? 0) / 100)) }}" stroke-linecap="round" fill="none" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-4xl font-extrabold text-emerald-600">{{ number_format((float) ($physicalTargetsProgress ?? 0), 0) }}%</span>
                </div>
              </div>
            </div>
          </div>
       

          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center items-center text-center">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-1">Financial Utilization</p>
            <div class="flex items-center justify-center mt-4">
              <div class="relative w-28 h-28">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle cx="50" cy="50" r="44" stroke="#f59e0b" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="{{ is_numeric($financialUtilization ?? null) ? 276.46 * (1 - ((float) $financialUtilization / 100)) : 276.46 }}" stroke-linecap="round" fill="none" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-4xl font-extrabold text-amber-500">{{ is_numeric($financialUtilization ?? null) ? number_format((float) $financialUtilization, 0) . '%' : 'N/A' }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center items-center text-center">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-8">Total Accomplishments</p>
            <div class="flex items-center justify-center mt-2">
              <p class="text-7xl font-extrabold text-blue-700">{{ number_format((float) ($overallAccomp ?? 0), 0) }}</p>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 animate-fade-in mb-12">
        <div class="px-6 py-5 border-b border-gray-100 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3 mb-1">
              <i class="fa-solid fa-chart-line text-accent"></i> Monthly / Quarterly Accomplishments Chart
            </h3>
            <p class="text-sm text-gray-500 mb-0">
              Accomplishment trend for {{ (int) ($year ?? now()->year) }}{{ ($selectedSector ?? 'all') !== 'all' ? ' - ' . strtoupper((string) $selectedSector) : '' }}
            </p>
          </div>
          <div class="btn-group progress-trend-toggle" role="group" aria-label="Progress chart range">
            <button type="button" class="btn btn-outline-primary active" data-progress-view="monthly">Monthly</button>
            <button type="button" class="btn btn-outline-primary" data-progress-view="quarterly">Quarterly</button>
          </div>
        </div>

        <div class="p-6">
          <div id="progressTrendChart" class="progress-trend-chart" aria-label="Monthly accomplishments chart"></div>
          <div class="d-flex flex-wrap gap-4 justify-content-between align-items-center mt-4 text-sm text-gray-500">
            <span><span class="d-inline-block rounded me-2" style="width: 12px; height: 12px; background: #2563eb;"></span>Accomplishments</span>
            <span>Bars compare accomplished physical outputs by period.</span>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 animate-fade-in">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
            <i class="fa-solid fa-chart-bar text-accent"></i> Sectors Performance Ranking
          </h3>
          <span class="text-sm text-gray-500">Sorted by progress (highest first)</span>
        </div>

        <div class="p-6 space-y-8">
          @forelse(($fieldStats ?? []) as $field)
            @php
              $progress = (float) ($field['progress'] ?? 0);
              $isOnTrack = $progress >= 80;
              $isNeedsAttention = $progress >= 60 && $progress < 80;
              $textClass = $isOnTrack ? 'text-emerald-600' : ($isNeedsAttention ? 'text-amber-600' : 'text-red-600');
              $barClass = $isOnTrack
                ? 'bg-gradient-to-r from-emerald-400 to-emerald-600'
                : ($isNeedsAttention
                    ? 'bg-gradient-to-r from-amber-400 to-amber-500'
                    : 'bg-gradient-to-r from-red-400 to-red-500');
            @endphp
            <a
              href="{{ route($field['key'] . '_physical') }}"
              class="block space-y-2 rounded-xl p-3 -m-3 text-decoration-none hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition"
              aria-label="Open {{ $field['label'] }} sector page"
            >
              <div class="flex justify-between items-center text-sm font-medium">
                <span class="text-gray-800">{{ $field['label'] }}</span>
                <span class="d-flex align-items-center gap-2 {{ $textClass }}">
                  {{ number_format($progress, 0) }}%
                  <i class="fa-solid fa-arrow-right text-xs"></i>
                </span>
              </div>
              <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $progress }}%"></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>Target: {{ number_format((float) ($field['target_total'] ?? 0), 0) }} | Accomplished: {{ number_format((float) ($field['accomp_total'] ?? 0), 0) }}</span>
                <span class="font-medium {{ $textClass }}">{{ $field['status'] }}</span>
              </div>
            </a>
          @empty
            <div class="text-sm text-gray-500">No field data available yet.</div>
          @endforelse
        </div>
      </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Optional: mobile sidebar toggle script -->
    <script>
        document.getElementById('toggleSidebar')?.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });
    </script>
    <script>
        const progressTrendData = @json($progressTrend ?? ['monthly' => [], 'quarterly' => []]);
        const progressTrendChart = document.getElementById('progressTrendChart');
        const progressTrendButtons = document.querySelectorAll('[data-progress-view]');

        function renderProgressTrend(view) {
            if (!progressTrendChart) {
                return;
            }

            const rows = progressTrendData[view] || [];
            const hasAccomplishments = rows.some((row) => Number(row.accomplishment || 0) > 0);

            progressTrendChart.classList.toggle('is-quarterly', view === 'quarterly');
            progressTrendChart.setAttribute('aria-label', `${view === 'quarterly' ? 'Quarterly' : 'Monthly'} accomplishments chart`);

            if (!hasAccomplishments) {
                progressTrendChart.innerHTML = '<div class="progress-trend-empty" style="grid-column: 1 / -1;">No accomplishment data available for this selection.</div>';
                return;
            }

            progressTrendChart.innerHTML = rows.map((row) => {
                const barHeight = Math.max(0, Math.min(100, Number(row.progress || 0)));
                const accomplishment = Number(row.accomplishment || 0).toLocaleString(undefined, { maximumFractionDigits: 0 });

                return `
                    <div class="progress-trend-item" title="${row.label}: ${accomplishment} accomplished">
                        <div class="progress-trend-value">${accomplishment}</div>
                        <div class="progress-trend-bar-track" aria-hidden="true">
                            <div class="progress-trend-bar" style="height: ${Math.max(barHeight, 1)}%;"></div>
                        </div>
                        <div class="progress-trend-label">${row.label}</div>
                    </div>
                `;
            }).join('');
        }

        progressTrendButtons.forEach((button) => {
            button.addEventListener('click', () => {
                progressTrendButtons.forEach((item) => item.classList.remove('active'));
                button.classList.add('active');
                renderProgressTrend(button.dataset.progressView);
            });
        });

        renderProgressTrend('monthly');
    </script>
</body>
</html>
