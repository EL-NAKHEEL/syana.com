<?php

namespace App\Models;

use App\Catalog\Catalog;
use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * Editorial layer of a curated facet (/store/brand/x, /store/capacity/x, /store/type/x, /store/brand/x/y):
 * the unique intro that makes it indexable (with ≥ 3 live products).
 *
 * @property int $id
 * @property string $kind
 * @property int|null $brand_id
 * @property string|null $hp
 * @property string|null $type
 * @property string $path
 * @property string|null $h1
 * @property string|null $intro
 * @property string|null $body
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read Brand|null $brand
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['kind', 'brand_id', 'hp', 'type', 'h1', 'intro', 'body', 'is_published', 'published_at'])]
class FacetPage extends Model implements HasSeo
{
    use AffectsPublicPages, BlocksUnconfirmedContent, HasSeoMeta, Publishable, TracksContentModification;

    public const KINDS = ['brand' => 'ماركة', 'capacity' => 'قدرة', 'type' => 'نوع', 'brand_capacity' => 'ماركة + قدرة'];

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            $page->loadMissing('brand');
            $page->path = self::pathFor($page->kind, $page->brand?->slug, $page->hp !== null ? (float) $page->hp : null, $page->type);
        });
    }

    public static function pathFor(string $kind, ?string $brand, ?float $hp, ?string $type): string
    {
        return match ($kind) {
            'brand' => 'brand/'.$brand,
            'capacity' => 'capacity/'.Catalog::hpSlug((float) $hp),
            'type' => 'type/'.$type,
            'brand_capacity' => 'brand/'.$brand.'/'.Catalog::hpSlug((float) $hp),
            default => throw new \InvalidArgumentException("Unknown facet kind [{$kind}]"),
        };
    }

    public function contentAttributes(): array
    {
        return ['h1', 'intro', 'body', 'is_published'];
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }
}
