<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // [TODO confirm] with the technicians before switching `confirmed` on.
        $this->migrator->add('calculator.confirmed', false);
        $this->migrator->add('calculator.btu_per_m2', 700);
        $this->migrator->add('calculator.sunny_factor', 1.15);
        $this->migrator->add('calculator.top_floor_factor', 1.1);
        $this->migrator->add('calculator.hp_btu', ['1.5' => 12000, '2.25' => 18000, '3' => 24000, '4' => 30000, '5' => 36000]);
    }
};
