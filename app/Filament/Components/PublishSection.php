<?php

namespace App\Filament\Components;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;

/**
 * Slug + publish controls shared by content resources. Changing a slug keeps a 301 from the old URL.
 */
class PublishSection
{
    public static function make(bool $editableSlug = true): Section
    {
        return Section::make('النشر')
            ->schema([
                TextInput::make('slug')
                    ->label('الرابط (slug)')
                    ->helperText('إنجليزي صغير بشرطات. لو اتغير، الرابط القديم بيتحوّل 301 تلقائيًا.')
                    ->required()
                    ->regex('/^[a-z0-9]+(?:-[a-z0-9]+)*$/')
                    ->unique(ignoreRecord: true)
                    ->disabled(! $editableSlug)
                    ->extraInputAttributes(['dir' => 'ltr']),
                Toggle::make('is_published')->label('منشورة')->helperText('مش هينفع تتنشر وفيها [TODO].'),
                DateTimePicker::make('published_at')->label('تاريخ النشر'),
            ]);
    }
}
