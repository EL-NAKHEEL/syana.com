<?php

namespace App\Filament\Resources\Brands\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name_ar')->label('الماركة')->searchable(),
                TextColumn::make('slug')->label('الرابط'),
                TextColumn::make('products_count')->label('المنتجات')->counts('products'),
                IconColumn::make('is_published')->label('منشورة')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
