<?php

namespace App\Filament\Resources\PriceGuides\Schemas;

use App\Filament\Components\FaqRepeater;
use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use App\Models\PriceGuide;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PriceGuideForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Callout::make('الأسعار بتتسحب تلقائيًا')
                    ->description('الدليل ده مش بيخزّن أسعار: الجدول بيتبني من «يبدأ من» في كل خدمة مختارة. ومش بيتنشر غير لو فيه خدمة واحدة على الأقل ليها سعر. {year} بيظهر في العنوان بس لو الأسعار اتحدثت قريب.')
                    ->columnSpanFull(),

                Section::make('المحتوى')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')->label('عنوان الصفحة (title) — ممكن فيه {year}')->required()->maxLength(80),
                        TextInput::make('h1')->label('العنوان الرئيسي (H1) — ممكن فيه {year}')->required()->maxLength(120),
                        Textarea::make('intro')->label('مقدمة')->rows(3),
                        Select::make('services')->label('الخدمات في الجدول')->relationship('services', 'name')->multiple()->preload(),
                        RichEditor::make('body')->label('المحتوى'),
                    ]),

                Section::make('الإعدادات')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('scope')->label('النوع')->options(PriceGuide::SCOPES)->default('services')->required(),
                        TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
                    ]),

                PublishSection::make()->columnSpan(1),
                FaqRepeater::make()->columnSpan(2),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
