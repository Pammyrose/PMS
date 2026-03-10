<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

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
<a href="{{ route('gass_physical') }}" class="text-decoration-none">
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
          </a>

          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center items-center text-center">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-1">Financial Utilization</p>
            <div class="flex items-center justify-center mt-4">
              <div class="relative w-28 h-28">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle cx="50" cy="50" r="44" stroke="#f59e0b" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="calc(276.46 * (1 - 0.67))" stroke-linecap="round" fill="none" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-4xl font-extrabold text-amber-500">67%</span>
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

      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 animate-fade-in">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
            <i class="fa-solid fa-chart-bar text-accent"></i> Field Performance Ranking
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
            <div class="space-y-2">
              <div class="flex justify-between items-center text-sm font-medium">
                <span class="text-gray-800">{{ $field['label'] }}</span>
                <span class="{{ $textClass }}">{{ number_format($progress, 0) }}%</span>
              </div>
              <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
                <div class="h-full {{ $barClass }} rounded-full" style="width: {{ $progress }}%"></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500">
                <span>Target: {{ number_format((float) ($field['target_total'] ?? 0), 0) }} | Accomplished: {{ number_format((float) ($field['accomp_total'] ?? 0), 0) }}</span>
                <span class="font-medium {{ $textClass }}">{{ $field['status'] }}</span>
              </div>
            </div>
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
</body>
</html>