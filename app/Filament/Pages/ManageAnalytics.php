<?php

namespace App\Filament\Pages;

use App\Settings\AnalyticsSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageAnalytics extends SettingsPage
{
    protected static string $settings = AnalyticsSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'التحليلات';

    protected static ?string $title = 'التحليلات';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Google')
                ->description('استخدم واحد بس: GTM أو GA4. لو الاتنين موجودين هيتحمّل GTM.')
                ->columns(2)
                ->schema([
                    TextInput::make('ga4_measurement_id')->label('GA4 Measurement ID')->placeholder('G-XXXXXXX')->regex('/^G-[A-Z0-9]+$/')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('gtm_container_id')->label('GTM Container ID')->placeholder('GTM-XXXXXXX')->regex('/^GTM-[A-Z0-9]+$/')->extraInputAttributes(['dir' => 'ltr']),
                ]),
        ]);
    }
}
