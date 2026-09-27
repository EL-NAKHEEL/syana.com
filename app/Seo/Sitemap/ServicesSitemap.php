<?php

namespace App\Seo\Sitemap;

use App\Models\Service;
use Spatie\Sitemap\Tags\Url;

class ServicesSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'services';
    }

    public function urls(): iterable
    {
        $services = Service::query()->live()->with('seoMeta')->get()->filter->isIndexable();

        if ($services->isEmpty()) {
            return;
        }

        yield Url::create(route('services.index'))->setLastModificationDate($services->max(fn (Service $s) => $s->lastModified()));

        foreach ($services as $service) {
            $url = Url::create($service->url());
            if ($modified = $service->lastModified()) {
                $url->setLastModificationDate($modified);
            }
            yield $url;
        }
    }
}
