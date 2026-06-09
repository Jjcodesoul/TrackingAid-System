<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display the user registry.
     */
    public function index(): View
    {
        // Safety: Prevent unauthorized access
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized access to user registry.');
        }

        // Fetch non-admin users for the table
        $users = User::where('role', '!=', 'admin')->get();

        return view('users.index', compact('users'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request): RedirectResponse
    {
        // Enforce admin permission check for storing records too
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            // FIXED: Added 'staff' to match your front-end dropdown configuration choice
            'role' => 'required|string|in:admin,user,staff', 
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        return redirect()->route('users.index')->with('success', 'Personnel authorized successfully.');
    }

    /**
     * Remove the specified user from storage (Disable/Delete).
     */
    public function destroy(User $user): RedirectResponse
    {
        // Safety: Restrict destructive actions to administrators only
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Safety: Prevent deleting your own logged-in session account
        if (Auth::id() === $user->id) {
            return back()->with('error', 'Action denied. You cannot revoke your own access privileges.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User access revoked successfully.');
    }
}