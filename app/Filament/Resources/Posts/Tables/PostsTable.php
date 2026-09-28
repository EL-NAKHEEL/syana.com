<?php

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('content_modified_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('العنوان')->searchable()->limit(60),
                TextColumn::make('author.name')->label('الكاتب'),
                TextColumn::make('category.name')->label('التصنيف')->placeholder('—'),
                IconColumn::make('is_published')->label('منشور')->boolean(),
                TextColumn::make('published_at')->label('النشر')->date()->sortable()->placeholder('—'),
                TextColumn::make('content_modified_at')->label('آخر تعديل')->since()->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
