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
            Section::make('Google Ads')
                ->description('نفس حساب الإعلانات اللي على الموقع القديم: كل ضغطة على «اتصل» بتتسجل conversion.')
                ->columns(2)
                ->schema([
                    TextInput::make('google_ads_id')->label('Google Ads ID')->placeholder('AW-XXXXXXXXXX')->regex('/^AW-[0-9]+$/')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('google_ads_call_label')->label('Conversion label للمكالمات')->regex('/^[A-Za-z0-9_-]+$/')->extraInputAttributes(['dir' => 'ltr']),
                ]),
            Section::make('Google Analytics / Tag Manager')
                ->description('لو بتستخدم GTM سيب GA4 فاضي وضيف التاجات من جوه GTM.')
                ->columns(2)
                ->schema([
                    TextInput::make('ga4_measurement_id')->label('GA4 Measurement ID')->placeholder('G-XXXXXXX')->regex('/^G-[A-Z0-9]+$/')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('gtm_container_id')->label('GTM Container ID')->placeholder('GTM-XXXXXXX')->regex('/^GTM-[A-Z0-9]+$/')->extraInputAttributes(['dir' => 'ltr']),
                ]),
        ]);
    }
}
