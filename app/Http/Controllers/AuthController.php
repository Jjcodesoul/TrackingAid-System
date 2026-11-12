<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\LoginHistoryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function login(Request $request, LoginHistoryService $loginHistory)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || ! $user->is_active || !Hash::check($credentials['password'], $user->password)) {
            $loginHistory->record($user, $credentials['email'], false, $request);

            return response()->json([
                'message' => 'Invalid email or password.'
            ], 401);
        }

        $loginHistory->record($user, $credentials['email'], true, $request);

        // Remove old tokens for this user
        $user->tokens()->delete();

        // Create a new token for the mobile app
        $token = $user->createToken('mobile-app')->plainTextToken;

          return response()->json([
            'message' => 'Login successful.',
            'token' => $token,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'role' => $user->role,
                'must_change_password' => $user->must_change_password,
            ],
        ], 200);
    }

    public function sendPasswordResetLink(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => 'Please wait before requesting another password reset email.',
            ], 429);
        }

        return response()->json([
            'message' => 'If an account exists with that email address, a password reset link will be sent.',
        ], 200);
    }


    public function verifyPasswordAndSendResetLink(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'string'],
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.',
            ], 422);
        }

        $status = Password::sendResetLink([
            'email' => $user->email,
        ]);

        if ($status === Password::RESET_THROTTLED) {
            return response()->json([
                'message' => 'Please wait before requesting another password reset email.',
            ], 429);
        }

        return response()->json([
            'message' => 'A password reset link has been sent to your registered email address.',
        ], 200);
    }


    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'new_password' => ['required', 'string', 'min:8', 'different:current_password'],
        ]);

        $user = $request->user();

        // Verify the current password
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'Current password is incorrect.'
            ], 422);
        }

        // Update password
        $user->password = Hash::make($request->new_password);

        // Remove first-login requirement
        $user->must_change_password = false;

        $user->save();

        return response()->json([
            'message' => 'Password updated successfully.',
            'must_change_password' => false,
        ], 200);
    }
}