<?php

namespace App\Filament\Pages;

use App\Settings\LayoutSettings;
use App\Support\Navigation;
use BackedEnum;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Pages\SettingsPage;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ManageLayout extends SettingsPage
{
    protected static string $settings = LayoutSettings::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBars3;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = -1;

    protected static ?string $navigationLabel = 'الهيدر والقائمة والفوتر';

    protected static ?string $title = 'الهيدر والقائمة والفوتر';

    protected static ?string $slug = 'manage-layout';

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Show every menu entry, including ones added to the site after the menu was last saved.
        $menu = is_array($data['menu'] ?? null) ? $data['menu'] : [];
        $saved = collect($menu)->filter(fn ($entry) => isset(Navigation::ITEMS[$entry['key'] ?? null]))->keyBy('key');
        foreach (Navigation::ITEMS as $key => $item) {
            $saved->put($key, $saved->get($key, ['key' => $key, 'label' => $item['label'], 'visible' => $item['visible'] ?? true]));
        }
        $data['menu'] = $saved->values()->all();

        return $data;
    }

    public function form(Schema $schema): Schema
    {
        $labels = array_map(fn (array $item) => $item['label'], Navigation::ITEMS);

        return $schema->components([
            Section::make('الشريط العلوي والهيدر')
                ->columns(2)
                ->schema([
                    TextInput::make('topbar_label')->label('النص قبل رقم التليفون')->required()->maxLength(40),
                    Toggle::make('header_cta_visible')->label('إظهار زرار الواتساب في الهيدر')->inline(false),
                    TextInput::make('header_cta_label')->label('كلام الزرار')->required()->maxLength(40),
                    TextInput::make('header_cta_message')->label('الرسالة اللي بتتكتب في الواتساب')->required()->maxLength(160),
                ]),
            Section::make('القائمة الرئيسية')
                ->columnSpanFull()
                ->description('اسحب لتغيير الترتيب. القسم بيظهر في القائمة بس لما يكون فيه محتوى منشور.')
                ->schema([
                    Repeater::make('menu')
                        ->hiddenLabel()
                        ->table([
                            TableColumn::make('القسم'),
                            TableColumn::make('الاسم في القائمة'),
                            TableColumn::make('ظاهر')->width('6rem'),
                        ])
                        ->schema([
                            Select::make('key')->label('القسم')->options($labels)->disabled()->dehydrated(),
                            TextInput::make('label')->label('الاسم في القائمة')->required()->maxLength(30),
                            Toggle::make('visible')->label('ظاهر')->inline(false),
                        ])
                        ->reorderable()
                        ->addable(false)
                        ->deletable(false)
                        ->itemLabel(fn (array $state) => $labels[$state['key'] ?? ''] ?? null),
                ]),
            Section::make('الفوتر')
                ->columns(2)
                ->schema([
                    Textarea::make('footer_about')->label('نبذة عن الشركة')->required()->rows(2)->maxLength(300)->columnSpanFull(),
                    Toggle::make('footer_show_links')->label('إظهار «روابط سريعة»'),
                    Toggle::make('footer_show_areas')->label('إظهار «مناطق الخدمة»'),
                ]),
            Section::make('الأزرار العائمة')
                ->columns(2)
                ->schema([
                    Toggle::make('float_whatsapp')->label('زرار الواتساب العائم'),
                    Toggle::make('float_call')->label('زرار الاتصال العائم'),
                ]),
        ]);
    }
}
