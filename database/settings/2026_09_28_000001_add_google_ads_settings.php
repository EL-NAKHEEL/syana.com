<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Carried over from the existing site's gtag snippet (legacy/old-site/index.html) so the running
        // Google Ads call-conversion tracking keeps working after the switch.
        $this->migrator->add('analytics.google_ads_id', 'AW-11415013969');
        $this->migrator->add('analytics.google_ads_call_label', '7C79CO6q5sscENGUjcMq');
    }
};
