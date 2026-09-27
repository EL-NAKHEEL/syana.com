<?php

namespace App\Seo\Sitemap;

use Spatie\Sitemap\Tags\Url;

interface SitemapProvider
{
    /** Child sitemap name, e.g. "pages" → /sitemaps/pages.xml */
    public function name(): string;

    /**
     * Only indexable, published, canonical 200 URLs, with lastmod from real content changes.
     *
     * @return iterable<Url>
     */
    public function urls(): iterable;
}
