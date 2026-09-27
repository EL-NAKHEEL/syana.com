<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Draft value from the old site; hidden until the owner confirms it (PLAN.md §0, Q4).
        $this->migrator->add('business.email', 'elnakheel55@gmail.com');
        $this->migrator->add('business.email_confirmed', false);
        $this->migrator->add('business.is_24_7', false);
        $this->migrator->add('business.opening_hours', []);
        $this->migrator->add('business.has_public_address', false);
        $this->migrator->add('business.street_address', null);
        $this->migrator->add('business.locality', null);
        $this->migrator->add('business.region', null);
        $this->migrator->add('business.postal_code', null);
        $this->migrator->add('business.latitude', null);
        $this->migrator->add('business.longitude', null);
        $this->migrator->add('business.gbp_url', null);
        $this->migrator->add('business.gbp_review_url', null);
        $this->migrator->add('business.same_as', []);
        $this->migrator->add('business.founding_year', null);
        $this->migrator->add('business.price_range', null);
    }
};
