<?php

namespace App\Filament\Pages;

use App\Settings\BusinessSettings;
use BackedEnum;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageBusiness extends SettingsPage
{
    protected static string $settings = BusinessSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'بيانات النشاط (NAP)';

    protected static ?string $title = 'بيانات النشاط';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('التواصل')
                ->description('رقم التليفون الوحيد المعروض هو 01055207525 (ثابت في الإعدادات البرمجية).')
                ->columns(2)
                ->schema([
                    TextInput::make('email')->label('البريد الإلكتروني')->email()->extraInputAttributes(['dir' => 'ltr']),
                    Toggle::make('email_confirmed')->label('البريد مؤكَّد ويظهر على الموقع'),
                ]),
            Section::make('مواعيد العمل')
                ->schema([
                    Toggle::make('is_24_7')->label('خدمة 24 ساعة طول الأسبوع (فعّلها بس لو حقيقية)')->live(),
                    Repeater::make('opening_hours')
                        ->label('المواعيد')
                        ->hidden(fn (Get $get) => (bool) $get('is_24_7'))
                        ->columns(3)
                        ->schema([
                            CheckboxList::make('days')->label('الأيام')->columns(4)->columnSpanFull()->options([
                                'Saturday' => 'السبت', 'Sunday' => 'الأحد', 'Monday' => 'الاثنين', 'Tuesday' => 'الثلاثاء',
                                'Wednesday' => 'الأربعاء', 'Thursday' => 'الخميس', 'Friday' => 'الجمعة',
                            ])->required(),
                            TimePicker::make('opens')->label('من')->seconds(false)->required(),
                            TimePicker::make('closes')->label('إلى')->seconds(false)->required(),
                        ])
                        ->addActionLabel('أضف فترة'),
                ]),
            Section::make('العنوان')
                ->description('نشاط خدمي بدون عنوان عام حتى تأكيد وجود مكان يستقبل العملاء.')
                ->columns(2)
                ->schema([
                    Toggle::make('has_public_address')->label('عندنا مكان يستقبل العملاء')->live()->columnSpanFull(),
                    TextInput::make('street_address')->label('الشارع')->visible(fn (Get $get) => $get('has_public_address')),
                    TextInput::make('locality')->label('المنطقة/المدينة')->visible(fn (Get $get) => $get('has_public_address')),
                    TextInput::make('region')->label('المحافظة')->visible(fn (Get $get) => $get('has_public_address')),
                    TextInput::make('postal_code')->label('الرمز البريدي')->visible(fn (Get $get) => $get('has_public_address')),
                    TextInput::make('latitude')->label('خط العرض')->numeric()->visible(fn (Get $get) => $get('has_public_address')),
                    TextInput::make('longitude')->label('خط الطول')->numeric()->visible(fn (Get $get) => $get('has_public_address')),
                ]),
            Section::make('جوجل والسوشيال')
                ->columns(2)
                ->schema([
                    TextInput::make('gbp_url')->label('رابط Google Business Profile')->url()->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('gbp_review_url')->label('رابط «قيّمنا على جوجل»')->url()->extraInputAttributes(['dir' => 'ltr']),
                    TagsInput::make('same_as')->label('حسابات السوشيال الرسمية (روابط)')->columnSpanFull(),
                ]),
            Section::make('معلومات إضافية (اتركها فاضية لو مش مؤكدة)')
                ->columns(2)
                ->schema([
                    TextInput::make('founding_year')->label('سنة التأسيس')->numeric()->minValue(1950)->maxValue((int) date('Y')),
                    TextInput::make('price_range')->label('نطاق الأسعار (schema priceRange)')->maxLength(20),
                ]),
        ]);
    }
}
