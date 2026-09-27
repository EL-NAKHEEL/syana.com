<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Components\SeoSection;
use App\Models\Page;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        $isHome = fn (Get $get) => $get('template') === 'home';

        return $schema
            ->columns(3)
            ->components([
                Section::make('المحتوى')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')->label('العنوان الرئيسي (H1)')->required()->maxLength(120),
                        Textarea::make('intro')->label('مقدمة')->rows(3),
                        RichEditor::make('body')->label('المحتوى')->helperText('أي معلومة مش مؤكدة اكتبها [TODO: …] — مش هينفع تتنشر وهي فيها TODO.'),
                    ]),

                Section::make('النشر')
                    ->columnSpan(1)
                    ->schema([
                        TextInput::make('slug')->label('المعرّف')->required()->disabledOn('edit')->unique(ignoreRecord: true)->extraInputAttributes(['dir' => 'ltr']),
                        Select::make('template')->label('القالب')->options(Page::TEMPLATES)->required()->live()->disabledOn('edit'),
                        Toggle::make('is_published')->label('منشورة')->helperText('المحتوى المنقول من الموقع القديم مش بيتنشر غير بعد مراجعتك.'),
                        DateTimePicker::make('published_at')->label('تاريخ النشر'),
                    ]),

                Section::make('الهيرو (الرئيسية)')
                    ->columnSpan(2)
                    ->visible($isHome)
                    ->schema([
                        TextInput::make('data.hero.slogan')->label('الشعار (جزء من H1)')->maxLength(60),
                        TextInput::make('data.hero.keywords')->label('سطر الكلمات المفتاحية (جزء من H1)')->maxLength(80),
                        Textarea::make('data.hero.lead')->label('نص تحت العنوان')->rows(2),
                        TextInput::make('data.hero.stamp')->label('الختم')->maxLength(40),
                    ]),

                Section::make('أقسام الرئيسية')
                    ->columnSpan(2)
                    ->visible($isHome)
                    ->collapsible()
                    ->schema([
                        TextInput::make('data.services.heading')->label('عنوان قسم الخدمات'),
                        Textarea::make('data.services.intro')->label('مقدمة الخدمات')->rows(2),
                        self::items('data.services.items', 'خدمة'),
                        TextInput::make('data.why.heading')->label('عنوان «ليه إحنا»'),
                        self::items('data.why.items', 'ميزة'),
                        TextInput::make('data.process.heading')->label('عنوان خطوات الشغل'),
                        self::items('data.process.steps', 'خطوة'),
                        TextInput::make('data.cta.heading')->label('عنوان الدعوة للتواصل'),
                        Textarea::make('data.cta.text')->label('نص الدعوة للتواصل')->rows(2),
                    ]),

                Section::make('الأسئلة الشائعة')
                    ->columnSpan(2)
                    ->collapsible()
                    ->schema([
                        Repeater::make('faqs')
                            ->label('')
                            ->relationship()
                            ->orderColumn('sort')
                            ->schema([
                                TextInput::make('question')->label('السؤال')->required()->maxLength(250),
                                Textarea::make('answer')->label('الإجابة')->required()->rows(3),
                            ])
                            ->itemLabel(fn (array $state) => $state['question'] ?? null)
                            ->collapsed()
                            ->addActionLabel('أضف سؤال'),
                    ]),

                SeoSection::make()->columnSpan(2),
            ]);
    }

    private static function items(string $path, string $noun): Repeater
    {
        return Repeater::make($path)
            ->label('العناصر')
            ->schema([
                TextInput::make('title')->label('العنوان')->required()->maxLength(120),
                Textarea::make('text')->label('النص')->required()->rows(2),
            ])
            ->itemLabel(fn (array $state) => $state['title'] ?? null)
            ->collapsed()
            ->addActionLabel('أضف '.$noun);
    }
}
