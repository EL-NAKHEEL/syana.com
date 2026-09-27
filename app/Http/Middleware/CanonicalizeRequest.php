<?php

namespace App\Http\Middleware;

use App\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * One 301 hop to the canonical URL: managed redirects (legacy + admin) first, then
 * https + APP_URL host, lowercase path, no trailing slash, no duplicate slashes, no ?page=1.
 */
class CanonicalizeRequest
{
    /** Paths that are never lowercased (case-sensitive tokens or package routes). */
    private const CASE_SENSITIVE_PREFIXES = ['admin', 'livewire', 'storage', 'build', 'filament'];

    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $rawPath = $request->getPathInfo();

        if ($match = Redirect::lookup($rawPath)) {
            Redirect::recordHit($match['id']);

            if ($match['status'] === 410 || blank($match['to'])) {
                return response()->view('errors.410', status: 410);
            }

            $to = str_starts_with((string) $match['to'], 'http')
                ? (string) $match['to']
                : $this->base($request).rtrim(Redirect::normalizePath((string) $match['to']), '/');

            return redirect()->to($this->withQuery($to, $request), $match['status']);
        }

        $path = $this->normalizePath($rawPath);
        [$query, $queryChanged] = $this->normalizeQuery($request);
        $base = $this->base($request);

        $needsRedirect = $path !== $rawPath
            || $queryChanged
            || $base !== $request->getSchemeAndHttpHost();

        if (! $needsRedirect) {
            return $next($request);
        }

        $target = rtrim($base, '/').($path === '/' ? '' : $path);

        return redirect()->to(($target === '' ? '/' : $target).($query !== '' ? '?'.$query : ''), 301);
    }

    private function normalizePath(string $path): string
    {
        $path = '/'.trim((string) preg_replace('#/{2,}#', '/', $path), '/');
        $first = explode('/', ltrim($path, '/'))[0];

        $isFile = str_contains((string) last(explode('/', $path)), '.');

        if (! $isFile && ! in_array($first, self::CASE_SENSITIVE_PREFIXES, true)) {
            $path = mb_strtolower($path);
        }

        return $path;
    }

    /**
     * Drops ?page=1 and invalid page values (page 1 is the clean URL).
     *
     * @return array{0: string, 1: bool}
     */
    private function normalizeQuery(Request $request): array
    {
        $raw = (string) $request->server('QUERY_STRING');

        if (! $request->query->has('page')) {
            return [$raw, false];
        }

        $page = $request->query('page');

        if (is_string($page) && ctype_digit($page) && (int) $page > 1 && $page === (string) (int) $page) {
            return [$raw, false];
        }

        $query = $request->query();
        unset($query['page']);

        return [http_build_query($query), true];
    }

    private function base(Request $request): string
    {
        if (! config('site.force_canonical_host')) {
            return $request->getSchemeAndHttpHost();
        }

        $appUrl = parse_url((string) config('app.url'));

        return ($appUrl['scheme'] ?? 'https').'://'.($appUrl['host'] ?? $request->getHost())
            .(isset($appUrl['port']) ? ':'.$appUrl['port'] : '');
    }

    private function withQuery(string $url, Request $request): string
    {
        $query = (string) $request->server('QUERY_STRING');

        return $query === '' ? $url : $url.(str_contains($url, '?') ? '&' : '?').$query;
    }
}
