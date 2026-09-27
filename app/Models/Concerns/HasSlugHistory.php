<?php

namespace App\Models\Concerns;

use App\Models\SlugHistory;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Records every previous slug so old URLs 301 to the current one.
 */
trait HasSlugHistory
{
    public static function bootHasSlugHistory(): void
    {
        static::updated(function (self $model): void {
            if (! $model->wasChanged('slug')) {
                return;
            }

            $old = (string) $model->getOriginal('slug');

            if ($old !== '') {
                SlugHistory::query()->updateOrCreate(
                    ['sluggable_type' => $model->getMorphClass(), 'old_slug' => $old],
                    ['sluggable_id' => $model->getKey(), 'created_at' => now()],
                );
            }

            // A slug that is live again must not redirect anywhere.
            SlugHistory::query()
                ->where('sluggable_type', $model->getMorphClass())
                ->where('old_slug', $model->slug)
                ->delete();
        });

        static::deleted(fn (self $model) => $model->slugHistories()->delete());
    }

    /**
     * @return MorphMany<SlugHistory, $this>
     */
    public function slugHistories(): MorphMany
    {
        return $this->morphMany(SlugHistory::class, 'sluggable');
    }

    public static function findBySlugHistory(string $slug): ?static
    {
        $history = SlugHistory::query()
            ->where('sluggable_type', static::query()->getModel()->getMorphClass())
            ->where('old_slug', $slug)
            ->first();

        /** @var static|null */
        return $history ? static::query()->find($history->sluggable_id) : null;
    }
}
