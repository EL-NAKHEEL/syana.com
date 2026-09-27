<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Models\Page;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable(),
                TextColumn::make('slug')->label('المعرّف'),
                TextColumn::make('template')->label('القالب')->formatStateUsing(fn (string $state) => Page::TEMPLATES[$state] ?? $state),
                IconColumn::make('is_published')->label('منشورة')->boolean(),
                TextColumn::make('content_modified_at')->label('آخر تعديل للمحتوى')->since(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
