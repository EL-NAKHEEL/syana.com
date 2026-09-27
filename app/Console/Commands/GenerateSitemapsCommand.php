<?php

namespace App\Console\Commands;

use App\Seo\Sitemap\SitemapGenerator;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:sitemap')]
#[Description('Regenerate the XML sitemaps')]
class GenerateSitemapsCommand extends Command
{
    public function handle(SitemapGenerator $generator): int
    {
        $generator->generate();
        $this->info('Sitemaps generated.');

        return self::SUCCESS;
    }
}
