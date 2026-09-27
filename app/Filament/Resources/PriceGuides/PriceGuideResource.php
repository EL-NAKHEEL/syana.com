<?php

namespace App\Filament\Resources\PriceGuides;

use App\Filament\Resources\PriceGuides\Pages\CreatePriceGuide;
use App\Filament\Resources\PriceGuides\Pages\EditPriceGuide;
use App\Filament\Resources\PriceGuides\Pages\ListPriceGuides;
use App\Filament\Resources\PriceGuides\Schemas\PriceGuideForm;
use App\Filament\Resources\PriceGuides\Tables\PriceGuidesTable;
use App\Models\PriceGuide;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PriceGuideResource extends Resource
{
    protected static ?string $model = PriceGuide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static string|\UnitEnum|null $navigationGroup = 'المحتوى';

    protected static ?string $modelLabel = 'دليل أسعار';

    protected static ?string $pluralModelLabel = 'أدلة الأسعار';

    protected static ?string $recordTitleAttribute = 'h1';

    public static function form(Schema $schema): Schema
    {
        return PriceGuideForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PriceGuidesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPriceGuides::route('/'),
            'create' => CreatePriceGuide::route('/create'),
            'edit' => EditPriceGuide::route('/{record}/edit'),
        ];
    }
}
