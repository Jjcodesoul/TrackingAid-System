<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureNotSuperAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_if($request->user()?->role === 'super_admin', 403, 'This operational feature is not available to Super Admin accounts.');

        return $next($request);
    }
}
