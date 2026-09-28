<?php

namespace App\Models;

use App\Events\PublicContentChanged;
use App\Models\Concerns\AffectsPublicPages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * A customer review, shown only after moderation. Business-level reviews are never marked up as
 * aggregateRating; product aggregateRating uses only approved reviews of that product shown on its page.
 *
 * @property int $id
 * @property string $name
 * @property int|null $area_id
 * @property string|null $reviewable_type
 * @property int|null $reviewable_id
 * @property int $rating
 * @property string $body
 * @property int|null $temp_before
 * @property int|null $temp_after
 * @property string|null $video_url
 * @property string $status
 * @property Carbon|null $approved_at
 * @property Carbon|null $created_at
 * @property-read Area|null $area
 * @property-read Model|null $reviewable
 */
#[Fillable(['name', 'area_id', 'reviewable_type', 'reviewable_id', 'rating', 'body', 'temp_before', 'temp_after', 'video_url', 'status', 'ip_hash'])]
class Review extends Model
{
    use AffectsPublicPages;

    public const STATUSES = ['pending' => 'بانتظار المراجعة', 'approved' => 'منشور', 'rejected' => 'مرفوض'];

    protected static function booted(): void
    {
        // Only approved reviews render publicly; pending submissions never flush the page cache.
        $changed = function (self $review): void {
            if ($review->status === 'approved' || $review->getOriginal('status') === 'approved') {
                PublicContentChanged::dispatch($review);
            }
        };
        static::saved($changed);
        static::deleted($changed);

        static::saving(function (self $review): void {
            if ($review->isDirty('status') && $review->status === 'approved' && $review->approved_at === null) {
                $review->approved_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return ['rating' => 'integer', 'approved_at' => 'datetime'];
    }

    /**
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function approved(Builder $query): void
    {
        $query->where('status', 'approved')->latest('approved_at');
    }

    /**
     * Approved reviews shown on a product/service page (and used for its aggregateRating, products only).
     *
     * @return Collection<int, static>
     */
    public static function shownFor(Model $model, int $limit = 12): Collection
    {
        return static::query()->approved()->with('area')
            ->where('reviewable_type', $model->getMorphClass())->where('reviewable_id', $model->getKey())
            ->limit($limit)->get();
    }

    /**
     * @return MorphTo<Model, $this>
     */
    public function reviewable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }
}
