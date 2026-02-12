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

<div class="flex items-center gap-2 bg-gray-100 px-3 py-1 rounded-lg w-fit">
  <span class="text-md text-gray-500">Office:</span>
  <span class="font-semibold text-lg text-blue-700">PMD</span>
</div>

<table class="w-full text-sm text-left">
  <thead class="bg-gradient-to-r from-primary to-primarydark text-white border-b-2 border-gray-300">
    <tr>
      <th class="px-6 py-3 border border-gray-300">P/A/P</th>
      <th class="px-6 py-3 text-center border border-gray-300">Indicators</th>
      <th class="px-6 py-3 text-center border border-gray-300">Office</th>

      <th class="px-6 py-3 text-center border border-gray-300">Physical
        <div class="grid grid-cols-2 gap-2 text-center mt-2">
          <span class="border-r border-gray-300 pr-2">To Date %</span>
          <span class="pl-2">Annual %</span>
        </div>

      </th>

      <th class="px-6 py-3 text-center border border-gray-300">Financial
        <div class="grid grid-cols-4 gap-2 text-center mt-2">
          <span class="border-r border-gray-300 pr-2">Expense Class</span>
          <span class="border-r border-gray-300 pr-2">Oblig/Allot %</span>
          <span class="border-r border-gray-300 pr-2">Disb/Allot %</span>
          <span class="pl-2">Disb/Oblig %</span>
        </div>
      </th>

      <th class="px-6 py-3 text-center">Remarks</th>
    </tr>
  </thead>

  <tbody>
    <tr class="hover:bg-gray-50 border border-gray-300">
      <th class="px-6 py-4 border border-gray-300 font-medium">PMD</th>
      <td class="px-6 py-4 border border-gray-300">130,000</td>
      <td class="px-6 py-4 text-center border border-gray-300">CAR</td>

      <td class="px-6 py-4 border border-gray-300">  
        <div class="grid grid-cols-2 gap-2 text-center">
          <span class="border-r border-gray-300 pr-2">100%</span>
          <span class="pl-2">100%</span>
        </div>
      </td>
      
      <td class="px-6 py-4 border border-gray-300">
        <div class="grid grid-cols-4 gap-2 text-center">
          <span class="border-r border-gray-300 pr-2">PS</span>
          <span class="border-r border-gray-300 pr-2">100%</span>
          <span class="border-r border-gray-300 pr-2">100%</span>
          <span class="pl-2">100%</span>
        </div>
      </td>
      <td class="px-6 py-4 text-center border border-gray-300">Completed</td>
    </tr>
  </tbody>
</table>
     

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