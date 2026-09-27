<?php

namespace App\Filament\Resources\Redirects\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class RedirectsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('hits', 'desc')
            ->columns([
                TextColumn::make('from_path')->label('من')->searchable()->limit(50),
                TextColumn::make('to_url')->label('إلى')->searchable()->limit(50)->placeholder('410'),
                TextColumn::make('status_code')->label('النوع')->badge(),
                TextColumn::make('hits')->label('الزيارات')->numeric()->sortable(),
                TextColumn::make('last_hit_at')->label('آخر زيارة')->since()->sortable(),
                TextColumn::make('source')->label('المصدر')->badge(),
                IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->filters([
                SelectFilter::make('source')->label('المصدر')->options(['manual' => 'يدوي', 'legacy' => 'الموقع القديم', 'slug' => 'تغيير رابط', '404-suggestion' => 'من سجل 404']),
                TernaryFilter::make('is_active')->label('مفعّل'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
