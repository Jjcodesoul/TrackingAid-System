<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class InventoryRegistrationMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array($request->user()?->role, ['admin', 'super_admin'], true)) {
            return $next($request);
        }

        return redirect('dashboard')->with('error', 'You do not have permission to register inventory.');
    }
}
