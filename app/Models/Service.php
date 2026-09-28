<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSiteImages;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use App\Settings\BusinessSettings;
use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string $h1
 * @property string $summary
 * @property string|null $intro
 * @property array<int, string>|null $included
 * @property array<int, string>|null $warning_signs
 * @property array<int, array{title: string, text: string}>|null $process_steps
 * @property array<int, string>|null $price_factors
 * @property string|null $body
 * @property string|null $starting_price
 * @property string|null $price_note
 * @property Carbon|null $price_changed_at
 * @property string|null $schema_service_type
 * @property bool $requires_24_7
 * @property string|null $image
 * @property int $sort
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'name', 'h1', 'summary', 'intro', 'included', 'warning_signs', 'process_steps', 'price_factors', 'body', 'starting_price', 'price_note', 'schema_service_type', 'requires_24_7', 'image', 'sort', 'is_published', 'published_at'])]
class Service extends Model implements HasMedia, HasSeo
{
    /** @use HasFactory<ServiceFactory> */
    use AffectsPublicPages, BlocksUnconfirmedContent, HasFactory, HasSeoMeta, HasSiteImages, HasSlugHistory, Publishable, TracksContentModification;

    protected static function booted(): void
    {
        static::saving(function (self $service): void {
            if ($service->isDirty('starting_price')) {
                $service->price_changed_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'price_changed_at' => 'datetime',
            'included' => 'array',
            'warning_signs' => 'array',
            'process_steps' => 'array',
            'price_factors' => 'array',
            'starting_price' => 'decimal:2',
            'requires_24_7' => 'boolean',
            'sort' => 'integer',
        ];
    }

    public function contentAttributes(): array
    {
        return ['name', 'h1', 'summary', 'intro', 'included', 'warning_signs', 'process_steps', 'price_factors', 'body', 'starting_price', 'price_note', 'is_published'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Live on the site: published, and a 24/7-only service (emergency) only when the business really is 24/7.
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function live(Builder $query): void
    {
        $query->published()->orderBy('sort')->orderBy('id');

        if (! app(BusinessSettings::class)->is_24_7) {
            $query->where('requires_24_7', false);
        }
    }

    public function isLive(): bool
    {
        return $this->isPublished() && (! $this->requires_24_7 || app(BusinessSettings::class)->is_24_7);
    }

    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isLive() && ($robots === null || str_starts_with($robots, 'index'));
    }

    public function url(): string
    {
        return route('services.show', $this);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')->singleFile(); // service card + page header
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }

    /**
     * @return BelongsToMany<Area, $this>
     */
    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class)->withPivot('note');
    }

    /**
     * @return BelongsToMany<PriceGuide, $this>
     */
    public function priceGuides(): BelongsToMany
    {
        return $this->belongsToMany(PriceGuide::class);
    }
}
