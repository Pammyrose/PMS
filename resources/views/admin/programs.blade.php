<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Program - DENR PMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<style>
    table {
        table-layout: fixed !important;
        width: 100% !important;
    }
</style>

<body>

    <!-- Top navigation bar (full width) -->
    @include('components.nav')

    <!-- Sidebar + Main Content (side-by-side) -->
    <div class="d-flex">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main content wrapper -->
        <main class="flex-grow-1 p-4 bg-gradient-to-b from-gray-50 to-white">

            <div
                class="px-10 py-3 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-lg border-default">
                <h3 class="text-lg font-bold flex items-center">
                    {{ isset($program) ? 'Edit Program / Target' : 'Add Programs' }}
                </h3>
            </div>

            <form id="activityProjectForm" 
                  method="POST" 
                  action="{{ isset($program) ? route('programs.update', $program->id) : route('programs.store') }}"
                  class="p-4 lg:p-10 space-y-1">
                
                @csrf
                
                @if(isset($program))
                    @method('PUT')
                @endif

                @if ($errors->any())
                    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <!-- Row 1 – very wide title + compact selects -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                        <input type="text" name="title" required 
                               value="{{ old('title', $program->title ?? '') }}"
                               class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base"
                               placeholder="Seedlings planted / Area rehabilitated / Patrols conducted" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Program</label>
                        <input type="text" name="program" 
                               value="{{ old('program', $program->program ?? '') }}"
                               class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base"
                               placeholder="e.g. Forest Management Program" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Activity</label>
                        <input type="text" name="activities" required 
                               value="{{ old('activities', $program->activities ?? '') }}"
                               class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Sub Activity</label>
                        <input type="text" name="subactivities" required 
                               value="{{ old('subactivities', $program->subactivities ?? '') }}"
                               class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Project</label>
                        <input type="text" name="project" 
                               value="{{ old('project', $program->project ?? '') }}"
                               class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base"
                               placeholder="seedlings / ha / man-days" />
                    </div>
                </div>

                <!-- Submit – prominent but compact -->
                <div class="flex justify-end mt-2">
                    <button type="submit"
                        class="px-3 py-3 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-3 text-sm">
                        <i class="fa-solid fa-floppy-disk"></i>
                        {{ isset($program) ? 'Update' : 'Save' }}
                    </button>
                </div>

            </form>

            <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-lg border border-default">

                <table class="w-full text-sm text-left rtl:text-right">
                    <thead
                        class="text-md px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium w-64">Title</th> <!-- ~256px -->
                            <th scope="col" class="px-6 py-3 font-medium w-48">Program</th> <!-- ~192px -->
                            <th scope="col" class="px-6 py-3 font-medium w-56">Activity</th> <!-- ~224px -->
                            <th scope="col" class="px-6 py-3 font-medium w-56">Sub Activity</th> <!-- ~224px -->
                            <th scope="col" class="px-6 py-3 font-medium w-40">Project</th> <!-- ~160px -->
                            <th scope="col" class="px-6 py-3 font-medium w-24 text-center">Action</th> <!-- narrow -->
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($programs as $program_item)
                            <tr class="bg-neutral-primary border-b border-default">
                                <th scope="row" class="px-6 py-4 font-medium text-heading whitespace-nowrap truncate">
                                    {{ $program_item->title ?? '—' }}
                                </th>
                                <td class="px-6 py-4 truncate">
                                    {{ $program_item->program ?? '—' }}
                                </td>
                                <td class="px-6 py-4 truncate">
                                    {{ $program_item->activities ?? '—' }}
                                </td>
                                <td class="px-6 py-4 truncate">
                                    {{ $program_item->subactivities ?? '—' }}
                                </td>
                                <td class="px-6 py-4 truncate">
                                    {{ $program_item->project ?? '—' }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('programs.edit', $program_item->id) }}"
                                       class="font-medium text-fg-brand text-blue-600 hover:underline">
                                        Edit
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    No programs added yet.
                                </td>
                            </tr>
                        @endforelse
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