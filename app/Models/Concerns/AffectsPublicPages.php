<?php

namespace App\Models\Concerns;

use App\Events\PublicContentChanged;

/**
 * Any change to a model that renders on public pages flushes the guest page cache
 * and schedules sitemap regeneration (see PublicContentChanged listeners).
 */
trait AffectsPublicPages
{
    public static function bootAffectsPublicPages(): void
    {
        $dispatch = fn (self $model) => PublicContentChanged::dispatch($model);

        static::saved($dispatch);
        static::deleted($dispatch);
    }
}
