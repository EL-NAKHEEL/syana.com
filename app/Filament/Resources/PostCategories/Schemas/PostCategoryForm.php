<?php

namespace App\Filament\Resources\PostCategories\Schemas;

use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PostCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('التصنيف')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')->label('الاسم')->required()->maxLength(80),
                        Textarea::make('intro')->label('مقدمة (فريدة لكل تصنيف)')->rows(3),
                        TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
                    ]),
                PublishSection::make()->columnSpan(1),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
