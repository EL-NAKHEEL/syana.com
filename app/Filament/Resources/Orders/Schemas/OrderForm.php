<?php

namespace App\Filament\Resources\Orders\Schemas;

use App\Models\Order;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('العميل')
                ->columns(2)
                ->schema([
                    TextInput::make('number')->label('رقم الطلب')->disabled(),
                    TextInput::make('name')->label('الاسم')->disabled(),
                    TextInput::make('phone')->label('الموبايل')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('area_text')->label('المنطقة')->disabled(),
                    Textarea::make('address')->label('العنوان')->disabled()->columnSpanFull(),
                    Textarea::make('notes')->label('ملاحظات')->disabled()->columnSpanFull(),
                ]),
            Section::make('المنتجات')
                ->schema([
                    Repeater::make('items')->relationship()->disabled()->deletable(false)->addable(false)->columns(4)->schema([
                        TextInput::make('name')->label('المنتج'),
                        TextInput::make('model_number')->label('الموديل'),
                        TextInput::make('qty')->label('الكمية'),
                        TextInput::make('line_total')->label('الإجمالي'),
                    ]),
                    TextInput::make('total')->label('إجمالي الطلب (جنيه)')->disabled(),
                ]),
            Section::make('المتابعة')
                ->columns(2)
                ->schema([
                    Select::make('status')->label('حالة الطلب')->options(Order::STATUSES)->required(),
                    Select::make('payment_status')->label('الدفع')->options(Order::PAYMENT_STATUSES)->required(),
                ]),
        ]);
    }
}
