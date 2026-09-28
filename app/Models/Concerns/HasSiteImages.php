<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Owner-uploaded photos rendered through <x-image>: WebP at 800 px and 1600 px, original kept as the fallback.
 * Until a photo is uploaded, views fall back to the temporary placeholders.
 */
trait HasSiteImages
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('sm')->nonQueued()->fit(Fit::Max, 800, 800)->format('webp');
        $this->addMediaConversion('lg')->nonQueued()->fit(Fit::Max, 1600, 1600)->format('webp');
    }
}
