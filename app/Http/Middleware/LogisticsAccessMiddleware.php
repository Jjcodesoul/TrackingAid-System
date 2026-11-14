<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class LogisticsAccessMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->user()?->role;

        abort_unless(
            in_array($role, ['super_admin','admin', 'staff'], true),
            403,
            'You do not have permission to access logistics operations.'
        );

        return $next($request);
    }
}