<?php

namespace App\Seo\Sitemap;

use App\Catalog\Catalog;
use App\Catalog\Facet;
use App\Models\Brand;
use App\Models\FacetPage;
use Spatie\Sitemap\Tags\Url;

/**
 * Only indexable curated facets: published intro + ≥ 3 live products (PLAN.md §6.3).
 */
class FacetsSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'facets';
    }

    public function urls(): iterable
    {
        $pages = FacetPage::query()->published()->with(['brand', 'seoMeta'])->get();

        foreach ($pages as $page) {
            $facet = match ($page->kind) {
                'brand' => $page->brand instanceof Brand ? Facet::brand($page->brand) : null,
                'capacity' => Facet::capacity((float) $page->hp),
                'type' => $page->type !== null && isset(Catalog::TYPES[$page->type]) ? Facet::type($page->type) : null,
                'brand_capacity' => $page->brand instanceof Brand ? Facet::brandCapacity($page->brand, (float) $page->hp) : null,
                default => null,
            };

            if ($facet === null || ($page->brand !== null && ! $page->brand->isPublished())) {
                continue;
            }

            $count = $facet->products()->count();
            if (! $facet->isIndexable($count)) {
                continue;
            }

            $url = Url::create($facet->url());
            if ($modified = $facet->lastPriceChange() ?? $page->lastModified()) {
                $url->setLastModificationDate(max($modified, $page->lastModified() ?? $modified));
            }
            yield $url;
        }
    }
}
