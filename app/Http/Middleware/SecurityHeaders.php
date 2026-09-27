<?php

namespace App\Http\Middleware;

use App\Support\InlineScripts;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline hardening headers (PLAN.md §6.9). CSP ships report-only until SECURITY_CSP_ENFORCE=true;
 * HSTS only once SECURITY_HSTS=true (after HTTPS is verified on the production host).
 * Public pages carry no nonce: the only inline script is allow-listed by hash, which keeps
 * full-page response caching compatible with CSP.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $headers = $response->headers;

        $headers->set('X-Content-Type-Options', 'nosniff');
        $headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $headers->set('X-Frame-Options', 'SAMEORIGIN');
        $headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=(), payment=(), usb=(), interest-cohort=()');
        $headers->set('Cross-Origin-Opener-Policy', 'same-origin');

        if (config('site.security.hsts') && $request->isSecure()) {
            $headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        if (! $this->isAdmin($request)) {
            $headers->set(
                config('site.security.csp_enforce') ? 'Content-Security-Policy' : 'Content-Security-Policy-Report-Only',
                $this->contentSecurityPolicy(),
            );
        }

        return $response;
    }

    public function contentSecurityPolicy(): string
    {
        $google = 'https://www.googletagmanager.com';
        $analytics = 'https://*.google-analytics.com https://*.analytics.google.com';
        // Google Ads call conversions (carried over from the existing site).
        $ads = 'https://www.googleadservices.com https://googleads.g.doubleclick.net https://*.doubleclick.net https://www.google.com';

        return implode('; ', [
            "default-src 'self'",
            "script-src 'self' ".InlineScripts::cspHashes()." {$google} {$ads}",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https:",
            "font-src 'self'",
            "connect-src 'self' {$analytics} {$google} {$ads}",
            'frame-src https://www.google.com https://www.youtube-nocookie.com https://td.doubleclick.net https://www.googletagmanager.com',
            "object-src 'none'",
            "base-uri 'self'",
            "form-action 'self' https://wa.me https://api.whatsapp.com",
            "frame-ancestors 'self'",
            'report-uri '.config('site.security.csp_report_uri'),
        ]);
    }

    private function isAdmin(Request $request): bool
    {
        return $request->is('admin', 'admin/*', 'livewire*', 'filament/*');
    }
}
