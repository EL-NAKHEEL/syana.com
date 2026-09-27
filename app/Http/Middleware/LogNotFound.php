<?php

namespace App\Http\Middleware;

use App\Models\NotFoundLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Records every GET 404 so the admin can turn frequent ones into redirects.
 */
class LogNotFound
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() === 404 && $request->isMethod('GET')) {
            try {
                NotFoundLog::record($request);
            } catch (\Throwable $e) {
                report($e);
            }
        }

        return $response;
    }
}
