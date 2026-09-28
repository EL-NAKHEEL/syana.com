<?php

namespace App\Providers;

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
use App\Seo\Seo;
use App\Support\Navigation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Spatie\LaravelSettings\Events\SettingsSaved;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // One SEO state per request (Octane-safe).
        $this->app->scoped(Seo::class);
        $this->app->scoped(Navigation::class);
    }

    public function boot(): void
    {
        // No N+1 queries: lazy loading throws outside production.
        Model::preventLazyLoading(! $this->app->isProduction());

        // Short, stable morph types for content (seo_meta, faqs, slug history, reviews).
        Relation::morphMap([
            'page' => Page::class,
            'service' => Service::class,
            'area' => Area::class,
            'price_guide' => PriceGuide::class,
            'brand' => Brand::class,
            'product' => Product::class,
            'facet_page' => FacetPage::class,
            'person' => Person::class,
            'post_category' => PostCategory::class,
            'post' => Post::class,
            'project' => Project::class,
        ]);

        // NAP/analytics/verification settings render on every public page.
        Event::listen(SettingsSaved::class, fn () => PublicContentChanged::dispatch());
    }
}
