<?php

namespace App\Seo\Sitemap;

use App\Models\PriceGuide;
use Spatie\Sitemap\Tags\Url;

class PricesSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'prices';
    }

    public function urls(): iterable
    {
        $guides = PriceGuide::query()->published()->with(['services', 'seoMeta'])->get()->filter->isIndexable();

        if ($guides->isEmpty()) {
            return;
        }

        yield Url::create(route('prices.index'))->setLastModificationDate($guides->max(fn (PriceGuide $g) => $g->lastPriceChange() ?? $g->lastModified()));

        foreach ($guides as $guide) {
            $url = Url::create($guide->url());
            // lastmod follows real price changes, like «آخر تحديث».
            if ($modified = $guide->lastPriceChange() ?? $guide->lastModified()) {
                $url->setLastModificationDate($modified);
            }
            yield $url;
        }
    }
}
