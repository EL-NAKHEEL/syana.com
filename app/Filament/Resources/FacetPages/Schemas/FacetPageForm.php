<?php

namespace App\Filament\Resources\FacetPages\Schemas;

use App\Catalog\Catalog;
use App\Filament\Components\FaqRepeater;
use App\Filament\Components\SeoSection;
use App\Models\FacetPage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class FacetPageForm
{
    public static function configure(Schema $schema): Schema
    {
        $needsBrand = fn (Get $get) => in_array($get('kind'), ['brand', 'brand_capacity'], true);
        $needsHp = fn (Get $get) => in_array($get('kind'), ['capacity', 'brand_capacity'], true);

        return $schema
            ->columns(3)
            ->components([
                Callout::make('صفحة ماركة / قدرة / نوع')
                    ->description('الصفحة بتظهر لجوجل بس لما تتنشر وفيها مقدمة فريدة و3 منتجات منشورة على الأقل. غير كده بتفضل noindex.')
                    ->columnSpanFull(),
                Section::make('الصفحة')
                    ->columnSpan(2)
                    ->schema([
                        Select::make('kind')->label('النوع')->options(FacetPage::KINDS)->required()->live(),
                        Select::make('brand_id')->label('الماركة')->relationship('brand', 'name_ar')->visible($needsBrand)->required($needsBrand),
                        Select::make('hp')->label('القدرة')->options(collect(Catalog::CAPACITIES)->mapWithKeys(fn ($hp) => [number_format($hp, 2, '.', '') => Catalog::hpLabel($hp).' حصان'])->all())->visible($needsHp)->required($needsHp),
                        Select::make('type')->label('نوع التكييف')->options(Catalog::TYPES)->visible(fn (Get $get) => $get('kind') === 'type')->required(fn (Get $get) => $get('kind') === 'type'),
                        TextInput::make('h1')->label('العنوان الرئيسي (H1)')->maxLength(120)->helperText('فاضي = «تكييف {الماركة/القدرة}» تلقائي.'),
                        Textarea::make('intro')->label('مقدمة فريدة (مطلوبة للفهرسة)')->rows(4),
                        RichEditor::make('body')->label('محتوى تحت المنتجات'),
                    ]),
                Section::make('النشر')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('path')->label('المسار')->disabled()->dehydrated(false)->extraInputAttributes(['dir' => 'ltr']),
                        Toggle::make('is_published')->label('منشورة'),
                        DateTimePicker::make('published_at')->label('تاريخ النشر'),
                    ]),
                FaqRepeater::make()->columnSpan(2),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
