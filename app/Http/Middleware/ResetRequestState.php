<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Starts every request with fresh request-scoped services (Seo, Navigation). PHP-FPM gets this for free;
 * long-lived workers (Octane) and the test client reuse the container between requests.
 */
class ResetRequestState
{
    public function handle(Request $request, Closure $next): Response
    {
        app()->forgetScopedInstances();

        return $next($request);
    }
}
