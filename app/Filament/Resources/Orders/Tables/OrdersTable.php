<?php

namespace App\Filament\Resources\Orders\Tables;

use App\Models\Order;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('number')->label('رقم الطلب')->searchable(),
                TextColumn::make('created_at')->label('وصل')->since()->sortable(),
                TextColumn::make('name')->label('الاسم')->searchable(),
                TextColumn::make('phone')->label('الموبايل')->searchable()->copyable(),
                TextColumn::make('total')->label('الإجمالي')->numeric(),
                TextColumn::make('status')->label('الحالة')->badge()->formatStateUsing(fn (string $state) => Order::STATUSES[$state] ?? $state),
            ])
            ->filters([SelectFilter::make('status')->label('الحالة')->options(Order::STATUSES)])
            ->recordActions([
                Action::make('whatsapp')->label('واتساب')->icon('heroicon-o-chat-bubble-left-right')
                    ->url(fn (Order $order) => 'https://wa.me/2'.$order->phone, shouldOpenInNewTab: true),
                EditAction::make(),
            ]);
    }
}
