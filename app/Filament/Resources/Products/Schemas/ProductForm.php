<?php

namespace App\Filament\Resources\Products\Schemas;

use App\Catalog\Catalog;
use App\Filament\Components\FaqRepeater;
use App\Filament\Components\SeoSection;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ProductForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('المنتج')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        Select::make('brand_id')->label('الماركة')->relationship('brand', 'name_ar')->required()->preload(),
                        TextInput::make('model_number')->label('رقم الموديل')->required()->maxLength(60)->extraInputAttributes(['dir' => 'ltr']),
                        TextInput::make('name')->label('اسم داخلي')->required()->maxLength(160)->helperText('الاسم المعروض بيتبني تلقائيًا من الماركة والقدرة والتبريد والموديل.'),
                        TextInput::make('sku')->label('SKU')->maxLength(60)->extraInputAttributes(['dir' => 'ltr']),
                        Select::make('type')->label('النوع')->options(Catalog::TYPES)->required(),
                        Select::make('hp')->label('القدرة (حصان)')->options(collect(Catalog::CAPACITIES)->mapWithKeys(fn ($hp) => [number_format($hp, 2, '.', '') => Catalog::hpLabel($hp)])->all())->required(),
                        TextInput::make('btu')->label('BTU')->numeric(),
                        Select::make('cooling')->label('التبريد')->options(Catalog::COOLING)->required()->default('cool'),
                        Toggle::make('is_inverter')->label('إنفرتر'),
                        TextInput::make('energy_class')->label('كفاءة الطاقة')->maxLength(20),
                        TextInput::make('room_area_min')->label('مناسب لمساحة من (م²)')->numeric(),
                        TextInput::make('room_area_max')->label('إلى (م²)')->numeric(),
                        Textarea::make('short_description')->label('وصف مختصر')->rows(2)->maxLength(300)->columnSpanFull(),
                        RichEditor::make('description')->label('الوصف')->columnSpanFull(),
                    ]),

                Section::make('السعر والمخزون')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('price')->label('السعر (جنيه)')->numeric()->required()->minValue(0),
                        TextInput::make('sale_price')->label('سعر العرض')->numeric()->minValue(0)->lt('price'),
                        DateTimePicker::make('sale_ends_at')->label('العرض ينتهي'),
                        Select::make('stock_status')->label('المخزون')->options(Catalog::STOCK)->required()->default('in_stock')
                            ->helperText('«توقف إنتاجه» بيحوّل صفحة المنتج 301 لأقرب صفحة ماركة/قدرة.'),
                        Select::make('installation_included')->label('التركيب')->options([1 => 'السعر شامل التركيب', 0 => 'التركيب بيتحسب لوحده'])->placeholder('مش محدد (مش هيظهر)'),
                        TextInput::make('warranty_months')->label('الضمان (شهور)')->numeric(),
                        TextInput::make('warranty_note')->label('ملاحظة الضمان')->maxLength(160),
                        TextInput::make('installments_note')->label('التقسيط')->maxLength(160),
                    ]),

                Section::make('الصور')
                    ->columnSpan(2)
                    ->schema([
                        SpatieMediaLibraryFileUpload::make('images')
                            ->label('صور المنتج (الأولى هي الأساسية)')
                            ->collection('images')
                            ->multiple()
                            ->reorderable()
                            ->image()
                            ->maxSize(4096)
                            ->helperText('اسم الملف بالإنجليزي وبيوصف الصورة (مثال: sharp-ay-x12-front.jpg).'),
                    ]),

                Section::make('النشر')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('slug')->label('الرابط (slug)')->required()->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                            ->notIn(['brand', 'capacity', 'type'])->unique(ignoreRecord: true)->extraInputAttributes(['dir' => 'ltr']),
                        Toggle::make('is_published')->label('منشور'),
                        DateTimePicker::make('published_at')->label('تاريخ النشر'),
                        Select::make('related')->label('بدائل مختارة')->relationship('related', 'name')->multiple()->preload(),
                    ]),

                Section::make('مواصفات إضافية')
                    ->columnSpan(2)
                    ->collapsible()
                    ->schema([
                        Repeater::make('specs')->hiddenLabel()->schema([
                            TextInput::make('label')->label('المواصفة')->required()->maxLength(80),
                            TextInput::make('value')->label('القيمة')->required()->maxLength(160),
                        ])->columns(2)->addActionLabel('أضف مواصفة'),
                    ]),

                FaqRepeater::make()->columnSpan(2),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
