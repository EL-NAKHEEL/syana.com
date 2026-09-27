<?php

namespace App\Models\Concerns;

use Carbon\CarbonInterface;

/**
 * Maintains content_modified_at, the only date used for sitemap <lastmod> and schema dateModified.
 * It changes only when user-visible attributes change (never on housekeeping updates).
 */
trait TracksContentModification
{
    public function initializeTracksContentModification(): void
    {
        $this->mergeCasts(['content_modified_at' => 'datetime']);
    }

    public static function bootTracksContentModification(): void
    {
        static::saving(function (self $model): void {
            if (! $model->exists || $model->isDirty($model->contentAttributes())) {
                $model->content_modified_at = now();
            }
        });
    }

    /**
     * Attributes whose change means the public page changed.
     *
     * @return array<int, string>
     */
    abstract public function contentAttributes(): array;

    public function lastModified(): ?CarbonInterface
    {
        return $this->content_modified_at ?? $this->published_at ?? $this->created_at;
    }
}
