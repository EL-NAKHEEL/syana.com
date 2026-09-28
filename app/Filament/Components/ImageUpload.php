<?php

namespace App\Filament\Components;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;

/**
 * Photo upload + its Arabic alt text. Empty upload = the temporary placeholder keeps showing.
 */
class ImageUpload
{
    public static function make(string $collection, string $label, bool $multiple = false, ?string $help = null): Group
    {
        $altField = $collection.'_alt';

        $upload = SpatieMediaLibraryFileUpload::make($collection)
            ->label($label)
            ->collection($collection)
            ->image()
            ->imageEditor()
            ->maxSize(5120)
            ->customProperties(fn (Get $get): array => array_filter(['alt' => $get($altField)]))
            ->helperText($help ?? 'سيبها فاضية عشان تفضل الصورة المؤقتة. اسم الملف بالإنجليزي وبيوصف الصورة.');

        if ($multiple) {
            $upload->multiple()->reorderable();
        }

        return Group::make([
            $upload,
            TextInput::make($altField)
                ->label('وصف الصورة بالعربي (alt) — للصور اللي هترفعها دلوقتي')
                ->dehydrated(false)
                ->maxLength(160),
        ]);
    }
}
