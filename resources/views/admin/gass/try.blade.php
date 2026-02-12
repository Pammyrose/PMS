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
      <div class="flex items-center justify-start mb-6">

        <!-- Left: GASS title -->
        <div>
          <h5 class="text-5xl font-semibold flex items-center gap-2 text-blue-500">
            GASS
          </h5>
        </div>

      </div>
      <div class="w-full h-0.5 bg-blue-500 rounded"></div>

      <!-- FILTER BAR -->
      <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center gap-3">
        <div class="flex items-center gap-2 border rounded-xl px-3 py-2">
          📅 <span class="font-medium">Year:</span> 2025
        </div>
        <div class="flex items-center gap-2 border rounded-xl px-3 py-2">
          Quarter: Q1
        </div>
        <div class="flex items-center gap-2 border rounded-xl px-3 py-2">
          Status: All
        </div>

        <div class="ml-auto flex items-center gap-2">
          <div class="relative">
            <input type="text" placeholder="Search" class="border rounded-xl pl-10 pr-4 py-2 w-64">
            <span class="absolute left-3 top-2.5"><i class="fa-solid fa-magnifying-glass"></i></span>
          </div>
        </div>
      </div>

      <div class="max-w-5xl mx-auto">
        <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center justify-center gap-8">
          <div class="flex items-center gap-2">
            📁 <span class="font-medium">Programs:</span>
            <span class="font-bold">{{ $programs->count() }}</span>
          </div>
          <div class="flex items-center gap-2">
            📊 <span class="font-medium">Physical:</span>
            <span class="font-bold">–</span> <!-- can be calculated later -->
          </div>
          <div class="flex items-center gap-2">
            💰 <span class="font-medium">Financial:</span>
            <span class="font-bold">–</span>
          </div>
          <div class="flex items-center gap-2">
            ⏱ <span class="font-medium">Completion:</span>
            <span class="font-bold">–</span>
          </div>
        </div>
      </div>

      <!-- TABS -->
      <div class="flex items-center mt-6">
        <div class="flex gap-6">
          <a href="{{ route('gass') }}">
            <button class="font-semibold text-blue-600 border-b-2 border-blue-600 pb-2">
              Physical
            </button>
          </a>
          <button class="text-gray-400 pb-2">
            Financial
          </button>
        </div>
      </div>

      <!-- TABLE -->
      <div class="bg-white rounded-2xl shadow-sm mt-3 overflow-hidden">
        <table class="w-full text-sm border-collapse">
          <thead class="text-md bg-gradient-to-r from-primary to-primarydark text-white">
            <tr>
              <th class="px-6 py-4 font-medium text-left">Program / Project / Activity</th>
              <th class="px-6 py-4 font-medium text-right">Target</th>
              <th class="px-6 py-4 font-medium text-center">Progress</th>
              <th class="px-6 py-4 font-medium text-center">Deadline</th>
              <th class="px-6 py-4 font-medium text-center">Status</th>
              <th class="px-6 py-4 w-12"></th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">

            @forelse($programs as $program)
              <!-- Program level – title row -->
              <tr class="bg-indigo-50/80 font-semibold text-base">
                <td class="px-6 py-4 font-semibold flex items-center justify-between" colspan="6">
                  {{ $program->title ?? '—' }}
                </td>
              </tr>

              <!-- Project / Activity level – toggle row -->
              <tr class="bg-gray-50/60 font-medium text-blue-700">
                <td class="px-6 py-4 pl-12 flex items-center justify-between cursor-pointer"
                    onclick="toggleContent('content-{{ $program->id }}', 'icon-{{ $program->id }}')">
                  {{ $program->activities ?: $program->project ?: $program->program ?: '—' }}
                  <i id="icon-{{ $program->id }}" class="ml-8 fa-solid fa-chevron-down transition-transform"></i>
                </td>
                <td class="px-6 py-4 text-right">-</td>
                <td class="px-6 py-4 text-center">-</td>
                <td class="px-6 py-4 text-center">-</td>
                <td class="px-6 py-4 text-center">-</td>
                <td class="px-6 py-4"></td>
              </tr>

              <!-- Sub-activities / collapsible content -->
              <tr id="content-{{ $program->id }}" class="hidden">
                <td colspan="6" class="p-0">
                  <div class="divide-y divide-gray-100">
                    @if(trim($program->subactivities ?? ''))
                      @foreach(explode("\n", trim($program->subactivities)) as $sub)
                        @if(trim($sub))
                          <div class="hover:bg-gray-50">
                            <td class="px-6 py-4 pl-20 text-red-700">
                              {{ trim($sub) }}
                            </td>
                            <td class="px-6 py-4 text-right">-</td>
                            <td class="px-6 py-4">
                              <div class="flex items-center justify-center gap-3">
                                <div class="w-28 h-2 bg-gray-200 rounded-full overflow-hidden">
                                  <div class="h-full bg-blue-500" style="width: 0%"></div>
                                </div>
                                <span class="font-medium">0%</span>
                              </div>
                            </td>
                            <td class="px-6 py-4 text-center whitespace-nowrap">-</td>
                            <td class="px-6 py-4 text-center">-</td>
                            <td class="px-6 py-4 text-center"></td>
                          </div>
                        @endif
                      @endforeach
                    @else
                      <div class="px-6 py-4 pl-20 text-gray-500 italic">
                        No sub-activities recorded
                      </div>
                    @endif
                  </div>
                </td>
              </tr>

            @empty
              <tr>
                <td colspan="6" class="py-12 text-center text-gray-500 italic">
                  No programs recorded yet.
                </td>
              </tr>
            @endforelse

          </tbody>
        </table>
      </div>

    </main>
  </div>

  <script>
  function toggleContent(contentId, iconId) {
    const content = document.getElementById(contentId);
    const icon = document.getElementById(iconId);
    if (content && icon) {
      content.classList.toggle('hidden');
      icon.classList.toggle('rotate-180');
    }
  }
  </script>

</body>
</html>