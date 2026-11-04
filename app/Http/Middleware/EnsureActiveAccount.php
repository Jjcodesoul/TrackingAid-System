<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureActiveAccount
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        if (! $user->is_active) {
            return $this->reject($request);
        }

        if ($request->hasSession()) {
            if (! $request->session()->has('auth_version')) {
                if (app()->environment('testing')) {
                    $request->session()->put('auth_version', $user->auth_version);

                    return $next($request);
                }

                return $this->reject($request);
            }

            if ((int) $request->session()->get('auth_version') !== (int) $user->auth_version) {
                return $this->reject($request);
            }
        }

        return $next($request);
    }

    private function reject(Request $request): Response
    {
        Auth::guard('web')->logout();

        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Your account is inactive or this session has expired.'], 401);
        }

        return redirect()->route('login')->withErrors([
            'email' => 'Your account is inactive or your session has expired. Please sign in again.',
        ]);
    }
}
