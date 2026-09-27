<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;

/**
 * Content is live only when is_published is true and published_at is not in the future.
 * Seeded/demo/migrated content ships unpublished.
 */
trait Publishable
{
    public function initializePublishable(): void
    {
        $this->mergeCasts([
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ]);
    }

    public static function bootPublishable(): void
    {
        static::saving(function (self $model): void {
            if ($model->is_published && $model->published_at === null) {
                $model->published_at = now();
            }
        });
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function published(Builder $query): void
    {
        $query->where($this->qualifyColumn('is_published'), true)
            ->where(fn (Builder $query) => $query
                ->whereNull($this->qualifyColumn('published_at'))
                ->orWhere($this->qualifyColumn('published_at'), '<=', now()));
    }

    public function isPublished(): bool
    {
        return $this->is_published && ($this->published_at === null || ! $this->published_at->isFuture());
    }
}
