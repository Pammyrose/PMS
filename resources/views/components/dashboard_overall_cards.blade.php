<div class="mb-4">
  <h3 class="text-xl font-bold text-gray-800 mb-3 flex items-center gap-3">
    <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-white bg-primary" style="width: 36px; height: 36px;">
      <i class="fa-solid fa-chart-pie"></i>
    </span>
    Overall Performance
  </h3>

  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
    <div class="bg-white rounded-2xl shadow-lg p-6 border border-primary-subtle position-relative overflow-hidden group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
      <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: #2563eb;"></div>
      <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="text-md text-black font-bold uppercase tracking-wide mb-0">Overall Progress</p>
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-primary" style="width: 38px; height: 38px; background: #eff6ff;">
          <i class="fa-solid fa-gauge-high"></i>
        </span>
      </div>
      <div class="flex items-center justify-center">
        <div class="relative w-28 h-28">
          <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
            <circle cx="50" cy="50" r="44" stroke="#2563eb" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="{{ 276.46 * (1 - ((float) ($overallProgress ?? 0) / 100)) }}" stroke-linecap="round" fill="none" />
          </svg>
          <div class="absolute inset-0 flex flex-col items-center justify-center">
            <span class="text-4xl font-extrabold text-blue-600">{{ number_format((float) ($overallProgress ?? 0), 0) }}%</span>
          </div>
        </div>
      </div>
    </div>

    <button type="button" class="bg-white rounded-2xl shadow-lg p-6 border border-success-subtle position-relative overflow-hidden group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 text-start w-100"
      data-bs-toggle="modal" data-bs-target="#papListModal" aria-label="View all PAP records">
      <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: #059669;"></div>
      <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="text-md text-black font-bold uppercase tracking-wide mb-0">P/A/P's</p>
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-success" style="width: 38px; height: 38px; background: #ecfdf5;">
          <i class="fa-solid fa-folder-tree"></i>
        </span>
      </div>
      <div class="d-flex align-items-end justify-content-center gap-3">
        <p class="text-7xl font-extrabold text-emerald-600 mb-0">{{ number_format((float) ($totalPap ?? 0), 0) }}</p>
   
      </div>
    </button>

    <button type="button" class="bg-white rounded-2xl shadow-lg p-6 border border-info-subtle position-relative overflow-hidden group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 text-start w-100"
      data-bs-toggle="modal" data-bs-target="#indicatorListModal" aria-label="View all indicator records">
      <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: #0891b2;"></div>
      <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="text-md text-black font-bold uppercase tracking-wide mb-0">Indicators</p>
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-info" style="width: 38px; height: 38px; background: #ecfeff;">
          <i class="fa-solid fa-list-check"></i>
        </span>
      </div>
      <div class="d-flex align-items-end justify-content-center gap-3">
        <p class="text-7xl font-extrabold text-blue-700 mb-0">{{ number_format((float) ($totalIndicators ?? 0), 0) }}</p>
      </div>
    </button>

    <div class="bg-white rounded-2xl shadow-lg p-6 border border-warning-subtle position-relative overflow-hidden group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
      <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: #f59e0b;"></div>
      <div class="d-flex align-items-center justify-content-between mb-4">
        <p class="text-md text-black font-bold uppercase tracking-wide mb-0">Financial Utilization</p>
        <span class="d-inline-flex align-items-center justify-content-center rounded-circle text-warning" style="width: 38px; height: 38px; background: #fffbeb;">
          <i class="fa-solid fa-coins"></i>
        </span>
      </div>
      <div class="flex items-center justify-center">
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
  </div>

  <div class="modal fade" id="indicatorListModal" tabindex="-1" aria-labelledby="indicatorListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header text-white" style="background: #0891b2;">
          <div>
            <h5 class="modal-title fw-bold" id="indicatorListModalLabel">Indicator List</h5>
            <p class="mb-0 small text-white-50">
              {{ number_format((float) ($totalIndicators ?? 0), 0) }} record{{ (int) ($totalIndicators ?? 0) === 1 ? '' : 's' }}
              for {{ (int) ($year ?? now()->year) }}{{ ($selectedSector ?? 'all') !== 'all' ? ' - ' . strtoupper((string) $selectedSector) : '' }}
            </p>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          @php
            $dashboardIndicatorList = collect($indicatorList ?? []);
          @endphp

          @if($dashboardIndicatorList->isEmpty())
            <div class="p-5 text-center text-muted">
              <i class="fa-solid fa-list-check fa-2x mb-3 text-info"></i>
              <p class="mb-0 fw-semibold">No indicator records found for this dashboard selection.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4" style="width: 70px;">#</th>
                    <th>Indicator</th>
                    <th>PAP</th>
                    <th style="width: 130px;">Sector</th>
                    <th style="width: 120px;">Year</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($dashboardIndicatorList as $indicator)
                    <tr class="dashboard-link-row" data-href="{{ $indicator['url'] ?? '#' }}" tabindex="0" role="link" aria-label="Open {{ $indicator['name'] ?: 'Untitled Indicator' }}">
                      <td class="ps-4 text-muted fw-semibold">{{ $loop->iteration }}</td>
                      <td class="fw-semibold">
                        <a href="{{ $indicator['url'] ?? '#' }}" class="text-gray-900 text-decoration-none">
                          {{ $indicator['name'] ?: 'Untitled Indicator' }}
                        </a>
                      </td>
                      <td class="text-muted small">{{ $indicator['pap_name'] ?: 'Untitled PAP' }}</td>
                      <td>
                        <span class="badge text-bg-info">{{ $indicator['sector'] ?: 'N/A' }}</span>
                      </td>
                      <td>{{ $indicator['year'] ?: 'N/A' }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="papListModal" tabindex="-1" aria-labelledby="papListModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header text-white" style="background: #059669;">
          <div>
            <h5 class="modal-title fw-bold" id="papListModalLabel">P/A/P List</h5>
            <p class="mb-0 small text-white-50">
              {{ number_format((float) ($totalPap ?? 0), 0) }} record{{ (int) ($totalPap ?? 0) === 1 ? '' : 's' }}
              for {{ (int) ($year ?? now()->year) }}{{ ($selectedSector ?? 'all') !== 'all' ? ' - ' . strtoupper((string) $selectedSector) : '' }}
            </p>
          </div>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body p-0">
          @php
            $dashboardPapList = collect($papList ?? []);
          @endphp

          @if($dashboardPapList->isEmpty())
            <div class="p-5 text-center text-muted">
              <i class="fa-solid fa-folder-open fa-2x mb-3 text-success"></i>
              <p class="mb-0 fw-semibold">No PAP records found for this dashboard selection.</p>
            </div>
          @else
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th class="ps-4" style="width: 70px;">#</th>
                    <th>PAP</th>
                    <th style="width: 130px;">Sector</th>
                    <th style="width: 120px;">Year</th>
                    <th style="width: 150px;">Indicators</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach($dashboardPapList as $pap)
                    <tr class="dashboard-link-row" data-href="{{ $pap['url'] ?? '#' }}" tabindex="0" role="link" aria-label="Open {{ $pap['name'] ?: 'Untitled PAP' }}">
                      <td class="ps-4 text-muted fw-semibold">{{ $loop->iteration }}</td>
                      <td class="fw-semibold">
                        <a href="{{ $pap['url'] ?? '#' }}" class="text-gray-900 text-decoration-none">
                          {{ $pap['name'] ?: 'Untitled PAP' }}
                        </a>
                      </td>
                      <td>
                        <span class="badge text-bg-success">{{ $pap['sector'] ?: 'N/A' }}</span>
                      </td>
                      <td>{{ $pap['year'] ?: 'N/A' }}</td>
                      <td>{{ number_format((int) ($pap['indicator_count'] ?? 0)) }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          @endif
        </div>
      </div>
    </div>
  </div>

  <style>
    #papListModal .dashboard-link-row,
    #indicatorListModal .dashboard-link-row {
      cursor: pointer;
    }

    #papListModal .dashboard-link-row:focus,
    #indicatorListModal .dashboard-link-row:focus {
      outline: 2px solid #059669;
      outline-offset: -2px;
    }
  </style>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      document.querySelectorAll('.dashboard-link-row').forEach(function (row) {
        const openRow = function () {
          const href = row.dataset.href;

          if (href && href !== '#') {
            window.location.href = href;
          }
        };

        row.addEventListener('click', function (event) {
          if (event.target.closest('a')) {
            return;
          }

          openRow();
        });

        row.addEventListener('keydown', function (event) {
          if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            openRow();
          }
        });
      });
    });
  </script>
</div>
