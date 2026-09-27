<?php

namespace App\Filament\Resources\Services\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ServicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->reorderable('sort')
            ->columns([
                TextColumn::make('name')->label('الخدمة')->searchable(),
                TextColumn::make('slug')->label('الرابط'),
                TextColumn::make('starting_price')->label('يبدأ من')->placeholder('بعد المعاينة'),
                IconColumn::make('is_published')->label('منشورة')->boolean(),
                IconColumn::make('requires_24_7')->label('24/7')->boolean(),
                TextColumn::make('content_modified_at')->label('آخر تعديل')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
