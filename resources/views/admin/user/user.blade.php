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

    <div id="saveSuccessAlertWrapper" class="position-fixed top-20 end-0 p-3 d-none" style="z-index: 1090; max-width: 360px; width: 100%;">
        <div id="saveSuccessAlert" class="alert alert-success alert-dismissible fade show shadow mb-0" role="alert">
            <strong>Success!</strong> <span id="saveSuccessMessage">Data saved successfully.</span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
    </div>

    <div id="saveErrorAlertWrapper" class="position-fixed top-0 end-0 p-3 d-none" style="z-index: 1090; max-width: 420px; width: 100%;">
        <div id="saveErrorAlert" class="alert alert-danger alert-dismissible fade show shadow mb-0" role="alert">
            <strong>Error:</strong> <span id="saveErrorMessage">Something went wrong.</span>
            <button type="button" class="btn-close" aria-label="Close"></button>
        </div>
    </div>

    <!-- Top navigation bar (full width) -->
    @include('components.nav')

    <!-- Sidebar + Main Content (side-by-side) -->
    <div class="d-flex">
        <!-- Sidebar -->
        @include('components.sidebar')

        <!-- Main content wrapper -->
        <main class="flex-grow-1 p-4 bg-gradient-to-b from-gray-50 to-white">

            <!-- Create Button – now triggers modal -->
            <button type="button"
                class="px-4 py-2.5 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-3 text-sm mb-2"
                id="openCreateUserModalBtn"
                data-bs-toggle="modal" data-bs-target="#createUserModal">
                Create User
            </button>

            <div class="relative overflow-x-auto bg-neutral-primary-soft shadow-xs rounded-lg border border-default">

                <table class="w-full text-sm text-left rtl:text-right">
                    <thead
                        class="text-md px-10 py-4 bg-gradient-to-r from-primary to-primarydark text-white border-b rounded-base border-default">
                        <tr>
                            <th scope="col" class="px-6 py-3 font-medium">Name</th>
                            <th scope="col" class="px-6 py-3 font-medium">Email</th>
                            <th scope="col" class="px-6 py-3 font-medium">Roles</th>
                            <th scope="col" class="px-6 py-3 font-medium">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="bg-neutral-primary border-b border-default">
                                <th scope="row" class="px-6 py-4 font-medium">{{ $user->name ?? '—' }}</th>
                                <td class="px-6 py-4">{{ $user->email ?? '—' }}</td>
                                <td class="px-6 py-4">{{ $user->role ?? 'N/A' }}</td>
                                <td class="px-6 py-4 flex gap-3">
                                    <button type="button" class="text-blue-600 hover:underline edit-user-btn"
                                        data-bs-toggle="modal" data-bs-target="#createUserModal"
                                        data-user-id="{{ $user->id }}">
                                        Edit
                                    </button>
                                    <!-- Add delete with confirmation if needed -->
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">
                                    No users found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </main>
    </div>

    <!-- ========================== CREATE USER MODAL ========================== -->
    <div class="modal fade" id="createUserModal" tabindex="-1" aria-labelledby="createUserModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content rounded-xl overflow-hidden shadow-2xl">

                <!-- Modal Header -->
                <div class="modal-header bg-gradient-to-r from-primary to-primarydark text-white border-0 px-5 py-4">
                    <h5 class="modal-title text-lg font-bold flex items-center gap-3" id="createUserModalLabel">
                        <i class="fa-solid fa-user-plus"></i> <span id="userModalTitleText">Create New User</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-white">
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-1" id="userForm">
                        @csrf
                        <input type="hidden" name="_method" id="userFormMethod" value="PUT" disabled>
                        <input type="hidden" name="user_id" id="userIdField" value="{{ old('user_id') }}">

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="userName" required class="form-control" value="{{ old('name') }}"
                                    placeholder="Enter full name" />
                                @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" id="userEmail" required class="form-control"
                                    value="{{ old('email') }}" placeholder="user@example.com" />
                                @error('email') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" id="userPassword" required class="form-control"
                                    placeholder="••••••••" />
                                <div class="text-muted text-xs mt-1 d-none" id="passwordEditHint">Leave blank to keep current password.</div>
                                @error('password') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" id="userPasswordConfirmation" required class="form-control"
                                    placeholder="••••••••" />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" id="userRole" class="form-select" required>
                                    <option value="">— Select Role —</option>
                                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                    <option value="staff" {{ old('role') == 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="user" {{ old('role') == 'user' ? 'selected' : '' }}>User</option>
                                </select>
                                @error('role') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="flex justify-end gap-3 mt-6 pt-4 border-t border-gray-100">
                            <button type="button"
                                class="px-6 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition"
                                data-bs-dismiss="modal">
                                Cancel
                            </button>
                            <button type="submit"
                                class="px-6 py-2.5 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-2"
                                id="userFormSubmitBtn">
                                <i class="fa-solid fa-floppy-disk"></i> <span id="userFormSubmitText">Create User</span>
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </div>
    </div>
    <!-- ========================== END CREATE USER MODAL ========================== -->

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Optional: mobile sidebar toggle script -->
    <script>
        document.getElementById('toggleSidebar')?.addEventListener('click', function () {
            document.querySelector('.sidebar').classList.toggle('d-none');
        });

        let saveSuccessAlertTimeout = null;
        let saveErrorAlertTimeout = null;

        function showTopRightSuccessAlert(message = 'Data saved successfully.', options = {}) {
            const {
                duration = 2200,
            } = options;

            const saveSuccessAlertWrapper = document.getElementById('saveSuccessAlertWrapper');
            const saveSuccessAlert = document.getElementById('saveSuccessAlert');
            const saveSuccessMessage = document.getElementById('saveSuccessMessage');

            if (!saveSuccessAlertWrapper || !saveSuccessAlert) {
                console.warn(message);
                return;
            }

            if (saveSuccessMessage) {
                saveSuccessMessage.textContent = message;
            }

            saveSuccessAlertWrapper.classList.remove('d-none');

            if (saveSuccessAlertTimeout) {
                clearTimeout(saveSuccessAlertTimeout);
            }

            const closeButton = saveSuccessAlert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.onclick = function () {
                    if (saveSuccessAlertTimeout) {
                        clearTimeout(saveSuccessAlertTimeout);
                    }
                    saveSuccessAlertWrapper.classList.add('d-none');
                };
            }

            saveSuccessAlertTimeout = setTimeout(() => {
                saveSuccessAlertWrapper.classList.add('d-none');
            }, duration);
        }

        function showTopRightErrorAlert(message = 'An error occurred.', options = {}) {
            const {
                duration = 2600,
            } = options;

            const saveErrorAlertWrapper = document.getElementById('saveErrorAlertWrapper');
            const saveErrorAlert = document.getElementById('saveErrorAlert');
            const saveErrorMessage = document.getElementById('saveErrorMessage');

            if (!saveErrorAlertWrapper || !saveErrorAlert) {
                console.warn(message);
                return;
            }

            if (saveErrorMessage) {
                saveErrorMessage.textContent = message;
            }

            saveErrorAlertWrapper.classList.remove('d-none');

            if (saveErrorAlertTimeout) {
                clearTimeout(saveErrorAlertTimeout);
            }

            const closeButton = saveErrorAlert.querySelector('.btn-close');
            if (closeButton) {
                closeButton.onclick = function () {
                    if (saveErrorAlertTimeout) {
                        clearTimeout(saveErrorAlertTimeout);
                    }
                    saveErrorAlertWrapper.classList.add('d-none');
                };
            }

            saveErrorAlertTimeout = setTimeout(() => {
                saveErrorAlertWrapper.classList.add('d-none');
            }, duration);
        }

        document.addEventListener('DOMContentLoaded', function () {
            const createButton = document.getElementById('openCreateUserModalBtn');
            const modalElement = document.getElementById('createUserModal');
            const userModal = modalElement ? bootstrap.Modal.getOrCreateInstance(modalElement) : null;
            const form = document.getElementById('userForm');
            const methodInput = document.getElementById('userFormMethod');
            const userIdField = document.getElementById('userIdField');
            const nameInput = document.getElementById('userName');
            const emailInput = document.getElementById('userEmail');
            const roleInput = document.getElementById('userRole');
            const passwordInput = document.getElementById('userPassword');
            const passwordConfirmationInput = document.getElementById('userPasswordConfirmation');
            const modalTitleText = document.getElementById('userModalTitleText');
            const submitText = document.getElementById('userFormSubmitText');
            const passwordEditHint = document.getElementById('passwordEditHint');

            const storeUrl = @json(route('users.store'));
            const editUrlTemplate = @json(route('users.edit', ['user' => '__USER_ID__']));
            const updateUrlTemplate = @json(route('users.update', ['user' => '__USER_ID__']));

            const setCreateMode = (clearFields = true) => {
                if (!form) return;

                form.action = storeUrl;
                if (methodInput) {
                    methodInput.disabled = true;
                    methodInput.value = 'PUT';
                }

                if (modalTitleText) modalTitleText.textContent = 'Create New User';
                if (submitText) submitText.textContent = 'Create User';

                if (passwordInput) passwordInput.required = true;
                if (passwordConfirmationInput) passwordConfirmationInput.required = true;
                if (passwordEditHint) passwordEditHint.classList.add('d-none');

                if (clearFields) {
                    if (userIdField) userIdField.value = '';
                    if (nameInput) nameInput.value = '';
                    if (emailInput) emailInput.value = '';
                    if (roleInput) roleInput.value = '';
                    if (passwordInput) passwordInput.value = '';
                    if (passwordConfirmationInput) passwordConfirmationInput.value = '';
                }
            };

            const setEditMode = (user) => {
                if (!form || !user?.id) return;

                form.action = updateUrlTemplate.replace('__USER_ID__', String(user.id));

                if (methodInput) {
                    methodInput.disabled = false;
                    methodInput.value = 'PUT';
                }

                if (userIdField) userIdField.value = String(user.id);
                if (nameInput) nameInput.value = user.name || '';
                if (emailInput) emailInput.value = user.email || '';
                if (roleInput) roleInput.value = user.role || '';
                if (passwordInput) {
                    passwordInput.required = false;
                    passwordInput.value = '';
                }
                if (passwordConfirmationInput) {
                    passwordConfirmationInput.required = false;
                    passwordConfirmationInput.value = '';
                }

                if (modalTitleText) modalTitleText.textContent = 'Edit User';
                if (submitText) submitText.textContent = 'Update User';
                if (passwordEditHint) passwordEditHint.classList.remove('d-none');
            };

            createButton?.addEventListener('click', function () {
                setCreateMode(true);
            });

            document.querySelectorAll('.edit-user-btn').forEach(button => {
                button.addEventListener('click', async function () {
                    const userId = this.getAttribute('data-user-id');
                    if (!userId) return;

                    try {
                        const response = await fetch(editUrlTemplate.replace('__USER_ID__', String(userId)), {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                            },
                        });

                        if (!response.ok) {
                            throw new Error('Failed to load user details.');
                        }

                        const user = await response.json();
                        setEditMode(user);
                    } catch (error) {
                        console.error(error);
                        showTopRightErrorAlert('Unable to load user details for editing. Please try again.');
                    }
                });
            });

            const oldMethod = @json(old('_method'));
            const oldUserId = @json(old('user_id'));
            const hasValidationErrors = @json($errors->any());
            const flashSuccess = @json(session('success'));
            const flashError = @json(session('error'));
            const validationErrorMessage = @json($errors->any() ? $errors->first() : null);

            if (flashSuccess) {
                showTopRightSuccessAlert(flashSuccess);
            }

            if (flashError) {
                showTopRightErrorAlert(flashError);
            }

            if (hasValidationErrors) {
                if (oldMethod === 'PUT' && oldUserId) {
                    setEditMode({
                        id: oldUserId,
                        name: @json(old('name')),
                        email: @json(old('email')),
                        role: @json(old('role')),
                    });
                }

                if (validationErrorMessage) {
                    showTopRightErrorAlert(validationErrorMessage);
                }

                userModal?.show();
            }
        });
    </script>

</body>

</html>