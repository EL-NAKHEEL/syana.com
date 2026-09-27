<?php

use App\Http\Middleware\CanonicalizeRequest;
use App\Http\Middleware\LogNotFound;
use App\Http\Middleware\NoindexOutsideProduction;
use App\Http\Middleware\ResetRequestState;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Middlewares\CacheResponse;
use Spatie\ResponseCache\Middlewares\DoNotCacheResponse;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind a CDN/load balancer the scheme must be trusted, or the https redirect would loop.
        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '*'));

        $middleware->prepend([ResetRequestState::class, CanonicalizeRequest::class]);
        $middleware->append([SecurityHeaders::class, NoindexOutsideProduction::class, LogNotFound::class]);

        $middleware->preventRequestForgery(except: ['csp-report']);

        $middleware->alias([
            'page-cache' => CacheResponse::class,
            'no-page-cache' => DoNotCacheResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
