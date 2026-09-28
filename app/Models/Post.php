<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $excerpt
 * @property string $body
 * @property int|null $post_category_id
 * @property int $author_id
 * @property int|null $reviewed_by_id
 * @property int|null $service_id
 * @property int $reading_minutes
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read Person $author
 * @property-read Person|null $reviewer
 * @property-read PostCategory|null $category
 * @property-read Service|null $service
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'title', 'excerpt', 'body', 'post_category_id', 'author_id', 'reviewed_by_id', 'service_id', 'is_published', 'published_at'])]
class Post extends Model implements HasMedia, HasSeo
{
    use AffectsPublicPages, BlocksUnconfirmedContent, HasSeoMeta, HasSlugHistory, InteractsWithMedia, Publishable, TracksContentModification;

    /** Arabic reading speed used for «وقت القراءة». */
    private const WORDS_PER_MINUTE = 180;

    protected static function booted(): void
    {
        static::saving(function (self $post): void {
            $words = count(preg_split('/\s+/u', trim(strip_tags($post->body)), -1, PREG_SPLIT_NO_EMPTY) ?: []);
            $post->reading_minutes = max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
        });
    }

    public function contentAttributes(): array
    {
        return ['title', 'excerpt', 'body', 'post_category_id', 'author_id', 'is_published'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')->nonQueued()->fit(Fit::Crop, 640, 360)->format('webp');
        $this->addMediaConversion('large')->fit(Fit::Max, 1280, 1280)->format('webp');
    }

    /**
     * «وقت القراءة» with Arabic number agreement (دقيقة / دقيقتين / 3–10 دقايق / 11+ دقيقة).
     */
    public function readingTime(): string
    {
        $n = $this->reading_minutes;

        return match (true) {
            $n <= 1 => 'دقيقة قراءة',
            $n === 2 => 'دقيقتين قراءة',
            $n <= 10 => $n.' دقايق قراءة',
            default => $n.' دقيقة قراءة',
        };
    }

    public function isLive(): bool
    {
        return $this->isPublished() && $this->author->isPublished();
    }

    public function url(): string
    {
        return route('blog.show', $this->slug);
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'author_id');
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Person::class, 'reviewed_by_id');
    }

    /**
     * @return BelongsTo<PostCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(PostCategory::class, 'post_category_id');
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
