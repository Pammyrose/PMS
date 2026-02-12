<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Target - DENR PMS</title>
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





        
<a href="{{ route('target_form') }}"><button class="px-4 py-2 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-3 text-sm mb-2">Create</button></a>
            <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-lg border border-default">
                
                <table class="w-full text-sm text-left rtl:text-right">
                    <thead class="text-md px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Programs
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Activities
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Projects
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Target
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Deadline
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Assigned Office/s
                            </th>
                            <th scope="col" class="px-6 py-3 font-medium">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-neutral-primary border-b border-default">
                            <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap">
                                NGP
                            </th>
                            <td class="px-6 py-4">
                                Planting
                            </td>
                            <td class="px-6 py-4">
                                Seedlings
                            </td>
                            <td class="px-6 py-4">
                                130,000
                            </td>
                            <td class="px-6 py-4">
                                12/02/2026
                            </td>
                            <td class="px-6 py-4">
                                PMD
                            </td>
                            <td class="px-6 py-4">
                                <a href="#" class="font-medium text-fg-brand text-blue-600 hover:underline">Edit</a>
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