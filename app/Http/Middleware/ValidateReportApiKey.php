<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateReportApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredKey = (string) config('services.trackingaid_reports.api_key', '');

        if ($configuredKey === '') {
            return response()->json([
                'status' => 'error',
                'message' => 'Report API authentication is not configured.',
            ], 503);
        }

        $providedKey = (string) $request->header('X-API-KEY', '');

        if ($providedKey === '' || ! hash_equals($configuredKey, $providedKey)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid or missing API key.',
            ], 401);
        }

        return $next($request);
    }
}
