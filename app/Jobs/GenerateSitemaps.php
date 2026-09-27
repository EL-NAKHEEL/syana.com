<?php

namespace App\Jobs;

use App\Seo\Sitemap\SitemapGenerator;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Debounced: many saves within a minute produce one regeneration.
 */
class GenerateSitemaps implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 60;

    public function handle(SitemapGenerator $generator): void
    {
        $generator->generate();
    }
}
