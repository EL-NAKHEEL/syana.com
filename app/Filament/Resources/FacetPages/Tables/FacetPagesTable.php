<?php

namespace App\Filament\Resources\FacetPages\Tables;

use App\Models\FacetPage;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FacetPagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('path')->label('المسار')->searchable(),
                TextColumn::make('kind')->label('النوع')->formatStateUsing(fn (string $state) => FacetPage::KINDS[$state] ?? $state),
                TextColumn::make('h1')->label('العنوان')->placeholder('تلقائي'),
                IconColumn::make('is_published')->label('منشورة')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }
}
