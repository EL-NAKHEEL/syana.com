<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Filament\Components\ImageUpload;
use App\Filament\Components\SeoSection;
use App\Models\Page;
use App\Support\Placeholders;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Group;
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
                        RichEditor::make('body')->label(fn (Get $get) => $get('template') === 'hub' ? 'نص إضافي تحت القائمة (اختياري)' : 'المحتوى')->helperText('أي معلومة مش مؤكدة اكتبها [TODO: …] — مش هينفع تتنشر وهي فيها TODO.'),
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
                        TextInput::make('data.hero.label')->label('السطر البرتقالي فوق العنوان')->maxLength(60),
                        TextInput::make('data.hero.slogan')->label('الشعار (جزء من H1)')->maxLength(60),
                        TextInput::make('data.hero.keywords')->label('سطر الكلمات المفتاحية (جزء من H1)')->maxLength(80),
                        Textarea::make('data.hero.lead')->label('نص تحت العنوان')->rows(2),
                    ]),

                Section::make('ترتيب أقسام الرئيسية')
                    ->description('اسحب لتغيير الترتيب، واقفل «ظاهر» لإخفاء القسم.')
                    ->columnSpan(2)
                    ->visible($isHome)
                    ->collapsible()
                    ->schema([
                        Repeater::make('data.sections')
                            ->hiddenLabel()
                            ->table([
                                TableColumn::make('القسم'),
                                TableColumn::make('ظاهر')->width('6rem'),
                            ])
                            ->schema([
                                Select::make('key')->label('القسم')->options(Page::HOME_SECTIONS)->disabled()->dehydrated(),
                                Toggle::make('visible')->label('ظاهر')->inline(false),
                            ])
                            ->reorderable()
                            ->addable(false)
                            ->deletable(false)
                            ->itemLabel(fn (array $state) => Page::HOME_SECTIONS[$state['key'] ?? ''] ?? null)
                            ->afterStateHydrated(function (Repeater $component, ?array $state): void {
                                $saved = collect($state ?? [])->keyBy('key');
                                $all = collect(Page::HOME_SECTIONS)->keys()
                                    ->map(fn (string $key) => $saved->get($key, ['key' => $key, 'visible' => true]));
                                $ordered = $saved->keys()->filter(fn ($k) => isset(Page::HOME_SECTIONS[$k]))
                                    ->map(fn ($k) => $saved[$k])->concat($all->reject(fn ($e) => $saved->has($e['key'])));
                                $component->state($ordered->values()->all());
                            }),
                    ]),

                Section::make('أقسام الرئيسية')
                    ->columnSpan(2)
                    ->visible($isHome)
                    ->collapsible()
                    ->schema([
                        TextInput::make('data.about.eyebrow')->label('الكلمة الصغيرة فوق «من نحن»')->placeholder('من نحن')->maxLength(40),
                        TextInput::make('data.about.heading')->label('عنوان «من نحن»'),
                        Textarea::make('data.about.text')->label('نص «من نحن»')->rows(2),
                        Repeater::make('data.about.checklist')->label('نقاط «من نحن»')->simple(TextInput::make('item')->required()->maxLength(120))->addActionLabel('أضف نقطة'),
                        TextInput::make('data.services.eyebrow')->label('الكلمة الصغيرة فوق الخدمات')->placeholder('خدماتنا')->maxLength(40),
                        TextInput::make('data.services.heading')->label('عنوان قسم الخدمات'),
                        Textarea::make('data.services.intro')->label('مقدمة الخدمات')->rows(2),
                        self::items('data.services.items', 'خدمة', withImage: true),
                        TextInput::make('data.why.eyebrow')->label('الكلمة الصغيرة فوق «ليه إحنا»')->placeholder('ميزاتنا')->maxLength(40),
                        TextInput::make('data.why.heading')->label('عنوان «ليه إحنا»'),
                        self::items('data.why.items', 'ميزة'),
                        TextInput::make('data.process.eyebrow')->label('الكلمة الصغيرة فوق خطوات الشغل')->placeholder('خطوات الشغل')->maxLength(40),
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
                            ->hiddenLabel()
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

                Section::make('الصور')
                    ->columnSpan(2)
                    ->collapsible()
                    ->schema([
                        Group::make([ImageUpload::make('hero', 'صورة الهيرو (أول الصفحة)')])->visible($isHome),
                        Group::make([ImageUpload::make('gallery', 'صور «من نحن» و«ليه إحنا» (1 و2 في «من نحن»، 3 في «ليه إحنا»)', multiple: true)])
                            ->visible(fn (Get $get) => in_array($get('template'), ['home', 'about'], true)),
                        Group::make([ImageUpload::make('header', 'صورة خلفية رأس الصفحة')])->visible(fn (Get $get) => $get('template') !== 'home'),
                    ]),

                SeoSection::make()->columnSpan(2),
            ]);
    }

    private static function items(string $path, string $noun, bool $withImage = false): Repeater
    {
        return Repeater::make($path)
            ->label('العناصر')
            ->schema(array_filter([
                TextInput::make('title')->label('العنوان')->required()->maxLength(120),
                Textarea::make('text')->label('النص')->required()->rows(2),
                $withImage ? Select::make('image')->label('الصورة (مؤقتة لحد الصور الحقيقية)')->options(Placeholders::options()) : null,
            ]))
            ->itemLabel(fn (array $state) => $state['title'] ?? null)
            ->collapsed()
            ->addActionLabel('أضف '.$noun);
    }
}
