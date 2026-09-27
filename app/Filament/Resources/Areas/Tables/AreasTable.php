<?php

namespace App\Filament\Resources\Areas\Tables;

use App\Models\Area;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort')
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount(['services', 'faqs', 'neighbors']))
            ->columns([
                TextColumn::make('name_ar')->label('المنطقة')->searchable(),
                TextColumn::make('slug')->label('الرابط'),
                IconColumn::make('is_published')->label('منشورة')->boolean(),
                IconColumn::make('guard_ok')->label('شروط النشر')->boolean()->state(fn (Area $record) => $record->guardFailures() === []),
                IconColumn::make('show_in_footer')->label('في الفوتر')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }
}
