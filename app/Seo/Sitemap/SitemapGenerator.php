<?php

namespace App\Seo\Sitemap;

use Illuminate\Support\Facades\Storage;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\SitemapIndex;
use Spatie\Sitemap\Tags\Sitemap as SitemapTag;

/**
 * Writes /sitemap.xml (index) and one child per provider to local storage. Empty children are omitted.
 */
class SitemapGenerator
{
    public const DIRECTORY = 'sitemaps';

    /**
     * @return array<int, SitemapProvider>
     */
    public function providers(): array
    {
        return array_map(fn (string $class) => app($class), (array) config('site.sitemaps'));
    }

    public function generate(): void
    {
        $disk = Storage::disk('local');
        $index = SitemapIndex::create();
        $written = [];

        foreach ($this->providers() as $provider) {
            $sitemap = Sitemap::create();
            $latest = null;

            foreach ($provider->urls() as $url) {
                $sitemap->add($url);
                $modified = $url->lastModificationDate;
                if ($modified !== null && ($latest === null || $modified->greaterThan($latest))) {
                    $latest = $modified;
                }
            }

            $path = self::DIRECTORY.'/'.$provider->name().'.xml';

            if ($sitemap->getTags() === []) {
                $disk->delete($path);

                continue;
            }

            $disk->put($path, $sitemap->render());
            $written[] = $provider->name();

            $tag = SitemapTag::create(route('sitemap.child', ['name' => $provider->name()]));
            if ($latest !== null) {
                $tag->setLastModificationDate($latest);
            }
            $index->add($tag);
        }

        $disk->put(self::DIRECTORY.'/index.xml', $index->render());
        $disk->put(self::DIRECTORY.'/.generated', implode(',', $written));
    }

    public function path(string $name): ?string
    {
        $path = self::DIRECTORY.'/'.$name.'.xml';

        return Storage::disk('local')->exists($path) ? $path : null;
    }

    /**
     * Every URL listed across all child sitemaps (used by the SEO test suite).
     *
     * @return array<int, string>
     */
    public function allUrls(): array
    {
        $urls = [];

        foreach ($this->providers() as $provider) {
            foreach ($provider->urls() as $url) {
                $urls[] = $url->url;
            }
        }

        return $urls;
    }
}
