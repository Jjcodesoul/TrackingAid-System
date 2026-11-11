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
        if ($role === 'admin') {
            return $next($request);
        }

        if ($role === 'super_admin') {
            abort(403, 'Super Admin accounts do not have Logistics Administrator access.');
        }

        return redirect('dashboard')->with('error', 'You do not have admin access.');
    }
}