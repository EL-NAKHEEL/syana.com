<?php

namespace App\Catalog;

use App\Models\Brand;
use App\Models\FacetPage;
use App\Models\Product;
use App\Settings\SeoSettings;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * A curated store facet (PLAN.md §6.3). Only these four shapes exist as URLs; every other combination is a
 * GET filter on /store (noindex). Indexable only with a published FacetPage intro and ≥ 3 live products.
 */
final class Facet
{
    private ?FacetPage $page = null;

    private bool $pageLoaded = false;

    public function __construct(
        public readonly string $kind,
        public readonly ?Brand $brand = null,
        public readonly ?float $hp = null,
        public readonly ?string $type = null,
    ) {}

    public static function brand(Brand $brand): self
    {
        return new self('brand', brand: $brand);
    }

    public static function capacity(float $hp): self
    {
        return new self('capacity', hp: $hp);
    }

    public static function type(string $type): self
    {
        return new self('type', type: $type);
    }

    public static function brandCapacity(Brand $brand, float $hp): self
    {
        return new self('brand_capacity', brand: $brand, hp: $hp);
    }

    public function path(): string
    {
        return FacetPage::pathFor($this->kind, $this->brand?->slug, $this->hp, $this->type);
    }

    public function url(): string
    {
        return url('/store/'.$this->path());
    }

    /** «تكييف شارب 1.5 حصان» */
    public function label(): string
    {
        return trim(implode(' ', array_filter([
            'تكييف',
            $this->brand?->name_ar,
            $this->type !== null ? Catalog::TYPES[$this->type] : null,
            $this->hp !== null ? Catalog::hpLabel($this->hp).' حصان' : null,
        ])));
    }

    /**
     * @return Builder<Product>
     */
    public function products(): Builder
    {
        return Product::query()->live()
            ->when($this->brand, fn (Builder $q) => $q->where('brand_id', $this->brand?->id))
            ->when($this->hp !== null, fn (Builder $q) => $q->where('hp', $this->hp))
            ->when($this->type !== null, fn (Builder $q) => $q->where('type', $this->type));
    }

    public function page(): ?FacetPage
    {
        if (! $this->pageLoaded) {
            $this->page = FacetPage::query()->with(['seoMeta', 'faqs'])->where('path', $this->path())->first();
            $this->pageLoaded = true;
        }

        return $this->page;
    }

    public function isIndexable(int $liveProducts): bool
    {
        $page = $this->page();
        $robots = $page?->seoMeta?->robots;

        return $page !== null
            && $page->isPublished()
            && filled($page->intro)
            && $liveProducts >= (int) config('site.seo.facet_min_products')
            && ($robots === null || str_starts_with($robots, 'index'));
    }

    public function lastPriceChange(): ?CarbonInterface
    {
        $value = $this->products()->max('price_changed_at');

        return $value ? Carbon::parse($value) : null;
    }

    public function pricesAreFresh(): bool
    {
        $changed = $this->lastPriceChange();

        return $changed !== null
            && $changed->isCurrentYear()
            && $changed->greaterThanOrEqualTo(now()->subDays(app(SeoSettings::class)->price_freshness_days));
    }

    /** PLAN.md §6.2 templates, e.g. «تكييف شارب: الأسعار والموديلات 2026». */
    public function title(): string
    {
        $year = $this->pricesAreFresh() ? ' '.now()->year : '';

        return $this->kind === 'brand_capacity'
            ? $this->label().': الأسعار'.$year
            : $this->label().': الأسعار والموديلات'.$year;
    }
}
