<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use App\Settings\SeoSettings;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * A price guide renders LIVE prices from the services (and, from P2, products) tables: no price is stored here.
 * «آخر تحديث» and {year} follow real price changes only.
 *
 * @property int $id
 * @property string $slug
 * @property string $title Admin title; may contain {year}
 * @property string $h1 May contain {year}
 * @property string|null $intro
 * @property string|null $body
 * @property string $scope
 * @property int $sort
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read SeoMeta|null $seoMeta
 * @property-read Collection<int, Service> $services
 */
#[Fillable(['slug', 'title', 'h1', 'intro', 'body', 'scope', 'sort', 'is_published', 'published_at'])]
class PriceGuide extends Model implements HasSeo
{
    use AffectsPublicPages, BlocksUnconfirmedContent, HasSeoMeta, HasSlugHistory, Publishable, TracksContentModification;

    public const SCOPES = ['services' => 'تكلفة خدمات'];

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function contentAttributes(): array
    {
        return ['title', 'h1', 'intro', 'body', 'is_published'];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withPivot('sort')->orderByPivot('sort');
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }

    /**
     * Rows shown in the price table: live services in scope.
     *
     * @return \Illuminate\Support\Collection<int, Service>
     */
    public function rows(): \Illuminate\Support\Collection
    {
        return $this->services->filter(fn (Service $service) => $service->isLive())->values();
    }

    /** A guide goes live only with at least one real price to show (no thin «بعد المعاينة»-only pages). */
    public function isLive(): bool
    {
        return $this->isPublished() && $this->rows()->contains(fn (Service $s) => $s->starting_price !== null);
    }

    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isLive() && ($robots === null || str_starts_with($robots, 'index'));
    }

    public function lastPriceChange(): ?CarbonInterface
    {
        /** @var CarbonInterface|null */
        return $this->rows()->pluck('price_changed_at')->filter()->max();
    }

    /** {year} renders only while prices changed this calendar year and within the freshness window. */
    public function pricesAreFresh(): bool
    {
        $changed = $this->lastPriceChange();

        return $changed !== null
            && $changed->isCurrentYear()
            && $changed->greaterThanOrEqualTo(now()->subDays(app(SeoSettings::class)->price_freshness_days));
    }

    public function render(string $text): string
    {
        $year = $this->pricesAreFresh() ? (string) now()->year : '';

        return trim((string) preg_replace('/\s{2,}/u', ' ', str_replace('{year}', $year, $text)));
    }

    public function url(): string
    {
        return route('prices.show', $this);
    }
}
