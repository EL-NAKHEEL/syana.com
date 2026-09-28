<?php

namespace App\Filament\Resources\Projects\Tables;

use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ProjectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('content_modified_at', 'desc')
            ->columns([
                TextColumn::make('title')->label('المشروع')->searchable()->limit(60),
                TextColumn::make('area.name_ar')->label('المنطقة')->placeholder('—'),
                IconColumn::make('is_published')->label('منشور')->boolean(),
                TextColumn::make('completed_on')->label('التنفيذ')->date()->sortable()->placeholder('—'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
