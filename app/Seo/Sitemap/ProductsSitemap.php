<?php

namespace App\Seo\Sitemap;

use App\Models\Product;
use Spatie\Sitemap\Tags\Url;

class ProductsSitemap implements SitemapProvider
{
    public function name(): string
    {
        return 'products';
    }

    public function urls(): iterable
    {
        $products = Product::query()->live()->with(['brand', 'media', 'seoMeta'])->get()->filter->isIndexable();

        if ($products->isEmpty()) {
            return;
        }

        yield Url::create(route('store.index'))->setLastModificationDate($products->max(fn (Product $p) => $p->lastModified()));

        foreach ($products as $product) {
            $url = Url::create($product->url());
            if ($modified = $product->lastModified()) {
                $url->setLastModificationDate($modified);
            }
            foreach ($product->media->where('collection_name', 'images') as $media) {
                $url->addImage($media->getFullUrl('large'), $media->getCustomProperty('alt') ?: $product->descriptiveName());
            }
            yield $url;
        }
    }
}
