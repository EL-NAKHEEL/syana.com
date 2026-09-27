<?php

namespace App\Catalog;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Store filters and sorting from GET params. Any of them makes the page noindex, follow with no canonical
 * (their names are listed in config('site.seo.filter_params')).
 */
final class ProductFilters
{
    public const SORTS = [
        'featured' => 'الترتيب الافتراضي',
        'price-asc' => 'السعر: من الأقل',
        'price-desc' => 'السعر: من الأعلى',
        'hp-asc' => 'القدرة: من الأصغر',
    ];

    /**
     * @param  array<int, string>  $brands
     * @param  array<int, string>  $hp
     * @param  array<int, string>  $types
     */
    public function __construct(
        public readonly array $brands = [],
        public readonly array $hp = [],
        public readonly array $types = [],
        public readonly ?string $cooling = null,
        public readonly ?bool $inverter = null,
        public readonly ?int $priceMin = null,
        public readonly ?int $priceMax = null,
        public readonly string $sort = 'featured',
    ) {}

    public static function fromRequest(Request $request): self
    {
        $list = fn (string $key, array $allowed) => array_values(array_intersect((array) $request->query($key, []), $allowed));
        $int = fn (string $key) => is_numeric($request->query($key)) ? max(0, (int) $request->query($key)) : null;

        return new self(
            brands: array_values(array_filter((array) $request->query('brand', []), 'is_string')),
            hp: $list('hp', array_keys(Catalog::CAPACITIES)),
            types: $list('type', array_keys(Catalog::TYPES)),
            cooling: in_array($request->query('cooling'), array_keys(Catalog::COOLING), true) ? (string) $request->query('cooling') : null,
            inverter: $request->query('inverter') === '1' ? true : null,
            priceMin: $int('price_min'),
            priceMax: $int('price_max'),
            sort: array_key_exists((string) $request->query('sort'), self::SORTS) ? (string) $request->query('sort') : 'featured',
        );
    }

    /**
     * @param  Builder<Product>  $query
     * @return Builder<Product>
     */
    public function apply(Builder $query): Builder
    {
        $query
            ->when($this->brands !== [], fn (Builder $q) => $q->whereHas('brand', fn ($b) => $b->whereIn('slug', $this->brands)))
            ->when($this->hp !== [], fn (Builder $q) => $q->whereIn('hp', array_map(fn ($s) => Catalog::CAPACITIES[$s], $this->hp)))
            ->when($this->types !== [], fn (Builder $q) => $q->whereIn('type', $this->types))
            ->when($this->cooling !== null, fn (Builder $q) => $q->where('cooling', $this->cooling))
            ->when($this->inverter === true, fn (Builder $q) => $q->where('is_inverter', true))
            ->when($this->priceMin !== null, fn (Builder $q) => $q->whereRaw('COALESCE(sale_price, price) >= ?', [$this->priceMin]))
            ->when($this->priceMax !== null, fn (Builder $q) => $q->whereRaw('COALESCE(sale_price, price) <= ?', [$this->priceMax]));

        return match ($this->sort) {
            'price-asc' => $query->orderByRaw('COALESCE(sale_price, price) asc'),
            'price-desc' => $query->orderByRaw('COALESCE(sale_price, price) desc'),
            'hp-asc' => $query->orderBy('hp')->orderBy('price'),
            default => $query->orderByRaw("CASE WHEN stock_status = 'out_of_stock' THEN 1 ELSE 0 END")->orderBy('hp')->orderBy('price'),
        };
    }

    /**
     * Active filters as removable chips: label → URL without that value.
     *
     * @param  array<string, string>  $brandNames  slug → Arabic name
     * @return array<int, array{label: string, url: string}>
     */
    public function chips(Request $request, array $brandNames): array
    {
        $chips = [];
        $without = function (string $key, ?string $value = null) use ($request): string {
            $query = $request->query();
            unset($query['page']);
            if ($value === null) {
                unset($query[$key]);
            } else {
                $query[$key] = array_values(array_diff((array) ($query[$key] ?? []), [$value]));
            }

            return $request->url().($query ? '?'.http_build_query($query) : '');
        };

        foreach ($this->brands as $slug) {
            $chips[] = ['label' => $brandNames[$slug] ?? $slug, 'url' => $without('brand', $slug)];
        }
        foreach ($this->hp as $slug) {
            $chips[] = ['label' => Catalog::hpLabel(Catalog::CAPACITIES[$slug]).' حصان', 'url' => $without('hp', $slug)];
        }
        foreach ($this->types as $type) {
            $chips[] = ['label' => Catalog::TYPES[$type], 'url' => $without('type', $type)];
        }
        if ($this->cooling) {
            $chips[] = ['label' => Catalog::COOLING[$this->cooling], 'url' => $without('cooling')];
        }
        if ($this->inverter) {
            $chips[] = ['label' => 'إنفرتر', 'url' => $without('inverter')];
        }
        if ($this->priceMin !== null) {
            $chips[] = ['label' => 'من '.number_format($this->priceMin).' جنيه', 'url' => $without('price_min')];
        }
        if ($this->priceMax !== null) {
            $chips[] = ['label' => 'لحد '.number_format($this->priceMax).' جنيه', 'url' => $without('price_max')];
        }

        return $chips;
    }
}
