<?php

namespace App\Console\Commands;

use App\Events\PublicContentChanged;
use App\Models\Area;
use App\Models\Brand;
use App\Models\FacetPage;
use App\Models\Page;
use App\Models\Person;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\PriceGuide;
use App\Models\Product;
use App\Models\Project;
use App\Models\Service;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * Time-based changes do not fire model events: a post scheduled for later, a sale that ends. When one of those
 * moments passed since the last run, refresh the page cache and sitemaps (scheduled every five minutes).
 */
#[Signature('app:refresh-scheduled-content')]
#[Description('Refresh caches when scheduled publishing or a sale end time has passed')]
class RefreshScheduledContentCommand extends Command
{
    private const LAST_RUN = 'scheduled-content.checked-at';

    /** Models whose published_at may be set in the future. */
    private const SCHEDULED = [Page::class, Service::class, Area::class, PriceGuide::class, Brand::class, Product::class,
        FacetPage::class, Person::class, PostCategory::class, Post::class, Project::class];

    public function handle(): int
    {
        $now = now();
        $since = Cache::get(self::LAST_RUN);
        Cache::forever(self::LAST_RUN, $now->toIso8601String());

        if (! is_string($since)) {
            return self::SUCCESS; // first run: nothing to compare against
        }

        $since = Carbon::parse($since);
        $due = Product::query()->whereBetween('sale_ends_at', [$since, $now])->exists();

        foreach (self::SCHEDULED as $model) {
            $due = $due || $model::query()->where('is_published', true)->whereBetween('published_at', [$since, $now])->exists();
        }

        if ($due) {
            PublicContentChanged::dispatch();
            $this->info('Scheduled content changed: caches refreshed.');
        }

        return self::SUCCESS;
    }
}
