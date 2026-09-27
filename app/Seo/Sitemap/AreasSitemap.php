<?php

namespace App\Seo\Sitemap;

use App\Models\Area;
use Spatie\Sitemap\Tags\Url;

class AreasSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'areas';
    }

    public function urls(): iterable
    {
        $areas = Area::live()->filter->isIndexable();

        // The hub is noindex (and so out of the sitemap) until it lists enough areas.
        if ($areas->count() >= (int) config('site.seo.hub_min_items')) {
            yield Url::create(route('areas.index'))->setLastModificationDate($areas->max(fn (Area $a) => $a->lastModified()));
        }

        foreach ($areas as $area) {
            $url = Url::create($area->url());
            if ($modified = $area->lastModified()) {
                $url->setLastModificationDate($modified);
            }
            yield $url;
        }
    }
}
