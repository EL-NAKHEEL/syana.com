<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class SeoSettings extends Settings
{
    public ?string $google_site_verification;

    public ?string $bing_site_verification;

    public ?string $indexnow_key;

    /** Path on the public disk of the default OG image (auto-generated when empty). */
    public ?string $default_og_image;

    /** {year} renders in titles only while in-scope prices changed within this many days. */
    public int $price_freshness_days;

    /** Minimum approved local reviews before an area can be published (owner decision: 0). */
    public int $area_min_reviews;

    public static function group(): string
    {
        return 'seo';
    }
}
