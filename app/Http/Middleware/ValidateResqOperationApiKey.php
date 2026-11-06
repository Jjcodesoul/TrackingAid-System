<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateResqOperationApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.resqoperation.api_key', '');
        if ($configuredKey === '') {
            return response()->json(['message' => 'Request intake is not configured.'], 503);
        }

        $providedKey = (string) $request->header('X-API-KEY', '');
        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json(['message' => 'Invalid API key.'], 401);
        }

        return $next($request);
    }
}
