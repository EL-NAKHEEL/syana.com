<?php

namespace App\Providers;

use App\Events\PublicContentChanged;
use App\Seo\Seo;
use App\Support\Navigation;
use Illuminate\Database\Eloquent\Model;
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

        // NAP/analytics/verification settings render on every public page.
        Event::listen(SettingsSaved::class, fn () => PublicContentChanged::dispatch());
    }
}
