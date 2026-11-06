<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Throwable;

class UserController extends Controller
{
    /**
     * Display the user registry.
     */
    public function index(Request $request): View
    {
        $this->authorizeUserManagement();

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'role' => ['nullable', 'in:admin,user,staff,delivery_personnel'],
            'is_active' => ['nullable', 'in:0,1'],
        ]);

        $usersQuery = User::where('role', '!=', 'super_admin');

        if (! empty($validated['search'])) {
            $search = $validated['search'];
            $usersQuery->where(fn ($query) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%"));
        }
        if (! empty($validated['role'])) {
            $usersQuery->where('role', $validated['role']);
        }
        if (array_key_exists('is_active', $validated)) {
            $usersQuery->where('is_active', (bool) $validated['is_active']);
        }

        return view('users.index', [
            'users' => $usersQuery->orderBy('name')->paginate(20)->withQueryString(),
            'filters' => $validated,
            'isSuperAdmin' => Auth::user()->role === 'super_admin',
        ]);
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeUserManagement();

        if ((!$request->filled('first_name') || !$request->filled('last_name')) && $request->filled('name')) {
            $parts = explode(' ', trim((string) $request->input('name')), 2);
            $request->merge([
                'first_name' => $request->filled('first_name') ? $request->input('first_name') : ($parts[0] ?? ''),
                'last_name' => $request->filled('last_name') ? $request->input('last_name') : ($parts[1] ?? ''),
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'role' => 'required|string|in:admin,user,staff,delivery_personnel',
        ]);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'name' => trim("{$validated['first_name']} {$validated['last_name']}"),
            'email' => $validated['email'],
            // The user sets a real password through Laravel's expiring reset token.
            'password' => Hash::make(Str::random(64)),
            'role' => $validated['role'],
            'must_change_password' => true,
        ]);

        try {
            $setupEmailSent = Password::sendResetLink(['email' => $user->email]) === Password::RESET_LINK_SENT;
        } catch (Throwable $exception) {
            report($exception);
            $setupEmailSent = false;
        }

        if (! $setupEmailSent) {
            return redirect()->route('users.index')->with('error', 'The account was created, but the setup email could not be sent. Ask the user to use Forgot password after mail delivery is restored.');
        }

        return redirect()->route('users.index')->with('success', 'Account created. A secure password setup link was sent to '.$user->email.'.');
    }

    /**
     * Render the individual user edit form view.
     */
    public function edit($id): View
    {
        $this->authorizeUserManagement();

        $user = User::findOrFail($id);
        abort_if($user->role === 'super_admin', 403, 'Super Admin accounts cannot be changed through user management.');

        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user's information.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->authorizeUserManagement();

        $user = User::findOrFail($id);
        abort_if($user->role === 'super_admin', 403, 'Super Admin accounts cannot be changed through user management.');

        if ((!$request->filled('first_name') || !$request->filled('last_name')) && $request->filled('name')) {
            $parts = explode(' ', trim((string) $request->input('name')), 2);
            $request->merge([
                'first_name' => $request->filled('first_name') ? $request->input('first_name') : ($parts[0] ?? ''),
                'last_name' => $request->filled('last_name') ? $request->input('last_name') : ($parts[1] ?? ''),
            ]);
        }

        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|string|in:admin,user,staff,delivery_personnel',
        ]);

        $user->first_name = $validated['first_name'];
        $user->last_name = $validated['last_name'];
        $user->name = trim("{$validated['first_name']} {$validated['last_name']}");
        $user->email = $validated['email'];
        $user->role = $validated['role'];

        $user->save();

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified user from storage (Disable/Delete).
     */
    public function destroy($id): RedirectResponse
    {
        $this->authorizeUserManagement();

        $user = User::findOrFail($id);
        abort_if($user->role === 'super_admin', 403, 'Super Admin accounts cannot be removed through user management.');

        // Safety: Prevent deleting your own logged-in session account
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Action denied. You cannot revoke your own access privileges.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User access revoked successfully.');
    }

    private function authorizeUserManagement(): void
    {
        abort_unless(
            in_array(Auth::user()?->role, ['admin', 'super_admin'], true),
            403,
            'Unauthorized action.'
        );
    }
}
