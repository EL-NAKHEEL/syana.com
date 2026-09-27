<?php

namespace App\Filament\Resources\Brands\Schemas;

use App\Filament\Components\PublishSection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('الماركة')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name_ar')->label('الاسم بالعربي')->required()->maxLength(60),
                        TextInput::make('name_en')->label('الاسم بالإنجليزي (schema)')->required()->maxLength(60)->extraInputAttributes(['dir' => 'ltr']),
                        Textarea::make('description')->label('وصف')->rows(3),
                        TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
                    ]),
                PublishSection::make()->columnSpan(1),
            ]);
    }
}
