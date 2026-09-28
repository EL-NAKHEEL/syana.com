<?php

namespace App\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/**
 * Stores width/height on every uploaded image so <img> tags get explicit dimensions (no layout shift).
 */
class RecordImageDimensions
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (! str_starts_with((string) $media->mime_type, 'image/')) {
            return;
        }

        $size = @getimagesize($media->getPath());
        if ($size === false) {
            return;
        }

        $media->setCustomProperty('width', $size[0])->setCustomProperty('height', $size[1])->saveQuietly();
    }
}
