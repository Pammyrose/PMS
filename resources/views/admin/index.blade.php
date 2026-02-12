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

      </div>

      <!-- 1. Overall Performance – Circular Rings + Numbers -->
      <div class="mb-12">
        <h3 class="text-xl font-bold text-gray-800 mb-5 flex items-center gap-3">
          <i class="fa-solid fa-chart-pie text-accent"></i> Overall Performance Snapshot
        </h3>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

          <!-- Overall -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-1">Overall Progress</p>
            <div class="flex items-center justify-center mt-4">
              <div class="relative w-28 h-28">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle cx="50" cy="50" r="44" stroke="#3b82f6" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="calc(276.46 * (1 - 0.74))" stroke-linecap="round" fill="none" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-4xl font-extrabold text-gray-900">74%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Physical -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-1">Physical Targets</p>
            <div class="flex items-center justify-center mt-4">
              <div class="relative w-28 h-28">
                <svg class="w-full h-full -rotate-90" viewBox="0 0 100 100">
                  <circle cx="50" cy="50" r="44" stroke="#e5e7eb" stroke-width="12" fill="none" />
                  <circle cx="50" cy="50" r="44" stroke="#10b981" stroke-width="12" stroke-dasharray="276.46" stroke-dashoffset="calc(276.46 * (1 - 0.82))" stroke-linecap="round" fill="none" />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                  <span class="text-4xl font-extrabold text-emerald-600">82%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Financial -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
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

          <!-- Accomplishment Numbers -->
          <div class="bg-white rounded-2xl shadow-lg p-6 border border-gray-100 group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 flex flex-col justify-center items-center text-center">
            <p class="text-sm text-gray-600 font-medium uppercase tracking-wide mb-3">Total Accomplishments</p>
            <p class="text-5xl font-extrabold text-blue-700">1,284</p>
          </div>

        </div>
      </div>

      <!-- 2. Division Performance – Horizontal Bars (UPDATED DIVISIONS) -->
      <div class="bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 animate-fade-in">
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
          <h3 class="text-xl font-bold text-gray-900 flex items-center gap-3">
            <i class="fa-solid fa-chart-bar text-accent"></i> Division Performance Ranking
          </h3>
          <span class="text-sm text-gray-500">Sorted by progress (highest first)</span>
        </div>

        <div class="p-6 space-y-8">
          <!-- Division Bar Item -->
          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">OFFICE OF THE REGIONAL DIRECTOR</span>
              <span class="text-emerald-600">94%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full transition-all duration-1000" style="width: 94%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 45 | Accomplished: 42</span>
              <span class="text-emerald-700 font-medium">On Track</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">FINANCE DIVISION</span>
              <span class="text-emerald-600">89%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: 89%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 180 | Accomplished: 160</span>
              <span class="text-emerald-700 font-medium">On Track</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">ADMINISTRATIVE DIVISION</span>
              <span class="text-emerald-600">85%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: 85%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 320 | Accomplished: 272</span>
              <span class="text-emerald-700 font-medium">On Track</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">CONSERVATION AND DEVELOPMENT DIVISION</span>
              <span class="text-emerald-600">78%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-emerald-400 to-emerald-600 rounded-full" style="width: 78%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 210 | Accomplished: 164</span>
              <span class="text-emerald-700 font-medium">On Track</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">PLANNING AND MANAGEMENT DIVISION</span>
              <span class="text-amber-600">72%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full" style="width: 72%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 150 | Accomplished: 108</span>
              <span class="text-amber-700 font-medium">Needs Attention</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">LICENSES, PATENTS AND DEEDS DIVISION</span>
              <span class="text-amber-600">68%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full" style="width: 68%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 260 | Accomplished: 177</span>
              <span class="text-amber-700 font-medium">Needs Attention</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">LEGAL DIVISION</span>
              <span class="text-amber-600">65%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-amber-400 to-amber-500 rounded-full" style="width: 65%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 140 | Accomplished: 91</span>
              <span class="text-amber-700 font-medium">Needs Attention</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">ENFORCEMENT DIVISION</span>
              <span class="text-red-600">59%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-red-400 to-red-500 rounded-full" style="width: 59%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 380 | Accomplished: 224</span>
              <span class="text-red-700 font-medium">Delayed</span>
            </div>
          </div>

          <div class="space-y-2">
            <div class="flex justify-between items-center text-sm font-medium">
              <span class="text-gray-800">SURVEYS AND MAPPING DIVISION</span>
              <span class="text-red-600">54%</span>
            </div>
            <div class="h-4 bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-red-400 to-red-500 rounded-full" style="width: 54%"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500">
              <span>Target: 190 | Accomplished: 103</span>
              <span class="text-red-700 font-medium">Delayed</span>
            </div>
          </div>

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