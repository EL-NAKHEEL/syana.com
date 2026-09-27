<?php

namespace App\Filament\Resources\BookingRequests\Tables;

use App\Models\BookingRequest;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class BookingRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['service', 'area']))
            ->columns([
                TextColumn::make('created_at')->label('وصل')->since()->sortable(),
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('phone')->label('الموبايل')->searchable()->copyable(),
                TextColumn::make('service.name')->label('الخدمة')->placeholder('—'),
                TextColumn::make('area_label')->label('المنطقة')->state(fn (BookingRequest $r) => $r->area->name_ar ?? $r->area_text ?? '—'),
                TextColumn::make('preferred_date')->label('الميعاد')->date()->placeholder('—'),
                TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn (string $state) => BookingRequest::STATUSES[$state] ?? $state),
            ])
            ->filters([
                SelectFilter::make('status')->label('الحالة')->options(BookingRequest::STATUSES),
            ])
            ->recordActions([
                Action::make('whatsapp')
                    ->label('واتساب')
                    ->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (BookingRequest $r) => 'https://wa.me/2'.$r->phone, shouldOpenInNewTab: true),
                EditAction::make(),
            ]);
    }
}
