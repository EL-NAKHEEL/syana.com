<?php

namespace App\Seo\Sitemap;

use App\Models\Page;
use Spatie\Sitemap\Tags\Url;

class PagesSitemap implements SitemapProvider
{
    /** Page slug → route name for fixed-route pages. */
    public const ROUTES = [
        'home' => 'home',
        'about' => 'about',
        'contact' => 'contact',
    ];

    public function name(): string
    {
        return 'pages';
    }

    public function urls(): iterable
    {
        $pages = Page::query()->published()->with('seoMeta')->whereIn('slug', array_keys(self::ROUTES))->get();

        foreach ($pages as $page) {
            if (! $page->isIndexable()) {
                continue;
            }

            $url = Url::create(route(self::ROUTES[$page->slug]));

            if ($modified = $page->lastModified()) {
                $url->setLastModificationDate($modified);
            }

            yield $url;
        }
    }
}
