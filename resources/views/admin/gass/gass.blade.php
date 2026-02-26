<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard - {{ config('app.name', 'Laravel') }}</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
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
            <span class="font-bold">3</span>
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
      <div class="flex items-center mt-4">
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
              <th class="px-6 py-4 font-medium text-left">Sub-activity / Indicators</th>
              <th class="px-6 py-4 font-medium text-center">Office</th>
              <th class="px-6 py-4 font-medium text-center">Target</th>
              <th class="px-6 py-4 font-medium text-center">Deadline</th>
              <th class="px-6 py-4 font-medium text-center">Progress</th>
              <th class="px-6 py-4 w-12"></th>
            </tr>
          </thead>

          <tbody class="divide-y divide-gray-100">

            <!-- Example Program 1 -->
            <tr class="bg-indigo-50/80 font-semibold text-base">
              <td class="px-6 py-4 font-bold flex items-center justify-between" colspan="6">
                <span>
                  Tree Planting Program
                  <span class="text-gray-600 font-normal text-sm ml-3">
                    • Environmental Conservation
                  </span>
                </span>
              </td>
            </tr>

            <tr class="bg-gray-50/60 font-medium text-blue-700">
              <td class="px-6 py-4 pl-12 grid grid-rows-2 gap-y-1 cursor-pointer group"
                onclick="toggleRow('content-1', 'icon-1')">
                <div class="flex items-center justify-between">
                  <span class="text-md">
                    Project: Reforestation Initiative 2025
                  </span>
                  <i id="icon-1" class="fa-solid fa-chevron-down transition-transform group-hover:text-indigo-600"></i>
                </div>
                <span class="text-sm text-gray-700 font-medium">
                  Community Tree Planting Activities
                </span>
              </td>
              <td class="px-6 py-4 text-right"></td>
              <td class="px-6 py-4 text-center"></td>
              <td class="px-6 py-4 text-center"></td>
              <td class="px-6 py-4 text-center"></td>
              <td class="px-6 py-4"></td>
            </tr>

            <tr id="content-1" class="hidden">
              <td colspan="6" class="p-0">
                <table class="w-full">
                  <tbody>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 pl-20 text-red-700 font-medium">
                        Urban Greening
                      </td>
                      <td class="px-6 py-4 pl-20 text-gray-700 font-medium">
                        <div class="text-xs font-bold text-red-700">Indicators</div>
                        Number of seedlings planted
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-xs font-bold text-red-700">Office</div>
                        <span class="text-xs">DENR (Biodiversity, Climate)
                        </span>
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-xs font-bold text-red-700">Target</div>
                        500,000 seedlings
                      </td>
                      <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div class="text-xs font-bold text-red-700">Deadline</div>
                        30 Jun 2025
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-xs font-bold text-center text-red-700">Progress</div>
                        <div class="flex items-center justify-center gap-3">
                          <div class="w-28 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: 65%"></div>
                          </div>
                          <span class="font-medium">65%</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-left">
                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')"
                          class="text-gray-600 hover:text-gray-900">
                          <i class="fa-solid fa-ellipsis-v text-lg"></i>
                        </button>
                        <div
                          class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50">
                          <button type="button"
                            class="block px-4 py-3 text-sm hover:bg-gray-50 w-full text-left bg-transparent border-0"
                            data-bs-toggle="modal" data-bs-target="#editTargetModal"
                            data-program-id="1"
                            data-indicator-id="1"
                            data-indicator-name="Number of seedlings planted"
                            data-target="500,000 seedlings"
                            data-deadline="2025-06-30"
                            data-office-id="1">
                            Edit Indicator
                          </button>
                          <a href="#"
                            class="block px-4 py-3 text-sm hover:bg-gray-50">
                            Add Accomplishment
                          </a>
                          <a href="#" class="block px-4 py-3 text-sm hover:bg-gray-50">
                            Generate Report
                          </a>
                          <hr class="my-1 border-gray-300">
                          <button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 pl-20 text-gray-600 italic">
                      </td>
                      <td class="px-6 py-4 pl-20 text-gray-700 font-medium">
                        <div class="text-xs font-bold text-red-700">Indicators</div>
                        Area covered (hectares)
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-xs font-bold text-red-700">Office</div>
                        <span class="text-xs">DENR (Biodiversity, Climate)</span>
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-xs font-bold text-red-700">Target</div>
                        100 hectares
                      </td>
                      <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div class="text-xs font-bold text-red-700">Deadline</div>
                        30 Jun 2025
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-xs font-bold text-center text-red-700">Progress</div>
                        <div class="flex items-center justify-center gap-3">
                          <div class="w-28 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: 78%"></div>
                          </div>
                          <span class="font-medium">78%</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-left">
                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')"
                          class="text-gray-600 hover:text-gray-900">
                          <i class="fa-solid fa-ellipsis-v text-lg"></i>
                        </button>
                        <div
                          class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50">
                          <button type="button"
                            class="block px-4 py-3 text-sm hover:bg-gray-50 w-full text-left bg-transparent border-0"
                            data-bs-toggle="modal" data-bs-target="#editTargetModal"
                            data-program-id="1"
                            data-indicator-id="2"
                            data-indicator-name="Area covered (hectares)"
                            data-target="100 hectares"
                            data-deadline="2025-06-30"
                            data-office-id="1">
                            Edit Indicator
                          </button>
                          <a href="#"
                            class="block px-4 py-3 text-sm hover:bg-gray-50">
                            Add Accomplishment
                          </a>
                          <a href="#" class="block px-4 py-3 text-sm hover:bg-gray-50">
                            Generate Report
                          </a>
                          <hr class="my-1 border-gray-300">
                          <button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>

            <!-- Example Program 2 -->
            <tr class="bg-indigo-50/80 font-semibold text-base">
              <td class="px-6 py-4 font-bold flex items-center justify-between" colspan="6">
                <span>
                  Disaster Risk Reduction Program
                  <span class="text-gray-600 font-normal text-sm ml-3">
                    • Calamity Management
                  </span>
                </span>
              </td>
            </tr>

            <tr class="bg-gray-50/60 font-medium text-blue-700">
              <td class="px-6 py-4 pl-12 grid grid-rows-2 gap-y-1 cursor-pointer group"
                onclick="toggleRow('content-2', 'icon-2')">
                <div class="flex items-center justify-between">
                  <span class="text-md">
                    Project: Emergency Response Capability
                  </span>
                  <i id="icon-2" class="fa-solid fa-chevron-down transition-transform group-hover:text-indigo-600"></i>
                </div>
                <span class="text-sm text-gray-700 font-medium">
                  Community Preparedness & Response
                </span>
              </td>
              <td class="px-6 py-4 text-right"></td>
              <td class="px-6 py-4 text-center"></td>
              <td class="px-6 py-4 text-center"></td>
              <td class="px-6 py-4 text-center"></td>
              <td class="px-6 py-4"></td>
            </tr>

            <tr id="content-2" class="hidden">
              <td colspan="6" class="p-0">
                <table class="w-full">
                  <tbody>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 pl-20 text-red-700 font-medium">
                        Evacuation Drills
                      </td>
                      <td class="px-6 py-4 pl-20 text-gray-700 font-medium">
                        <div class="text-xs font-bold text-red-700">Indicators</div>
                        Number of drills conducted
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-xs font-bold text-red-700">Office</div>
                        <span class="text-xs">NDRRMC (Disasters)</span>
                      </td>
                      <td class="px-6 py-4 text-center">
                        <div class="text-xs font-bold text-red-700">Target</div>
                        12 drills
                      </td>
                      <td class="px-6 py-4 text-center whitespace-nowrap">
                        <div class="text-xs font-bold text-red-700">Deadline</div>
                        31 Dec 2025
                      </td>
                      <td class="px-6 py-4">
                        <div class="text-xs font-bold text-center text-red-700">Progress</div>
                        <div class="flex items-center justify-center gap-3">
                          <div class="w-28 h-2 bg-gray-200 rounded-full overflow-hidden">
                            <div class="h-full bg-emerald-500" style="width: 50%"></div>
                          </div>
                          <span class="font-medium">50%</span>
                        </div>
                      </td>
                      <td class="px-6 py-4 text-left">
                        <button type="button" onclick="this.nextElementSibling.classList.toggle('hidden')"
                          class="text-gray-600 hover:text-gray-900">
                          <i class="fa-solid fa-ellipsis-v text-lg"></i>
                        </button>
                        <div
                          class="hidden absolute right-0 mt-1 w-48 bg-white border border-gray-200 rounded-xl shadow-xl z-50">
                          <button type="button"
                            class="block px-4 py-3 text-sm hover:bg-gray-50 w-full text-left bg-transparent border-0"
                            data-bs-toggle="modal" data-bs-target="#editTargetModal"
                            data-program-id="2"
                            data-indicator-id="3"
                            data-indicator-name="Number of drills conducted"
                            data-target="12 drills"
                            data-deadline="2025-12-31"
                            data-office-id="2">
                            Edit Indicator
                          </button>
                          <a href="#"
                            class="block px-4 py-3 text-sm hover:bg-gray-50">
                            Add Accomplishment
                          </a>
                          <a href="#" class="block px-4 py-3 text-sm hover:bg-gray-50">
                            Generate Report
                          </a>
                          <hr class="my-1 border-gray-300">
                          <button class="w-full text-left px-4 py-3 text-sm text-red-600 hover:bg-red-50">
                            Delete
                          </button>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </td>
            </tr>

          </tbody>
        </table>
      </div>

    </main>
  </div>

  <!-- Edit Target / Deadline Modal -->
  <!-- Edit Target / Deadline Modal -->
  <div class="modal fade" id="editTargetModal" tabindex="-1" aria-labelledby="editTargetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow-lg">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="editTargetModalLabel">Edit Indicator, Target & Deadline</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form id="editTargetForm" method="POST" action="">
          @csrf
          @method('PATCH')

          <div class="modal-body">
            <input type="hidden" name="indicator_id" id="modal_indicator_id">

            <!-- Performance Indicator -->
            <div class="mb-4">
              <label for="modal_indicator_name" class="form-label fw-bold">Performance Indicator</label>
              <input type="text" name="name" id="modal_indicator_name" class="form-control form-control-lg"
                placeholder="e.g. Number of seedlings planted">
              @error('name')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <!-- Target -->
            <div class="mb-4">
              <label for="modal_target" class="form-label fw-bold">Target</label>
              <input type="text" name="target" id="modal_target" class="form-control form-control-lg"
                placeholder="e.g. 500,000 seedlings">
              @error('target')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <!-- Office -->
            <div class="mb-4">
              <label for="modal_office_id" class="form-label fw-bold">Office</label>
              <select name="office_id" id="modal_office_id" class="form-control form-control-lg">
                <option value="">-- Select Office --</option>
                <option value="1">DENR (Biodiversity, Climate)</option>
                <option value="2">NDRRMC (Disasters)</option>
                <option value="3">PAGASA (Weather)</option>
                <option value="4">BFP (Fire Services)</option>
                <option value="5">PNP (Police)</option>
              </select>
              @error('office_id')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
            </div>

            <!-- Deadline -->
            <div class="mb-4">
              <label for="modal_deadline" class="form-label fw-bold">Deadline</label>
              <input type="date" name="deadline" id="modal_deadline" class="form-control form-control-lg">
              @error('deadline')
                <div class="text-danger small mt-1">{{ $message }}</div>
              @enderror
              <small class="form-text text-muted">Expected completion date</small>
            </div>
          </div>

          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
            <button type="button" id="saveIndicatorBtn" class="btn btn-primary px-3">
              <i class="fas fa-save"></i> Save
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- Toast Container (top-right position) -->
  <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 9999;">
    <!-- Success Toast -->
    <div id="successToast" class="toast bg-success text-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-success text-white">
        <strong class="me-auto">Success</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="successMessage">
        Indicator saved successfully!
      </div>
    </div>

    <!-- Error Toast -->
    <div id="errorToast" class="toast bg-danger text-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-danger text-white">
        <strong class="me-auto">Error</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="errorMessage">
        Something went wrong.
      </div>
    </div>

    <!-- Info / Confirmation Toast (optional) -->
    <div id="infoToast" class="toast bg-info text-white" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="toast-header bg-info text-white">
        <strong class="me-auto">Please confirm</strong>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
      <div class="toast-body" id="infoMessage">
        Are you sure you want to save changes?
      </div>
    </div>
  </div>

  <script>
    function toggleRow(contentId, iconId) {
      const content = document.getElementById(contentId);
      const icon = document.getElementById(iconId);
      if (content && icon) {
        content.classList.toggle('hidden');
        icon.classList.toggle('rotate-180');
      }
    }

    document.addEventListener('show.bs.modal', function (event) {
      const button = event.relatedTarget;
      const programId = button.getAttribute('data-program-id');
      const indicatorId = button.getAttribute('data-indicator-id');
      const name = button.getAttribute('data-indicator-name');
      const target = button.getAttribute('data-target');
      const deadline = button.getAttribute('data-deadline');
      const officeId = button.getAttribute('data-office-id');

      const form = document.getElementById('editTargetForm');

      // Set form action based on whether we're creating or updating
      if (indicatorId && indicatorId.trim() !== '') {
        form.action = `/admin/gass/indicators/${indicatorId}`;
        form.querySelector('input[name="_method"]').value = 'PATCH';
      } else {
        form.action = `/admin/gass/indicators`;
        form.querySelector('input[name="_method"]').value = 'POST';
      }

      // Add program_id to form data
      let programIdInput = form.querySelector('input[name="program_id"]');
      if (!programIdInput) {
        programIdInput = document.createElement('input');
        programIdInput.type = 'hidden';
        programIdInput.name = 'program_id';
        form.appendChild(programIdInput);
      }
      programIdInput.value = programId || '';

      document.getElementById('modal_indicator_id').value = indicatorId || '';
      document.getElementById('modal_indicator_name').value = name || '';
      document.getElementById('modal_target').value = target || '';
      document.getElementById('modal_deadline').value = deadline || '';
      document.getElementById('modal_office_id').value = officeId || '';
    });

    // Make sure Bootstrap is already loaded (you have it in head)

    const successToastEl = document.getElementById('successToast');
    const errorToastEl = document.getElementById('errorToast');
    const infoToastEl = document.getElementById('infoToast');

    const successToast = new bootstrap.Toast(successToastEl);
    const errorToast = new bootstrap.Toast(errorToastEl);
    const infoToast = new bootstrap.Toast(infoToastEl);

    document.getElementById('saveIndicatorBtn')?.addEventListener('click', async function () {
      const form = document.getElementById('editTargetForm');
      if (!form || !form.action) {
        showErrorToast('Cannot save: no indicator selected or form not found');
        return;
      }

      // Show confirmation toast (non-blocking style)
      document.getElementById('infoMessage').textContent = "Save this indicator?";
      infoToast.show();

      // Wait for user to confirm (you can use a timeout or better: add Yes/No buttons)
      // For simplicity here we still use confirm() — but you can enhance later
      if (!confirm('Are you sure you want to save this indicator?')) {
        infoToast.hide();
        return;
      }

      infoToast.hide();

      try {
        const formData = new FormData(form);

        const response = await fetch(form.action, {
          method: 'POST',  // Always use POST - let _method field handle the override
          body: formData,
          headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
          }
        });

        let result;
        try {
          result = await response.json();
        } catch {
          throw new Error('Invalid JSON response from server');
        }

        if (!response.ok) {
          throw new Error(result.message || `Server error (${response.status})`);
        }

        // Success
        document.getElementById('successMessage').textContent =
          result.message || 'Indicator saved successfully!';

        successToast.show();

        // Close modal
        bootstrap.Modal.getInstance(document.getElementById('editTargetModal'))?.hide();

        // Refresh after short delay so user sees the toast
        setTimeout(() => {
          location.reload();
        }, 1200);

      } catch (err) {
        console.error('Save error:', err);
        document.getElementById('errorMessage').textContent =
          err.message || 'Error saving indicator. Please try again.';
        errorToast.show();
      }
    });

    // Optional: Helper functions
    function showErrorToast(message) {
      document.getElementById('errorMessage').textContent = message;
      errorToast.show();
    }

    function showSuccessToast(message) {
      document.getElementById('successMessage').textContent = message;
      successToast.show();
    }
  </script>

</body>

</html>