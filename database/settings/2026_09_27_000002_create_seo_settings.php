<?php

use Illuminate\Support\Str;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('seo.google_site_verification', null);
        $this->migrator->add('seo.bing_site_verification', null);
        $this->migrator->add('seo.indexnow_key', Str::lower(Str::random(32)));
        $this->migrator->add('seo.default_og_image', null);
        $this->migrator->add('seo.price_freshness_days', 90);
        $this->migrator->add('seo.area_min_reviews', 0);
    }
};
