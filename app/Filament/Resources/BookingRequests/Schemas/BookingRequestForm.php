<?php

namespace App\Filament\Resources\BookingRequests\Schemas;

use App\Models\BookingRequest;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الطلب')
                ->columns(2)
                ->schema([
                    TextInput::make('name')->label('الاسم')->disabled(),
                    TextInput::make('phone')->label('الموبايل')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                    Select::make('service_id')->label('الخدمة')->relationship('service', 'name')->disabled(),
                    Select::make('ac_type')->label('نوع التكييف')->options(BookingRequest::AC_TYPES)->disabled(),
                    Select::make('area_id')->label('المنطقة')->relationship('area', 'name_ar')->disabled(),
                    TextInput::make('area_text')->label('المنطقة (نص)')->disabled(),
                    DatePicker::make('preferred_date')->label('الميعاد المفضل')->disabled(),
                    TextInput::make('source_url')->label('من صفحة')->disabled()->extraInputAttributes(['dir' => 'ltr']),
                    Textarea::make('notes')->label('ملاحظات العميل')->disabled()->columnSpanFull(),
                ]),
            Section::make('المتابعة')
                ->schema([
                    Select::make('status')->label('الحالة')->options(BookingRequest::STATUSES)->required(),
                ]),
        ]);
    }
}
