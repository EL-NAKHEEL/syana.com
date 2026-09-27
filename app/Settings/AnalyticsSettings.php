<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class AnalyticsSettings extends Settings
{
    public ?string $ga4_measurement_id;

    public ?string $gtm_container_id;

    /** Google Ads account (AW-…) already used by the existing site for call conversions. */
    public ?string $google_ads_id;

    /** Conversion label fired on every phone-call click (value 1 EGP, as on the existing site). */
    public ?string $google_ads_call_label;

    public static function group(): string
    {
        return 'analytics';
    }
}
