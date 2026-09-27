<?php

namespace App\Http\Cache;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\ResponseCache\CacheProfiles\CacheAllSuccessfulGetRequests;
use Symfony\Component\HttpFoundation\Response;

/**
 * Full-page cache for guests only: plain GET pages (pagination allowed), HTML/XML 200 responses.
 * Filtered/sorted variants, previews and anything personal are never cached. The cache is cleared
 * whenever public content changes (App\Listeners\RefreshPublicCaches).
 */
class GuestPageCacheProfile extends CacheAllSuccessfulGetRequests
{
    public function shouldCacheRequest(Request $request): bool
    {
        if (Auth::check() || $request->hasSession() && $request->session()->has('cart')) {
            return false;
        }

        $params = array_diff(
            array_keys($request->query()),
            (array) config('responsecache.ignored_query_parameters'),
            ['page'],
        );

        return $params === [] && parent::shouldCacheRequest($request);
    }

    public function shouldCacheResponse(Response $response): bool
    {
        return $response->getStatusCode() === 200 && parent::shouldCacheResponse($response);
    }

    public function useCacheNameSuffix(Request $request): string
    {
        return '';
    }
}
