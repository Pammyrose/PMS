<?php

namespace App\Http\Controllers;

use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    private const USER_ROLES = ['super-admin', 'admin', 'ro-office', 'ro office', 'penro', 'cenro'];

    public function index()
    {
        $users = User::with('office')
            ->select('id', 'name', 'email', 'role', 'office_id', 'created_at')
            ->latest()
            ->get();
        $offices = Office::query()
            ->orderBy('office_types_id')
            ->orderBy('name')
            ->get();

        return view('admin.user.user', compact('users', 'offices'));
    }

    public function create()
    {
        return redirect()->route('user');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in(self::USER_ROLES)],
            'office_id' => ['nullable', 'required_if:role,penro,cenro', 'exists:offices,id'],
        ]);
        $this->validateRoleOffice($request, $validated);
        $officeId = $this->officeIdForRole($request, $validated);

        // Create the user
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'office_id' => $officeId,
        ]);

        // Flash success message
        return redirect()
            ->route('user')
            ->with('success', "User '{$user->name}' has been created successfully!");
    }

    public function edit(User $user)
    {
        // Optional: protect from editing super-admin or self in dangerous ways
        // if ($user->role === 'super-admin' && auth()->user()->role !== 'super-admin') {
        //     abort(403);
        // }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'office_id' => $user->office_id,
        ]);
    }

    // ── NEW: Update user
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
            'role' => ['required', Rule::in(self::USER_ROLES)],
            'office_id' => ['nullable', 'required_if:role,penro,cenro', 'exists:offices,id'],
        ]);
        $this->validateRoleOffice($request, $validated);
        $officeId = $this->officeIdForRole($request, $validated);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => $validated['role'],
            'office_id' => $officeId,
        ];

        // Only update password if provided
        if ($request->filled('password')) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('user')
            ->with('success', "User '{$user->name}' updated successfully!");
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return redirect()->route('user')
                ->with('error', 'You cannot delete the currently logged-in user.');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('user')
            ->with('success', "User '{$userName}' deleted successfully!");
    }

    private function validateRoleOffice(Request $request, array $validated): void
    {
        $requiredOfficeType = match ($validated['role'] ?? null) {
            'penro' => 2,
            'cenro' => 3,
            default => null,
        };

        if ($requiredOfficeType === null || empty($validated['office_id'])) {
            return;
        }

        $officeMatchesRole = Office::query()
            ->whereKey($validated['office_id'])
            ->where('office_types_id', $requiredOfficeType)
            ->exists();

        if (! $officeMatchesRole) {
            $request->validate([
                'office_id' => [function ($attribute, $value, $fail) use ($validated) {
                    $fail('The selected office does not match the selected ' . strtoupper($validated['role']) . ' role.');
                }],
            ]);
        }
    }

    private function officeIdForRole(Request $request, array $validated): ?int
    {
        if (in_array($validated['role'] ?? null, ['super-admin', 'ro-office', 'ro office'], true)) {
            $regionalOfficeId = Office::query()
                ->where('office_types_id', 1)
                ->where('name', 'RO')
                ->value('id') ?? Office::query()
                ->where('office_types_id', 1)
                ->value('id');

            if ($regionalOfficeId === null) {
                $request->validate([
                    'office_id' => [function ($attribute, $value, $fail) {
                        $fail('Regional Office (RO) is not available. Please seed or create the RO office first.');
                    }],
                ]);
            }

            return (int) $regionalOfficeId;
        }

        return isset($validated['office_id']) ? (int) $validated['office_id'] : null;
    }
}
