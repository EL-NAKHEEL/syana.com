<?php

namespace App\Filament\Resources\Products\Tables;

use App\Catalog\Catalog;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('brand'))
            ->columns([
                TextColumn::make('brand.name_ar')->label('الماركة')->sortable(),
                TextColumn::make('model_number')->label('الموديل')->searchable(),
                TextColumn::make('hp')->label('حصان')->formatStateUsing(fn ($state) => Catalog::hpLabel($state))->sortable(),
                TextColumn::make('price')->label('السعر')->numeric()->sortable(),
                TextColumn::make('sale_price')->label('العرض')->numeric()->placeholder('—'),
                TextColumn::make('stock_status')->label('المخزون')->badge()->formatStateUsing(fn (string $state) => Catalog::STOCK[$state] ?? $state),
                IconColumn::make('is_published')->label('منشور')->boolean(),
                TextColumn::make('price_changed_at')->label('آخر تغيير سعر')->since(),
            ])
            ->filters([
                SelectFilter::make('brand')->label('الماركة')->relationship('brand', 'name_ar'),
                SelectFilter::make('stock_status')->label('المخزون')->options(Catalog::STOCK),
            ])
            ->recordActions([EditAction::make()]);
    }
}
