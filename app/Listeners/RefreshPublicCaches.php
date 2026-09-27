<?php

namespace App\Listeners;

use App\Events\PublicContentChanged;
use App\Jobs\GenerateSitemaps;
use Spatie\ResponseCache\Facades\ResponseCache;

class RefreshPublicCaches
{
    public function handle(PublicContentChanged $event): void
    {
        ResponseCache::clear();

        GenerateSitemaps::dispatch()->afterCommit();
    }
}
