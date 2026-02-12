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

<div class="flex items-center gap-3">      
<select name="office_single" id="office_single"
  class="w-48 px-2 py-2 mb-3 border border-gray-300 rounded-lg text-base">
  <option value="">Select Office</option>
  <option value="regional">Regional</option>
  <option value="alfonso">CENRO Alfonso Lista</option>
  <option value="baguio">CENRO Baguio</option>
  <option value="bangued">CENRO Bangued</option>
  <option value="buguias">CENRO Buguias</option>
  <option value="calanasan">CENRO Calanasan</option>
  <option value="conner">CENRO Conner</option>
  <option value="lagangilang">CENRO Lagangilang</option>
  <option value="lamut">CENRO Lamut</option>
  <option value="paracelis">CENRO Paracelis</option>
  <option value="pinukpuk">CENRO Pinukpuk</option>
  <option value="sabangan">CENRO Sabangan</option>
  <option value="tabuk">CENRO Tabuk</option>
  <option value="cti">CTI</option>
  <option value="penro_abra">PENRO Abra</option>
  <option value="penro_apayao">PENRO Apayao</option>
  <option value="penro_benguet">PENRO Benguet</option>
  <option value="penro_ifugao">PENRO Ifugao</option>
  <option value="penro_kalinga">PENRO Kalinga</option>
  <option value="penro_mtprovince">PENRO Mt. Province</option>
</select>
<select name="office_single" id="office_single"
  class="w-48 px-2 py-2 mb-3 border border-gray-300 rounded-lg text-base">
  <option value="">Select Department</option>
  <option value="regional">PMD</option>
  <option value="alfonso">CD</option>
  <option value="baguio">FD</option>
  <option value="bangued">AD</option>
  <option value="buguias">CDD</option>
  <option value="calanasan">LPDD</option>
  <option value="conner">SMD</option>
  <option value="lagangilang">ED</option>
</select>
<select name="year" class="w-48 px-2 py-2 mb-3 border border-gray-300 rounded-lg text-base">
  <option value="">Select Year</option>
  <option>2026</option>
  <option>2025</option>
  <option>2024</option>
  <option>2023</option>
</select>

</div>
            <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-lg border border-default">
                
                <table class="w-full text-sm text-left rtl:text-right text-body">
                    <thead class="text-md px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Office
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Target
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Accomplishments
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Budget
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-neutral-primary border-b border-default">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                PMD
                            </th>
                            <td class="px-6 py-4">
                                130,000
                            </td>
                            <td class="px-6 py-4">
                                120,000
                            </td>
                            <td class="px-6 py-4">
                                50,000
                            </td>


                            <td class="px-6 py-4">
                                <a href="{{ route('accomplishment_list') }}" class="font-medium text-fg-brand text-blue-600 hover:underline">Open</a>
                            </td>
                        </tr>
                        
                    </tbody>
                </table>
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