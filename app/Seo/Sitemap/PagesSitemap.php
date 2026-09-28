<?php

namespace App\Seo\Sitemap;

use App\Models\Page;
use App\Models\Review;
use Illuminate\Support\Carbon;
use Spatie\Sitemap\Tags\Url;

class PagesSitemap implements SitemapProvider
{
    /** Page slug → route name for fixed-route pages. */
    public const ROUTES = [
        'home' => 'home',
        'about' => 'about',
        'contact' => 'contact',
        'warranty' => 'warranty',
        'shipping-returns' => 'shipping-returns',
        'privacy' => 'privacy',
        'terms' => 'terms',
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

        // Reviews hub: indexable once it shows enough approved reviews.
        $reviews = Review::query()->approved();
        if ($reviews->count() >= (int) config('site.seo.hub_min_items')) {
            yield Url::create(route('reviews.index'))->setLastModificationDate(Carbon::parse($reviews->max('approved_at')));
        }
    }
}
