<?php

namespace App\Filament\Pages;

use App\Settings\SeoSettings;
use BackedEnum;
use Filament\Forms\Components\TextInput;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageSeo extends SettingsPage
{
    protected static string $settings = SeoSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'إعدادات SEO';

    protected static ?string $title = 'إعدادات SEO';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('التحقق من الملكية')
                ->columns(2)
                ->schema([
                    TextInput::make('google_site_verification')->label('Google Search Console (content)')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('bing_site_verification')->label('Bing Webmaster (msvalidate.01)')->extraInputAttributes(['dir' => 'ltr']),
                ]),
            Section::make('الفهرسة')
                ->columns(2)
                ->schema([
                    TextInput::make('indexnow_key')->label('مفتاح IndexNow')->required()->regex('/^[a-z0-9-]{8,128}$/')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('default_og_image')->label('صورة OG الاحتياطية (مسار على القرص العام)')->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('price_freshness_days')->label('السنة تظهر في العناوين لو الأسعار اتحدثت خلال (يوم)')->numeric()->minValue(7)->maxValue(365)->required(),
                    TextInput::make('area_min_reviews')->label('أقل عدد تقييمات محلية لنشر صفحة منطقة')->numeric()->minValue(0)->maxValue(20)->required(),
                ]),
        ]);
    }
}
