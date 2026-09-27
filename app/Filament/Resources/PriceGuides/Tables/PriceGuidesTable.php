<?php

namespace App\Filament\Resources\PriceGuides\Tables;

use App\Models\PriceGuide;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PriceGuidesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->modifyQueryUsing(fn (Builder $query) => $query->with('services'))
            ->columns([
                TextColumn::make('h1')->label('الدليل')->searchable(),
                TextColumn::make('slug')->label('الرابط'),
                IconColumn::make('is_published')->label('منشور')->boolean(),
                IconColumn::make('has_prices')->label('فيه أسعار')->boolean()->state(fn (PriceGuide $g) => $g->rows()->contains(fn ($s) => $s->starting_price !== null)),
                TextColumn::make('last_price')->label('آخر تغيير سعر')->state(fn (PriceGuide $g) => $g->lastPriceChange()?->diffForHumans() ?? '—'),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
