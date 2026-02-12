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

            <!-- Very wide container with controlled padding -->
            <div class="max-w-9xl mx-auto px-4 sm:px-6 lg:px-10">

                <div class="bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden">

                    <div class="px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white">
                        <h3 class="text-lg font-bold flex items-center">

                            New Programs
                        </h3>

                    </div>

                    <form id="activityProjectForm" method="POST" action="{{ route('targets.store') }}"
                        class="p-8 lg:p-10 space-y-7">
                        @csrf
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
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Title *</label>
                                <select name="title" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base">
                                    <option value="" disabled {{ old('title') ? '' : 'selected' }}>
                                        Select title...
                                    </option>

                                    @foreach($programs as $program)
                                        <option value="{{ $program->title }}" {{ old('title') == $program->title ? 'selected' : '' }}>
                                            {{ $program->title }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Program</label>
                                <select name="program" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base">
                                    <option value="" disabled {{ old('program') ? '' : 'selected' }}>
                                        Select program...
                                    </option>

                                    @foreach($programs as $program)
                                        <option value="{{ $program->program }}" {{ old('program') == $program->program ? 'selected' : '' }}>
                                            {{ $program->program }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Activity</label>
                                <select name="activities" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base">
                                    <option value="" disabled {{ old('activities') ? '' : 'selected' }}>
                                        Select activities...
                                    </option>

                                    @foreach($programs as $program)
                                        <option value="{{ $program->activities }}" {{ old('activities') == $program->activities ? 'selected' : '' }}>
                                            {{ $program->activities }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Sub Activity</label>
                                <select name="subactivities" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base">
                                    <option value="" disabled {{ old('subactivities') ? '' : 'selected' }}>
                                        Select sub-activities...
                                    </option>

                                    @foreach($programs as $program)
                                        <option value="{{ $program->subactivities }}" {{ old('subactivities') == $program->subactivities ? 'selected' : '' }}>
                                            {{ $program->subactivities }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-1 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Project</label>
                                <select name="project" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base">
                                    <option value="" disabled {{ old('project') ? '' : 'selected' }}>
                                        Select project...
                                    </option>

                                    @foreach($programs as $program)
                                        <option value="{{ $program->project }}" {{ old('project') == $program->project ? 'selected' : '' }}>
                                            {{ $program->project }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>



                        <!-- Description – compact height -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Performance Indicators /
                                Expected Outcome</label>
                            <textarea name="indicators" rows="3"
                                class="w-full px-2 py-3.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-accent focus:border-accent text-base"
                                placeholder="Main purpose and expected outcome...">{{ old('indicators') }}</textarea>
                        </div>


                        <!-- Budget + Dates + Status – four columns -->
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Target Quantity *</label>
                                <input type="number" name="target" min="0" required
                                    class="w-full px-2 py-3.5 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base"
                                    value="{{ old('target') }}" placeholder="25000" />
                            </div>
                            <div class="lg:col-span-2">
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Allotted Budget (PHP)
                                    *</label>
                                <div class="relative">
                                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500">₱</span>
                                    <input type="number" name="budget" min="0" step="any" required
                                        class="w-full px-5 pr-2 py-3.5 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base"
                                        value="{{ old('budget') }}" placeholder="2500000" />
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Start Date</label>
                                <input type="date" name="start_date" required
                                    class="w-full px-2 py-3.5 border border-gray-300 rounded-lg text-base"
                                    value="{{ old('start_date') }}" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Target End Date</label>
                                <input type="date" name="end_date" required
                                    class="w-full px-2 py-3.5 border border-gray-300 rounded-lg text-base"
                                    value="{{ old('end_date') }}" />
                            </div>
                        </div>


                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <!-- PENRO Dropdown -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Assigned Office*</label>
                                <select id="penro_id" name="penro_id"
                                    class="w-full px-3 py-3.5 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base"
                                    required>
                                    <option value="" selected disabled>Select office level</option>

                                    @foreach($penros as $penro)
                                        <option value="{{ $penro->id }}"
                                            data-is-top-level="{{ $penro->parent_id === null ? 'true' : 'false' }}">
                                            {{ $penro->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- CENRO Dropdown (populated via JS) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1.5">Assigned CENRO</label>
                                <select id="cenro_id" name="cenro_id"
                                    class="w-full px-3 py-3.5 border border-gray-300 rounded-lg focus:ring-accent focus:border-accent text-base disabled:bg-gray-200 disabled:text-gray-600 disabled:cursor-not-allowed"
                                    **disabled**>
                                    <option value="">Select CENRO (after choosing Office)</option>
                                </select>
                            </div>
                        </div>


                        <!-- Checkboxes -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            @foreach(['PMD', 'CDD', 'LD', 'LPDD', 'FD', 'SMD', 'AD', 'ED'] as $div)
                                <label class="flex items-center space-x-2">
                                    <input type="checkbox" name="div[]" value="{{ $div }}" {{ in_array($div, old('div', [])) ? 'checked' : '' }} />
                                    <span>{{ $div }}</span>
                                </label>
                            @endforeach
                        </div>


                        <!-- Submit – prominent but compact -->
                        <div class="flex justify-end">
                            <button type="submit"
                                class="px-5 py-3 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-3 text-sm">
                                <i class="fa-solid fa-floppy-disk"></i>
                                Save
                            </button>
                        </div>

                    </form>
                </div>
            </div>

        </main>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Mobile sidebar toggle (if you still use it)
        document.getElementById('toggleSidebar')?.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });

        document.getElementById('penro_id')?.addEventListener('change', function () {
            const cenroSelect = document.getElementById('cenro_id');

            // Always start disabled + reset
            cenroSelect.disabled = true;
            cenroSelect.innerHTML = '<option value="">Select CENRO...</option>';

            const selectedOption = this.options[this.selectedIndex];
            const penroId = this.value?.trim() || '';

            if (!penroId) {
                cenroSelect.innerHTML = '<option value="">Please select PENRO first</option>';
                return;
            }

            // Check if selected PENRO is top-level (parent_id null)
            const isTopLevel = selectedOption.dataset.isTopLevel === 'true';

            if (isTopLevel) {
                cenroSelect.innerHTML = '<option value="" disabled selected>No CENROs for this office</option>';
                // stays disabled
                return;
            }

            // Only fetch if NOT top-level
            cenroSelect.innerHTML = '<option value="">Loading CENROs...</option>';

            fetch(`/offices/${penroId}/cenros`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error(`HTTP ${response.status}`);
                    return response.json();
                })
                .then(data => {
                    cenroSelect.innerHTML = '<option value="">Select CENRO</option>';

                    if (!Array.isArray(data) || data.length === 0) {
                        cenroSelect.innerHTML = '<option value="" disabled>No CENROs found</option>';
                        // still disabled
                    } else {
                        data.forEach(item => {
                            cenroSelect.appendChild(new Option(item.name, item.id));
                        });
                        cenroSelect.disabled = false;   // only enable here
                    }
                })
                .catch(err => {
                    console.error('Error:', err);
                    cenroSelect.innerHTML = '<option value="" disabled>Error loading</option>';
                });
        });

        let isSubmitting = false;

        document.getElementById('activityProjectForm').addEventListener('submit', function (e) {
            if (isSubmitting) {
                e.preventDefault();           // stop the second submission
                return;
            }

            isSubmitting = true;

            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Saving...';
            }

            // Optional: re-enable after some time (if redirect fails)
            // setTimeout(() => { isSubmitting = false; submitBtn.disabled = false; }, 10000);
        });
    </script>
</body>

</html>