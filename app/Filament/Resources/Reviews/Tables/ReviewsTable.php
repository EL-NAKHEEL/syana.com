<?php

namespace App\Filament\Resources\Reviews\Tables;

use App\Models\Review;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReviewsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')->label('وصل')->since()->sortable(),
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('rating')->label('التقييم')->formatStateUsing(fn (int $state) => $state.'/5'),
                TextColumn::make('body')->label('الرأي')->limit(80)->wrap(),
                TextColumn::make('reviewable_type')->label('عن')->formatStateUsing(fn (?string $state) => ['product' => 'منتج', 'service' => 'خدمة'][$state] ?? 'عام')->placeholder('عام'),
                TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn (string $state) => Review::STATUSES[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        'approved' => 'success', 'rejected' => 'danger', default => 'warning'
                    }),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(Review::STATUSES)->default('pending'),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('نشر')
                    ->icon('heroicon-o-check')
                    ->color('success')
                    ->visible(fn (Review $record) => $record->status !== 'approved')
                    ->action(fn (Review $record) => $record->update(['status' => 'approved'])),
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-mark')
                    ->color('danger')
                    ->visible(fn (Review $record) => $record->status !== 'rejected')
                    ->action(fn (Review $record) => $record->update(['status' => 'rejected'])),
                EditAction::make(),
            ]);
    }
}
