<?php

namespace App\Http\Controllers;

use App\Catalog\Catalog;
use App\Catalog\Facet;
use App\Catalog\ProductFilters;
use App\Http\Controllers\Concerns\ResolvesPublicRecords;
use App\Http\Controllers\Concerns\UsesHubPage;
use App\Models\Brand;
use App\Models\Product;
use App\Models\Review;
use App\Seo\Schema\ProductSchema;
use App\Seo\Seo;
use App\Seo\TitleBuilder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoreController extends Controller
{
    use ResolvesPublicRecords, UsesHubPage;

    private const PER_PAGE = 24;

    public function index(Request $request, Seo $seo): View
    {
        $filters = ProductFilters::fromRequest($request);
        $products = $this->paginate($filters->apply(Product::query()->live()), $request);

        abort_if($products->total() === 0 && ! $request->query() && ! Auth::check(), 404);

        $count = Product::query()->live()->count();
        $seo->title('متجر التكييفات: اشتري تكييفك أونلاين من النخيل كوول')
            ->description(TitleBuilder::limit('اختار من '.$count.' موديل تكييف بأنواع وقدرات مختلفة، بأسعار واضحة وطلب أونلاين بالدفع عند الاستلام. محتار؟ اتصل 01055207525 ونساعدك تختار.', 160))
            ->canonical(route('store.index'))
            ->page($products->currentPage())
            ->pageType('CollectionPage')
            ->breadcrumbs([['name' => 'المتجر', 'url' => route('store.index')]])
            ->ogKicker('المتجر');

        return view('store.index', [
            'hub' => $this->hubPage($seo, 'store'),
            'products' => $products,
            'filters' => $filters,
            'facetLinks' => $this->facetLinks(),
            'formAction' => route('store.index'),
        ] + $this->filterOptions($request, $filters));
    }

    public function brand(Request $request, Seo $seo, string $brand): View|RedirectResponse
    {
        return $this->facet($request, $seo, Facet::brand($this->brandOr404($brand)));
    }

    public function brandCapacity(Request $request, Seo $seo, string $brand, string $hp): View|RedirectResponse
    {
        return $this->facet($request, $seo, Facet::brandCapacity($this->brandOr404($brand), $this->hpOr404($hp)));
    }

    public function capacity(Request $request, Seo $seo, string $hp): View|RedirectResponse
    {
        return $this->facet($request, $seo, Facet::capacity($this->hpOr404($hp)));
    }

    public function type(Request $request, Seo $seo, string $type): View|RedirectResponse
    {
        abort_unless(array_key_exists($type, Catalog::TYPES), 404);

        return $this->facet($request, $seo, Facet::type($type));
    }

    public function product(Seo $seo, string $slug): View|RedirectResponse
    {
        /** @var Product $product */
        $product = $this->resolveRecord(
            Product::class,
            $slug,
            ['brand', 'media', 'seoMeta', 'faqs'],
            fn (Product $p) => $p->isPublished() && $p->brand->isPublished(),
            fn (Product $p) => $p->url(),
        );

        // Discontinued models 301 to the closest live facet (PLAN.md §6.3).
        if ($product->stock_status === 'discontinued' && ! Auth::check()) {
            return redirect()->to($this->closestFacetUrl($product), 301);
        }

        $related = $product->related()->live()->with(['brand', 'media'])->get();
        if ($related->count() < 4) {
            $related = $related->concat(
                Product::query()->live()->with(['brand', 'media'])
                    ->whereKeyNot([$product->id, ...$related->modelKeys()])
                    ->where('hp', $product->hp)
                    ->orderByRaw("CASE WHEN stock_status = 'in_stock' THEN 0 ELSE 1 END")
                    ->limit(4 - $related->count())
                    ->get()
            );
        }

        $url = $product->url();
        $facets = collect([
            Facet::brandCapacity($product->brand, (float) $product->hp),
            Facet::capacity((float) $product->hp),
            Facet::brand($product->brand),
            Facet::type($product->type),
        ]);

        $seo->title($this->productTitle($product))
            ->description($this->productDescription($product))
            ->canonical($url)
            ->ogType('product')
            ->breadcrumbs([
                ['name' => 'المتجر', 'url' => route('store.index')],
                ['name' => 'تكييف '.$product->brand->name_ar, 'url' => Facet::brand($product->brand)->url()],
                ['name' => $product->descriptiveName(false), 'url' => $url],
            ])
            ->dates($product->published_at, $product->lastModified())
            ->addNode(app(ProductSchema::class)->node($product, $url, $reviews = Review::shownFor($product)))
            ->fromModel($product);

        if ($image = $product->primaryImage()) {
            $seo->ogImage($image->getFullUrl('large'), $product->descriptiveName());
        }

        return view('store.product', ['product' => $product, 'related' => $related, 'facets' => $facets, 'reviews' => $reviews]);
    }

    private function facet(Request $request, Seo $seo, Facet $facet): View|RedirectResponse
    {
        $filters = ProductFilters::fromRequest($request);
        $liveCount = $facet->products()->count();

        abort_if($liveCount === 0 && ! Auth::check(), 404);

        $products = $this->paginate($filters->apply($facet->products()), $request);
        $page = $facet->page();
        $h1 = $page?->h1 ?: $facet->label();
        $priceRows = $facet->products()->with('brand')->orderBy('hp')->orderByRaw('COALESCE(sale_price, price)')->limit(60)->get();
        $min = $priceRows->min(fn (Product $p) => $p->currentPrice());

        $breadcrumbs = [['name' => 'المتجر', 'url' => route('store.index')]];
        if ($facet->kind === 'brand_capacity' && $facet->brand) {
            $breadcrumbs[] = ['name' => 'تكييف '.$facet->brand->name_ar, 'url' => Facet::brand($facet->brand)->url()];
        }
        $breadcrumbs[] = ['name' => $h1, 'url' => $facet->url()];

        $seo->title($facet->title())
            ->description(TitleBuilder::limit(
                $facet->label().': '.$liveCount.' موديل'.($min ? ' تبدأ أسعارها من '.number_format($min).' جنيه' : '')
                .' مع جدول أسعار محدّث. اطلب أونلاين أو اتصل 01055207525.',
                160,
            ))
            ->canonical($facet->url())
            ->page($products->currentPage())
            ->pageType('CollectionPage')
            ->breadcrumbs($breadcrumbs)
            ->ogKicker('المتجر')
            ->dates($page?->published_at, $facet->lastPriceChange() ?? $page?->lastModified());

        if ($page !== null) {
            $seo->fromModel($page);
        }

        // Curated facet rule: indexable only with a published unique intro and ≥ 3 live products.
        if (! $facet->isIndexable($liveCount)) {
            $seo->noindex();
        }

        return view('store.facet', [
            'facet' => $facet,
            'page' => $page,
            'h1' => $h1,
            'products' => $products,
            'filters' => $filters,
            'priceRows' => $priceRows,
            'updated' => $facet->lastPriceChange(),
            'formAction' => $facet->url(),
            'facetLinks' => $this->facetLinks(),
        ] + $this->filterOptions($request, $filters));
    }

    /**
     * @param  Builder<Product>  $query
     * @return LengthAwarePaginator<int, Product>
     */
    private function paginate($query, Request $request): LengthAwarePaginator
    {
        $products = $query->with(['brand', 'media'])->paginate(self::PER_PAGE)->withQueryString();

        abort_if($products->currentPage() > max(1, $products->lastPage()), 404);

        return $products;
    }

    /**
     * @return array{brandOptions: array<string, string>, chips: array<int, array{label: string, url: string}>}
     */
    private function filterOptions(Request $request, ProductFilters $filters): array
    {
        $brandOptions = Brand::query()->published()->whereHas('products', fn ($q) => $q->live())->orderBy('sort')->pluck('name_ar', 'slug')->all();

        return ['brandOptions' => $brandOptions, 'chips' => $filters->chips($request, $brandOptions)];
    }

    /**
     * Links to curated facets that have live products (descriptive Arabic anchors).
     *
     * @return array{brands: array<int, array{label: string, url: string}>, capacities: array<int, array{label: string, url: string}>, types: array<int, array{label: string, url: string}>}
     */
    private function facetLinks(): array
    {
        $live = Product::query()->live();

        return [
            'brands' => Brand::query()->published()->whereHas('products', fn ($q) => $q->live())->orderBy('sort')->get()
                ->map(fn (Brand $b) => ['label' => 'تكييف '.$b->name_ar, 'url' => Facet::brand($b)->url()])->all(),
            'capacities' => (clone $live)->distinct()->orderBy('hp')->pluck('hp')
                ->map(fn ($hp) => ['label' => 'تكييف '.Catalog::hpLabel($hp).' حصان', 'url' => Facet::capacity((float) $hp)->url()])->all(),
            'types' => (clone $live)->distinct()->pluck('type')
                ->filter(fn ($t) => isset(Catalog::TYPES[$t]))
                ->map(fn ($t) => ['label' => 'تكييف '.Catalog::TYPES[$t], 'url' => Facet::type($t)->url()])->values()->all(),
        ];
    }

    private function brandOr404(string $slug): Brand
    {
        $brand = Brand::query()->where('slug', $slug)->first();

        if ($brand === null && ($moved = Brand::findBySlugHistory($slug))) {
            abort(redirect()->to(Facet::brand($moved)->url(), 301));
        }

        abort_if($brand === null || (! $brand->isPublished() && ! Auth::check()), 404);

        return $brand;
    }

    private function hpOr404(string $slug): float
    {
        return Catalog::hpFromSlug($slug) ?? abort(404);
    }

    private function closestFacetUrl(Product $product): string
    {
        foreach ([Facet::brandCapacity($product->brand, (float) $product->hp), Facet::capacity((float) $product->hp), Facet::brand($product->brand)] as $facet) {
            $count = $facet->products()->count();
            if ($count > 0 && ($facet->isIndexable($count) || $facet->kind === 'brand')) {
                return $facet->url();
            }
        }

        return route('store.index');
    }

    private function productTitle(Product $product): string
    {
        $full = $product->descriptiveName();

        return mb_strlen(TitleBuilder::title($full)) <= (int) config('site.seo.title_max') || mb_strlen($full) <= (int) config('site.seo.title_max')
            ? $full
            : $product->descriptiveName(false);
    }

    private function productDescription(Product $product): string
    {
        $parts = [$product->descriptiveName().'، نوع '.(Catalog::TYPES[$product->type] ?? $product->type).'، بسعر '.number_format($product->currentPrice()).' جنيه'];
        if ($product->warranty_months) {
            $parts[] = 'ضمان '.$product->warranty_months.' شهر';
        }
        $parts[] = $product->canBeOrdered() ? 'اطلبه أونلاين أو اتصل 01055207525.' : 'غير متوفر حاليًا، شوف البدائل أو اتصل 01055207525.';

        return TitleBuilder::limit(implode('، ', array_slice($parts, 0, -1)).'. '.last($parts), 160);
    }
}
