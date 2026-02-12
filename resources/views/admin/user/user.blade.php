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

            <!-- Create Button – now triggers modal -->
            <button type="button"
                class="px-4 py-2.5 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-3 text-sm mb-2"
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
                        <i class="fa-solid fa-user-plus"></i> Create New User
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body p-4 bg-white">
                    <form method="POST" action="{{ route('users.store') }}" class="space-y-1">
                        @csrf

                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                <input type="text" name="name" required class="form-control" value="{{ old('name') }}"
                                    placeholder="Enter full name" />
                                @error('name') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                <input type="email" name="email" required class="form-control"
                                    value="{{ old('email') }}" placeholder="user@example.com" />
                                @error('email') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" name="password" required class="form-control"
                                    placeholder="••••••••" />
                                @error('password') <div class="text-red-600 text-xs mt-1">{{ $message }}</div> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                <input type="password" name="password_confirmation" required class="form-control"
                                    placeholder="••••••••" />
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Role</label>
                                <select name="role" class="form-select">
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
                                class="px-6 py-2.5 bg-accent text-white font-semibold rounded-xl shadow-md hover:bg-blue-700 transition flex items-center gap-2">
                                <i class="fa-solid fa-floppy-disk"></i> Create User
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
    </script>

</body>

</html>