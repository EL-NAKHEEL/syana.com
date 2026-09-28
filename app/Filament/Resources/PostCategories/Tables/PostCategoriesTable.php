<?php

namespace App\Filament\Resources\PostCategories\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->columns([
                TextColumn::make('name')->label('التصنيف')->searchable(),
                TextColumn::make('slug')->label('الرابط'),
                IconColumn::make('is_published')->label('منشور')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
