<?php

namespace App\Filament\Resources\FacetPages;

use App\Filament\Resources\FacetPages\Pages\CreateFacetPage;
use App\Filament\Resources\FacetPages\Pages\EditFacetPage;
use App\Filament\Resources\FacetPages\Pages\ListFacetPages;
use App\Filament\Resources\FacetPages\Schemas\FacetPageForm;
use App\Filament\Resources\FacetPages\Tables\FacetPagesTable;
use App\Models\FacetPage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class FacetPageResource extends Resource
{
    protected static ?string $model = FacetPage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFunnel;

    protected static string|\UnitEnum|null $navigationGroup = 'المتجر';

    protected static ?string $modelLabel = 'صفحة ماركة/قدرة';

    protected static ?string $pluralModelLabel = 'صفحات الماركات والقدرات';

    protected static ?string $recordTitleAttribute = 'path';

    public static function form(Schema $schema): Schema
    {
        return FacetPageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FacetPagesTable::configure($table);
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
            'index' => ListFacetPages::route('/'),
            'create' => CreateFacetPage::route('/create'),
            'edit' => EditFacetPage::route('/{record}/edit'),
        ];
    }
}
