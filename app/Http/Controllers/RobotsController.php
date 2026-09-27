<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Production allows everything except private areas (CSS/JS/images and AI-search crawlers are never
     * blocked). Every other environment disallows all crawling.
     */
    public function __invoke(): Response
    {
        $lines = app()->isProduction()
            ? [
                'User-agent: *',
                'Allow: /',
                'Disallow: /admin',
                'Disallow: /cart',
                'Disallow: /checkout',
                'Disallow: /search',
                '',
                'Sitemap: '.route('sitemap.index'),
            ]
            : ['User-agent: *', 'Disallow: /'];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
