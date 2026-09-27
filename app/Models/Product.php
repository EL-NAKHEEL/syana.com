<?php

namespace App\Models;

use App\Catalog\Catalog;
use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use App\Support\ArabicNormalizer;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * @property int $id
 * @property int $brand_id
 * @property string $slug
 * @property string $name
 * @property string $model_number
 * @property string|null $sku
 * @property string $type
 * @property string $hp
 * @property int|null $btu
 * @property string $cooling
 * @property bool $is_inverter
 * @property string|null $energy_class
 * @property int|null $room_area_min
 * @property int|null $room_area_max
 * @property int|null $warranty_months
 * @property string|null $warranty_note
 * @property string $price
 * @property string|null $sale_price
 * @property Carbon|null $sale_ends_at
 * @property string|null $installments_note
 * @property bool|null $installation_included
 * @property string $stock_status
 * @property array<int, array{label: string, value: string}>|null $specs
 * @property string|null $short_description
 * @property string|null $description
 * @property string|null $search_text
 * @property Carbon|null $price_changed_at
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read Brand $brand
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['brand_id', 'slug', 'name', 'model_number', 'sku', 'type', 'hp', 'btu', 'cooling', 'is_inverter', 'energy_class', 'room_area_min', 'room_area_max', 'warranty_months', 'warranty_note', 'price', 'sale_price', 'sale_ends_at', 'installments_note', 'installation_included', 'stock_status', 'specs', 'short_description', 'description', 'is_published', 'published_at'])]
class Product extends Model implements HasMedia, HasSeo
{
    /** @use HasFactory<ProductFactory> */
    use AffectsPublicPages, BlocksUnconfirmedContent, HasFactory, HasSeoMeta, HasSlugHistory, InteractsWithMedia, Publishable, TracksContentModification;

    protected static function booted(): void
    {
        static::saving(function (self $product): void {
            if ($product->isDirty(['price', 'sale_price'])) {
                $product->price_changed_at = now();
            }

            $brand = $product->brand_id ? Brand::query()->find($product->brand_id) : null;
            $product->search_text = ArabicNormalizer::normalize(implode(' ', [
                'تكييف', $brand?->name_ar, $brand?->name_en, $product->name, $product->model_number, $product->sku,
                Catalog::TYPES[$product->type] ?? '', Catalog::hpLabel($product->hp).' حصان',
                Catalog::COOLING[$product->cooling] ?? '', $product->is_inverter ? 'انفرتر inverter' : '',
            ]));
        });

        static::created(fn (self $product) => $product->logPriceChange(null, null));

        static::updated(function (self $product): void {
            if ($product->wasChanged(['price', 'sale_price'])) {
                $product->logPriceChange($product->getOriginal('price'), $product->getOriginal('sale_price'));
            }
        });
    }

    private function logPriceChange(mixed $oldPrice, mixed $oldSalePrice): void
    {
        $this->priceChanges()->create([
            'old_price' => $oldPrice,
            'new_price' => $this->price,
            'old_sale_price' => $oldSalePrice,
            'new_sale_price' => $this->sale_price,
            'changed_at' => now(),
        ]);
    }

    protected function casts(): array
    {
        return [
            'hp' => 'decimal:2',
            'btu' => 'integer',
            'is_inverter' => 'boolean',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'sale_ends_at' => 'datetime',
            'installation_included' => 'boolean',
            'specs' => 'array',
            'price_changed_at' => 'datetime',
        ];
    }

    public function contentAttributes(): array
    {
        return ['name', 'model_number', 'type', 'hp', 'btu', 'cooling', 'is_inverter', 'energy_class', 'warranty_months', 'warranty_note', 'price', 'sale_price', 'installments_note', 'stock_status', 'specs', 'short_description', 'description', 'is_published'];
    }

    /**
     * @return array<int, string>
     */
    public function richTextAttributes(): array
    {
        return ['description'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')->nonQueued()->fit(Fit::Contain, 480, 480)->format('webp')->quality(78);
        $this->addMediaConversion('large')->fit(Fit::Contain, 1000, 1000)->format('webp')->quality(80);
    }

    /**
     * Listed in the store: published and not discontinued (out-of-stock stays live).
     *
     * @param  Builder<static>  $query
     */
    #[Scope]
    protected function live(Builder $query): void
    {
        $query->published()->where('stock_status', '!=', 'discontinued')->whereHas('brand', fn ($q) => $q->published());
    }

    public function isLive(): bool
    {
        return $this->isPublished() && $this->stock_status !== 'discontinued' && $this->brand->isPublished();
    }

    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isLive() && ($robots === null || str_starts_with($robots, 'index'));
    }

    public function onSale(): bool
    {
        return $this->sale_price !== null
            && (float) $this->sale_price < (float) $this->price
            && ($this->sale_ends_at === null || $this->sale_ends_at->isFuture());
    }

    public function currentPrice(): float
    {
        return (float) ($this->onSale() ? $this->sale_price : $this->price);
    }

    public function canBeOrdered(): bool
    {
        return in_array($this->stock_status, ['in_stock', 'preorder'], true);
    }

    public function url(): string
    {
        return route('store.product', $this->slug);
    }

    /** «تكييف شارب 1.5 حصان بارد ساخن إنفرتر» */
    public function descriptiveName(bool $withModel = true): string
    {
        return trim(implode(' ', array_filter([
            'تكييف',
            $this->brand->name_ar,
            Catalog::hpLabel($this->hp).' حصان',
            Catalog::COOLING[$this->cooling] ?? null,
            $this->is_inverter ? 'إنفرتر' : null,
            $withModel ? $this->model_number : null,
        ])));
    }

    /**
     * Image for cards/meta: the first product photo, or the temporary placeholder.
     */
    public function primaryImage(): ?Media
    {
        return $this->relationLoaded('media') ? $this->media->where('collection_name', 'images')->first() : $this->getFirstMedia('images');
    }

    /**
     * @return BelongsTo<Brand, $this>
     */
    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    /**
     * @return HasMany<ProductPriceChange, $this>
     */
    public function priceChanges(): HasMany
    {
        return $this->hasMany(ProductPriceChange::class);
    }

    /**
     * @return BelongsToMany<Product, $this>
     */
    public function related(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'related_products', 'product_id', 'related_id')->withPivot('sort')->orderByPivot('sort');
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }
}
