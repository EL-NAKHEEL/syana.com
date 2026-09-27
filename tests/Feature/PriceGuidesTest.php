<?php

use App\Models\PriceGuide;
use App\Models\Service;
use App\Seo\Sitemap\SitemapGenerator;
use Database\Seeders\LegacyContentSeeder;

beforeEach(function () {
    publishCorePages();
});

function guideWith(iterable $services): PriceGuide
{
    $guide = PriceGuide::query()->create([
        'slug' => 'ac-installation-cost',
        'title' => 'تكلفة تركيب التكييف {year}: الأسعار بالتفصيل',
        'h1' => 'تكلفة تركيب التكييف {year}',
        'intro' => 'أسعار التركيب والتأسيس من جدول أسعارنا الحالي وإيه اللي بيأثر على التكلفة.',
        'is_published' => true,
    ]);
    $guide->services()->sync(collect($services)->pluck('id'));

    return $guide;
}

it('stays offline while no service in it has a real price', function () {
    guideWith(publishServices(2));

    $this->get('/prices/ac-installation-cost')->assertNotFound();
    $this->get('/prices')->assertNotFound();
});

it('renders live prices as text with the last real price change', function () {
    $services = publishServices(2);
    $this->travelTo(now()->addYear()->startOfYear()->addDays(10));
    $services[0]->update(['starting_price' => 450]);
    guideWith($services);

    $doc = html((string) $this->get('/prices/ac-installation-cost')->assertOk()->getContent());
    $year = (string) now()->year;

    expect($doc->querySelector('h1')->textContent)->toBe('تكلفة تركيب التكييف '.$year)
        ->and($doc->querySelector('title')->textContent)->toContain($year)
        ->and($doc->querySelector('.price-table')->textContent)->toContain('450')->toContain('السعر بعد المعاينة')
        ->and($doc->querySelector('time')->getAttribute('datetime'))->toBe(now()->toDateString());

    $page = collect(json_decode($doc->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph'])
        ->firstWhere('@id', url('/prices/ac-installation-cost').'#webpage');
    expect($page['dateModified'])->toStartWith(now()->toDateString())
        ->and(collect(json_decode($doc->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph'])->firstWhere('@type', 'Product'))->toBeNull();
});

it('drops the year from titles once prices are stale', function () {
    $services = publishServices(1);
    $services[0]->update(['starting_price' => 450]);
    guideWith($services);

    $this->travel(120)->days();
    $doc = html((string) $this->get('/prices/ac-installation-cost')->getContent());

    expect($doc->querySelector('h1')->textContent)->toBe('تكلفة تركيب التكييف');
});

it('moves «آخر تحديث» only when a price really changes', function () {
    $services = publishServices(1);
    $services[0]->update(['starting_price' => 450]);
    $changed = $services[0]->fresh()->price_changed_at;

    $this->travel(3)->days();
    $services[0]->update(['summary' => 'ملخص جديد مختلف تمامًا عن القديم لخدمة التكييف رقم واحد للاختبار.']);
    expect($services[0]->fresh()->price_changed_at->equalTo($changed))->toBeTrue();

    $services[0]->update(['starting_price' => 500]);
    expect($services[0]->fresh()->price_changed_at->greaterThan($changed))->toBeTrue();
});

it('links services to their guides and lists guides in the sitemap', function () {
    $services = publishServices(1);
    $services[0]->update(['starting_price' => 450]);
    guideWith($services);

    $this->get('/services/service-1')->assertSee(url('/prices/ac-installation-cost'), false);
    expect(app(SitemapGenerator::class)->allUrls())->toContain(url('/prices'), url('/prices/ac-installation-cost'));
    $this->get('/prices')->assertOk()->assertSee('تكلفة تركيب التكييف');
});

it('seeds the price guide drafts without any stored price', function () {
    $this->seed(LegacyContentSeeder::class);

    expect(PriceGuide::query()->count())->toBe(2)
        ->and(PriceGuide::query()->published()->count())->toBe(0)
        ->and(Service::query()->whereNotNull('starting_price')->count())->toBe(0);
});
