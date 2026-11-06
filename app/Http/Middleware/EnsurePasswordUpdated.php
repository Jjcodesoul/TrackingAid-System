<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordUpdated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()?->must_change_password
            && ! in_array($request->route()?->getName(), ['profile.edit', 'password.update', 'logout'], true)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Update your password before continuing.'], 403);
            }

            return redirect()->route('profile.edit')->with('password_setup_required', 'Update your temporary password before continuing.');
        }

        return $next($request);
    }
}
