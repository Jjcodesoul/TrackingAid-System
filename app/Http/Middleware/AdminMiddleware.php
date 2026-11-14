<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        if (in_array($role, ['admin', 'super_admin'], true)) {
            return $next($request);
        }

        return redirect('dashboard')->with('error', 'You do not have admin access.');
    }
}