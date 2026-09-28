<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('المقال')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')->label('العنوان (H1)')->required()->maxLength(120),
                        Textarea::make('excerpt')->label('ملخص (للكروت والوصف)')->required()->rows(2)->maxLength(300),
                        RichEditor::make('body')
                            ->label('المحتوى')
                            ->required()
                            ->helperText('العناوين الفرعية H2/H3 بتعمل فهرس تلقائي. لإدراج منتج اكتب [product:slug] في سطر لوحده.'),
                    ]),
                Section::make('التفاصيل')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('author_id')->label('الكاتب')->relationship('author', 'name')->required()->searchable()->preload(),
                        Select::make('reviewed_by_id')->label('راجعه (اختياري)')->relationship('reviewer', 'name')->searchable()->preload(),
                        Select::make('post_category_id')->label('التصنيف')->relationship('category', 'name')->searchable()->preload(),
                        Select::make('service_id')->label('الخدمة المرتبطة (رابط الصفحة التجارية)')->relationship('service', 'name')->searchable()->preload(),
                        SpatieMediaLibraryFileUpload::make('featured')
                            ->label('الصورة الرئيسية')
                            ->collection('featured')
                            ->image()
                            ->maxSize(4096)
                            ->customProperties(fn (Get $get): array => ['alt' => $get('featured_alt')])
                            ->helperText('اسم الملف بالإنجليزي وبيوصف الصورة.'),
                        TextInput::make('featured_alt')->label('وصف الصورة (alt) بالعربي')->dehydrated(false)->maxLength(160),
                    ]),
                PublishSection::make()->columnSpan(1),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
