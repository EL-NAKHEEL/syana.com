<?php

namespace App\Filament\Resources\Areas\Schemas;

use App\Filament\Components\FaqRepeater;
use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use App\Models\Area;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Callout::make('شروط نشر صفحة المنطقة')
                    ->description(fn (?Area $record) => $record === null
                        ? 'صفحة المنطقة مش هتتنشر غير لما كل الخانات المحلية تكتمل (منع الصفحات المكررة).'
                        : (($failures = $record->guardFailures()) === [] ? 'كل الشروط مكتملة.' : implode(' • ', $failures)))
                    ->color(fn (?Area $record) => $record !== null && $record->guardFailures() === [] ? 'success' : 'warning')
                    ->columnSpanFull(),

                Section::make('المنطقة')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name_ar')->label('اسم المنطقة')->required()->maxLength(80),
                        TextInput::make('name_en')->label('الاسم بالإنجليزي')->maxLength(80)->extraInputAttributes(['dir' => 'ltr']),
                        TextInput::make('governorate')->label('المحافظة')->maxLength(80),
                        Textarea::make('local_intro')
                            ->label('مقدمة محلية (خاصة بالمنطقة دي بس)')
                            ->rows(8)
                            ->live(debounce: 500)
                            ->helperText(fn (?string $state) => Area::wordCount($state).' كلمة — المطلوب '.Area::MIN_INTRO_WORDS.' على الأقل. اكتب عن المنطقة فعلًا: طبيعة المباني، مشاكل التكييف الشائعة فيها، شغل عملناه هناك.'),
                        TextInput::make('response_time_note')->label('وقت الاستجابة')->placeholder('مثال: غالبًا في نفس اليوم')->maxLength(160),
                        Textarea::make('local_notes')->label('ملاحظات محلية')->rows(3),
                    ]),

                Section::make('الربط')
                    ->columnSpan(1)
                    ->schema([
                        CheckboxList::make('services')->label('الخدمات المتاحة في المنطقة')->relationship('services', 'name'),
                        Select::make('neighbors')->label('مناطق مجاورة')->relationship('neighbors', 'name_ar')->multiple()->preload(),
                        Toggle::make('show_in_footer')->label('تظهر في الفوتر'),
                        TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
                    ]),

                PublishSection::make()->columnSpan(1),
                FaqRepeater::make()->columnSpan(2),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
