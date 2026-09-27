<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('analytics.ga4_measurement_id', null);
        $this->migrator->add('analytics.gtm_container_id', null);
    }
};
