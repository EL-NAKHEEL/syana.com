<?php

namespace App\Filament\Pages;

use App\Settings\CalculatorSettings;
use BackedEnum;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageCalculator extends SettingsPage
{
    protected static string $settings = CalculatorSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalculator;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?string $navigationLabel = 'حاسبة الحصان';

    protected static ?string $title = 'حاسبة «تكييف كام حصان لأوضتك؟»';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('المعاملات')
                ->description('القيم الحالية قواعد تقريبية عامة ولسه ما اتأكدتش من الفنيين. الحاسبة مش هتظهر على الموقع غير لما تفعّل «مؤكَّدة».')
                ->columns(3)
                ->schema([
                    Toggle::make('confirmed')->label('المعاملات مؤكَّدة (أظهر الحاسبة)')->columnSpanFull(),
                    TextInput::make('btu_per_m2')->label('BTU لكل متر مربع')->numeric()->required(),
                    TextInput::make('sunny_factor')->label('معامل الشمس')->numeric()->required(),
                    TextInput::make('top_floor_factor')->label('معامل آخر دور')->numeric()->required(),
                    KeyValue::make('hp_btu')->label('الحصان → BTU')->keyLabel('حصان')->valueLabel('BTU')->columnSpanFull(),
                ]),
        ]);
    }
}
