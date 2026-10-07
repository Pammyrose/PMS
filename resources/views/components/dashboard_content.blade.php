<!-- Section Header -->
      <div class="flex flex-col sm:flex-row sm:items-center justify-between mb-8 animate-fade-in">
        <div>
          <h2 class="text-2xl sm:text-3xl font-bold text-gray-900">Performance Overview</h2>
        </div>
      </div>

      @include('components.dashboard_overall_cards')

      <div class="mb-12">
        @include('components.dashboard_performance_rankings')
      </div>

      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 animate-fade-in mb-12">
        <div class="px-6 py-5 border-b border-gray-100 d-flex flex-column flex-lg-row gap-3 align-items-lg-center justify-content-between">
          <div>
            <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3 mb-1">
              <i class="fa-solid fa-triangle-exclamation text-danger"></i> Monthly / Quarterly Delays Chart
            </h3>
            <p class="text-sm text-gray-500 mb-0">
              Delayed physical inputs for {{ (int) ($year ?? now()->year) }}{{ ($selectedSector ?? 'all') !== 'all' ? ' - ' . strtoupper((string) $selectedSector) : '' }}
            </p>
          </div>
          <div class="btn-group progress-trend-toggle" role="group" aria-label="Progress chart range">
            <button type="button" class="btn btn-outline-primary active" data-progress-view="monthly">Monthly</button>
            <button type="button" class="btn btn-outline-primary" data-progress-view="quarterly">Quarterly</button>
          </div>
        </div>

        <div class="p-6">
          <div id="progressTrendChart" class="progress-trend-chart" aria-label="Monthly delays chart"></div>
          <div class="d-flex flex-wrap gap-4 justify-content-between align-items-center mt-4 text-sm text-gray-500">
            <span><span class="d-inline-block rounded me-2" style="width: 12px; height: 12px; background: #dc2626;"></span>Delayed outputs</span>
            <span>Each bar counts inputs where accomplishment is below target.</span>
          </div>
        </div>
      </div>
<script type="application/json" id="dashboardTrendData">@json($progressTrend ?? ['monthly' => [], 'quarterly' => []])</script>
