<?php

use App\Models\FacetPage;
use App\Models\Product;
use App\Seo\Sitemap\SitemapGenerator;
use App\Settings\CalculatorSettings;
use Illuminate\Support\Collection;

beforeEach(function () {
    publishCorePages();
});

function graphOf(string $html): Collection
{
    return collect(json_decode(html($html)->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph']);
}

it('lists live products and noindexes filtered or sorted views without a canonical', function () {
    publishCatalog();

    $doc = html((string) $this->get('/store')->assertOk()->getContent());
    expect($doc->querySelectorAll('.product-card'))->toHaveCount(3)
        ->and($doc->querySelector('link[rel="canonical"]')->getAttribute('href'))->toBe(url('/store'));

    $filtered = html((string) $this->get('/store?sort=price-desc&hp[]=1-5-hp')->assertOk()->getContent());
    expect($filtered->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($filtered->querySelector('link[rel="canonical"]'))->toBeNull()
        ->and($filtered->querySelector('form[data-filter-form]')->getAttribute('method'))->toBe('get');
});

it('keeps pagination indexable and self-canonical with the page number in the title', function () {
    publishCatalog(26);

    $doc = html((string) $this->get('/store?page=2')->assertOk()->getContent());
    expect($doc->querySelector('link[rel="canonical"]')->getAttribute('href'))->toBe(url('/store').'?page=2')
        ->and($doc->querySelector('title')->textContent)->toContain('صفحة 2')
        ->and($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('index, follow');

    $this->get('/store?page=9')->assertNotFound();
});

it('indexes a curated facet only with a published intro and three live products', function () {
    ['products' => $products] = publishCatalog(3);

    $doc = html((string) $this->get('/store/brand/sharp')->assertOk()->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('index, follow')
        ->and($doc->querySelector('h1')->textContent)->toBe('تكييف شارب')
        ->and($doc->querySelector('.price-table')->textContent)->toContain('21,000')
        ->and(app(SitemapGenerator::class)->allUrls())->toContain(url('/store/brand/sharp'), url('/store/capacity/1-5-hp'));

    $products[0]->update(['is_published' => false]);
    $doc = html((string) $this->get('/store/brand/sharp')->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and(app(SitemapGenerator::class)->allUrls())->not->toContain(url('/store/brand/sharp'));
});

it('keeps facets without an intro noindex and 404s empty facets', function () {
    publishCatalog();
    FacetPage::query()->delete();

    $doc = html((string) $this->get('/store/type/split')->assertOk()->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow');

    $this->get('/store/type/cassette')->assertNotFound();
    $this->get('/store/capacity/7-hp')->assertNotFound();
    $this->get('/store/brand/unknown')->assertNotFound();
});

it('puts the year in facet titles only while prices are fresh', function () {
    publishCatalog();
    $year = (string) now()->year;

    expect(html((string) $this->get('/store/brand/sharp')->getContent())->querySelector('title')->textContent)
        ->toBe('تكييف شارب: الأسعار والموديلات '.$year.' | النخيل كوول');

    $this->travel(120)->days();
    expect(html((string) $this->get('/store/brand/sharp')->getContent())->querySelector('title')->textContent)
        ->toBe('تكييف شارب: الأسعار والموديلات | النخيل كوول');
});

it('renders the product page with complete Product and Offer markup', function () {
    ['products' => $products] = publishCatalog();
    $products[0]->update(['sale_price' => 19000, 'is_inverter' => true, 'cooling' => 'cool-heat']);

    $response = $this->get('/store/sharp-ah-1')->assertOk();
    $doc = html((string) $response->getContent());
    $product = graphOf((string) $response->getContent())->firstWhere('@type', 'Product');

    expect($doc->querySelector('h1')->textContent)->toBe('تكييف شارب 1.5 حصان بارد ساخن إنفرتر AH-A11')
        ->and($product['@id'])->toBe(url('/store/sharp-ah-1').'#product')
        ->and($product['brand']['name'])->toBe('Sharp')
        ->and($product['mpn'])->toBe('AH-A11')
        ->and($product['offers']['price'])->toBe('19000.00')
        ->and($product['offers']['priceCurrency'])->toBe('EGP')
        ->and($product['offers']['availability'])->toBe('https://schema.org/InStock')
        ->and($product['offers']['priceSpecification'][1]['priceType'])->toBe('https://schema.org/StrikethroughPrice')
        ->and($product['offers']['priceSpecification'][1]['price'])->toBe('21000.00')
        ->and($product)->not->toHaveKey('aggregateRating')
        ->and(collect($product['additionalProperty'])->pluck('name'))->toContain('القدرة', 'إنفرتر');
});

it('keeps out-of-stock products live with alternatives and 301s discontinued ones to a facet', function () {
    ['products' => $products] = publishCatalog();
    $products[0]->update(['stock_status' => 'out_of_stock']);

    $response = $this->get('/store/sharp-ah-1')->assertOk()->assertSee('غير متوفر حاليًا')->assertSee('بدائل متوفرة');
    expect(graphOf((string) $response->getContent())->firstWhere('@type', 'Product')['offers']['availability'])->toBe('https://schema.org/OutOfStock');

    $products[1]->update(['stock_status' => 'discontinued']);
    $this->get('/store/sharp-ah-2')->assertStatus(301)->assertRedirect(url('/store/brand/sharp'));
});

it('logs real price changes only', function () {
    ['products' => $products] = publishCatalog(1);
    $product = $products[0];
    $changed = $product->fresh()->price_changed_at;

    $this->travel(2)->days();
    $product->update(['short_description' => 'وصف جديد']);
    expect($product->fresh()->price_changed_at->equalTo($changed))->toBeTrue()
        ->and($product->priceChanges()->count())->toBe(1);

    $product->update(['price' => 30000]);
    expect($product->priceChanges()->count())->toBe(2)
        ->and($product->fresh()->price_changed_at->greaterThan($changed))->toBeTrue();
});

it('searches with Arabic normalization and keeps results noindex', function () {
    ['products' => $products] = publishCatalog();
    $products[0]->update(['is_inverter' => true]);

    $doc = html((string) $this->get('/search?q='.urlencode('تكييف انفرتر'))->assertOk()->getContent());

    expect($doc->querySelectorAll('.product-card'))->toHaveCount(1)
        ->and($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow');
});

it('shows the HP calculator only once its coefficients are confirmed', function () {
    publishCatalog();
    $this->get('/store')->assertDontSee('تكييف كام حصان لأوضتك؟');

    $settings = app(CalculatorSettings::class);
    $settings->confirmed = true;
    $settings->save();

    $this->get('/store?room=12')->assertSee('تكييف كام حصان لأوضتك؟')->assertSee(url('/store/capacity/1-5-hp'), false);
});

it('links the store from the menu once products are live', function () {
    expect((string) $this->get('/')->getContent())->not->toContain('href="'.url('/store').'"');

    publishCatalog(1);
    expect((string) $this->get('/')->getContent())->toContain('href="'.url('/store').'"');
});

it('never lists unpublished brands or products', function () {
    ['brand' => $brand] = publishCatalog();
    $brand->update(['is_published' => false]);

    $this->get('/store/sharp-ah-1')->assertNotFound();
    expect(Product::query()->live()->count())->toBe(0);
});
