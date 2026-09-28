<?php

namespace App\Filament\Resources\Projects\Schemas;

use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use App\Models\Project;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class ProjectForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('المشروع')
                    ->description('مشاريع حقيقية بس، بصور حقيقية وموافقة العميل.')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')->label('العنوان')->required()->maxLength(120),
                        Textarea::make('summary')->label('ملخص')->required()->rows(2)->maxLength(300),
                        RichEditor::make('story')->label('التفاصيل (المشكلة، الحل، النتيجة)'),
                        SpatieMediaLibraryFileUpload::make('photos')
                            ->label('الصور')
                            ->collection('photos')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxSize(4096)
                            ->customProperties(fn (Get $get): array => ['alt' => $get('photos_alt')]),
                        TextInput::make('photos_alt')->label('وصف الصور الجديدة (alt) بالعربي')->dehydrated(false)->maxLength(160),
                    ]),
                Section::make('التفاصيل')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('client_type')->label('نوع العميل')->options(Project::CLIENT_TYPES)->required()->default('home'),
                        Select::make('area_id')->label('المنطقة')->relationship('area', 'name_ar')->searchable()->preload(),
                        Select::make('service_id')->label('الخدمة')->relationship('service', 'name')->searchable()->preload(),
                        DatePicker::make('completed_on')->label('تاريخ التنفيذ'),
                    ]),
                PublishSection::make()->columnSpan(1),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
