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
        <input type="text" placeholder="Search"
          class="border rounded-xl pl-10 pr-4 py-2 w-64">
        <span class="absolute left-3 top-2.5"><i class="fa-solid fa-magnifying-glass"></i></span>
      </div>
    </div>
  </div>

<div class="max-w-5xl mx-auto">
  <div class="bg-white rounded-2xl shadow-sm p-4 flex items-center justify-center gap-8">
    <div class="flex items-center gap-2">
      📁 <span class="font-medium">Programs:</span>
      <span class="font-bold">8</span>
    </div>
    <div class="flex items-center gap-2">
      📊 <span class="font-medium">Physical:</span>
      <span class="font-bold">128</span>
    </div>
    <div class="flex items-center gap-2">
      💰 <span class="font-medium">Financial:</span>
      <span class="font-bold">₱4.2M</span>
    </div>
    <div class="flex items-center gap-2">
      ⏱ <span class="font-medium">Completion:</span>
      <span class="font-bold">76%</span>
    </div>
  </div>
</div>


  <!-- TABS -->
  <div class="flex items-center">
    <div class="flex gap-6">
      <button class="font-semibold text-blue-600 border-b-2 border-blue-600 pb-2">
        Physical
      </button>
      <button class="text-gray-400 pb-2">
        Financial
      </button>
    </div>
  </div>

  <!-- TABLE -->
  <div class="bg-white rounded-2xl shadow-sm overflow-hidden mt-3">
    <table class="w-full text-sm">
      <thead class="text-md px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
        <tr>
          <th class="px-6 py-3 font-medium text-left">Programs</th>
          <th class="px-6 py-3 font-medium text-left">Activities</th>
          <th class="px-6 py-3 font-medium text-left">Projects</th>
          <th class="px-6 py-3 font-medium text-left">Target</th>
          <th class="px-6 py-3 font-medium text-left">Accomplished</th>
          <th class="px-6 py-3 font-medium text-left">Progress</th>
          <th class="px-6 py-3 font-medium text-left">Deadline</th>
          <th class="px-6 py-3 font-medium text-left">Status</th>
          <th class="px-6 py-3 font-medium"></th>
        </tr>
      </thead>

      <tbody>
        <tr class="border-b hover:bg-gray-50">
          <td class="p-2 font-semibold flex items-center gap-2">
            NGP
          </td>
          <td class="p-2">Planting</td>
          <td class="p-2">Seedlings</td>
          <td class="p-2">130,000</td>
          <td class="p-2">98,000</td>

          <!-- Progress pill -->
          <td class="p-2">
            <div class="flex items-center gap-3">
              <div class="w-28 h-2 bg-gray-200 rounded-full">
                <div class="h-2 bg-green-500 rounded-full" style="width:75%"></div>
              </div>
              <span class="font-medium">75%</span>
            </div>
          </td>

          <td class="p-2 flex items-center gap-2">
           12/02/2026
          </td>

          <!-- Status -->
          <td class="p-2">
            <span class="flex items-center gap-2 bg-yellow-100 text-yellow-700 px-3 py-1 rounded-full w-fit text-xs">
              <span class="w-2 h-2 bg-yellow-500 rounded-full"></span>
              On Track
            </span>
          </td>

          <!-- Actions -->
          <td class=" text-black text-xl">⋮</td>
        </tr>
      </tbody>
    </table>
  </div>

        </main>
    </div>


</body>

</html>