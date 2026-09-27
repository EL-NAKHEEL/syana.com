<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Components\FaqRepeater;
use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use App\Support\Placeholders;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('المحتوى')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')->label('اسم الخدمة')->required()->maxLength(120),
                        TextInput::make('h1')->label('العنوان الرئيسي (H1)')->required()->maxLength(120),
                        Textarea::make('summary')->label('ملخص (للكروت والوصف)')->required()->rows(2)->maxLength(300),
                        Textarea::make('intro')->label('مقدمة')->rows(4),
                        self::list('included', 'الخدمة بتشمل إيه', 'بند'),
                        self::list('warning_signs', 'علامات إنك محتاج الخدمة', 'علامة'),
                        Repeater::make('process_steps')
                            ->label('خطوات الشغل')
                            ->schema([
                                TextInput::make('title')->label('الخطوة')->required()->maxLength(60),
                                Textarea::make('text')->label('الشرح')->required()->rows(2),
                            ])
                            ->itemLabel(fn (array $state) => $state['title'] ?? null)
                            ->collapsed()
                            ->addActionLabel('أضف خطوة'),
                        self::list('price_factors', 'إيه اللي بيحدد السعر', 'عامل'),
                        RichEditor::make('body')->label('محتوى إضافي'),
                    ]),

                Section::make('السعر والإعدادات')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('starting_price')->label('يبدأ من (جنيه)')->numeric()->minValue(0)->helperText('فاضي = يظهر نص السعر تحت.'),
                        TextInput::make('price_note')->label('نص السعر')->placeholder('السعر بعد المعاينة')->maxLength(120),
                        TextInput::make('schema_service_type')->label('نوع الخدمة (schema, إنجليزي)')->maxLength(120)->extraInputAttributes(['dir' => 'ltr']),
                        Select::make('image')->label('صورة الكارت (مؤقتة)')->options(Placeholders::options()),
                        TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
                        Toggle::make('requires_24_7')->label('خدمة طوارئ 24/7 (تظهر بس لو 24/7 مفعّل في الإعدادات)'),
                    ]),

                PublishSection::make()->columnSpan(1),
                FaqRepeater::make()->columnSpan(2),
                SeoSection::make()->columnSpan(2),
            ]);
    }

    private static function list(string $name, string $label, string $noun): Repeater
    {
        return Repeater::make($name)
            ->label($label)
            ->simple(TextInput::make('item')->required()->maxLength(250))
            ->addActionLabel('أضف '.$noun);
    }
}
