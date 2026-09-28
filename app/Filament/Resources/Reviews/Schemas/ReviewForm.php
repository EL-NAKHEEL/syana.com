<?php

namespace App\Filament\Resources\Reviews\Schemas;

use App\Models\Review;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ReviewForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('التقييم')
                    ->description('ممكن تصحح الإملاء بس، من غير ما تغير معنى رأي العميل.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')->label('الاسم')->required()->maxLength(80),
                        Select::make('rating')->label('التقييم')->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1'])->required(),
                        Textarea::make('body')->label('الرأي')->required()->rows(4)->columnSpanFull(),
                        Select::make('area_id')->label('المنطقة')->relationship('area', 'name_ar'),
                        Select::make('status')->label('الحالة')->options(Review::STATUSES)->required(),
                        TextInput::make('temp_before')->label('الحرارة قبل')->numeric(),
                        TextInput::make('temp_after')->label('الحرارة بعد')->numeric(),
                        TextInput::make('video_url')->label('فيديو')->url()->extraInputAttributes(['dir' => 'ltr'])->columnSpanFull(),
                    ]),
            ]);
    }
}
