<?php

namespace App\Models\Concerns;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeoMeta
{
    /**
     * @return MorphOne<SeoMeta, $this>
     */
    public function seoMeta(): MorphOne
    {
        return $this->morphOne(SeoMeta::class, 'seoable');
    }

    /**
     * Default: indexable when published and not switched to noindex in the SEO panel.
     */
    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isPublished() && ($robots === null || str_starts_with($robots, 'index'));
    }

    public static function bootHasSeoMeta(): void
    {
        static::deleting(fn (self $model) => $model->seoMeta()->delete());
    }
}
