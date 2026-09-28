<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Filament\Facades\Filament;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

/**
 * Initial-letter avatar as an inline SVG: no request to ui-avatars.com (privacy, CSP, works offline).
 */
class LocalAvatarProvider implements AvatarProvider
{
    public function get(Model|Authenticatable $record): string
    {
        $initial = mb_substr(trim(Filament::getNameForDefaultAvatar($record)), 0, 1) ?: '?';

        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#02245B"/>'
            .'<text x="32" y="42" font-family="sans-serif" font-size="30" fill="#fff" text-anchor="middle">'.e($initial).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
