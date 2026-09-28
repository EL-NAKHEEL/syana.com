<?php

namespace App\Filament\Resources\People\Schemas;

use App\Filament\Components\PublishSection;
use App\Filament\Components\SeoSection;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PersonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                Section::make('البيانات')
                    ->description('أشخاص حقيقيين بس، بأسمائهم وخبراتهم الفعلية.')
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('name')->label('الاسم')->required()->maxLength(120),
                        TextInput::make('job_title')->label('الوظيفة')->maxLength(120),
                        Textarea::make('bio')->label('نبذة')->rows(4),
                        Textarea::make('credentials')->label('الخبرات والشهادات')->rows(3),
                        TextInput::make('years_experience')->label('سنين الخبرة')->numeric()->minValue(0)->maxValue(60),
                        Repeater::make('same_as')
                            ->label('روابط حساباته (لينكدإن…)')
                            ->simple(TextInput::make('url')->url()->required()->extraInputAttributes(['dir' => 'ltr'])),
                        SpatieMediaLibraryFileUpload::make('photo')->label('صورة شخصية حقيقية')->collection('photo')->image()->avatar()->maxSize(2048),
                    ]),
                Section::make('الظهور')
                    ->columnSpan(1)
                    ->schema([
                        Toggle::make('is_author')->label('بيكتب في المدونة')->default(true),
                        Toggle::make('is_team_member')->label('يظهر في صفحة من نحن'),
                        TextInput::make('sort')->label('الترتيب')->numeric()->default(0),
                    ]),
                PublishSection::make()->columnSpan(1),
                SeoSection::make()->columnSpan(2),
            ]);
    }
}
