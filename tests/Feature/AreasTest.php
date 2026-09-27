<?php

use App\Models\Area;
use App\Seo\Sitemap\SitemapGenerator;

beforeEach(function () {
    publishCorePages();
});

it('keeps a published area offline until every local field is filled (doorway-page guard)', function () {
    $area = Area::factory()->create(['slug' => 'thin-area', 'local_intro' => 'مقدمة قصيرة', 'is_published' => true]);

    expect($area->guardFailures())->not->toBeEmpty();
    $this->get('/areas/thin-area')->assertNotFound();
    expect(app(SitemapGenerator::class)->allUrls())->not->toContain(url('/areas/thin-area'));
});

it('does not require local reviews (owner decision C1)', function () {
    [$area] = publishAreas(1);

    expect($area->guardFailures())->toBe([])->and($area->isLive())->toBeTrue();
});

it('renders a live area with its services, FAQs, neighbors, schema and a preselected booking form', function () {
    $areas = publishAreas(2);

    $doc = html((string) $this->get('/areas/area-1')->assertOk()->getContent());
    $graph = collect(json_decode($doc->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph']);

    expect($doc->querySelector('h1')->textContent)->toBe('صيانة وتركيب تكييفات في منطقة رقم 1')
        ->and($doc->querySelector('title')->textContent)->toBe('صيانة وتركيب تكييفات في منطقة رقم 1 | النخيل كوول')
        ->and($doc->querySelector('a[href="'.url('/areas/area-2').'"]'))->not->toBeNull()
        ->and($doc->querySelector('select[name="area_id"] option[selected]')->getAttribute('value'))->toBe((string) $areas[0]->id)
        ->and($graph->firstWhere('@type', 'Service')['areaServed']['name'])->toBe('منطقة رقم 1')
        ->and($graph->filter(fn ($n) => in_array('LocalBusiness', (array) $n['@type'], true)))->toBeEmpty();
});

it('lists live areas in the organization areaServed', function () {
    publishAreas(2);

    $doc = html((string) $this->get('/')->getContent());
    $org = json_decode($doc->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph'][0];

    expect(collect($org['areaServed'])->pluck('name')->all())->toBe(['منطقة رقم 1', 'منطقة رقم 2']);
});

it('keeps the areas hub noindex and out of the sitemap below three areas', function () {
    publishAreas(2);

    $doc = html((string) $this->get('/areas')->assertOk()->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and(app(SitemapGenerator::class)->allUrls())->not->toContain(url('/areas'))
        ->toContain(url('/areas/area-1'));
});

it('indexes the areas hub from three live areas', function () {
    publishAreas(3);

    $doc = html((string) $this->get('/areas')->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('index, follow')
        ->and(app(SitemapGenerator::class)->allUrls())->toContain(url('/areas'));
});

it('shows footer areas only when flagged', function () {
    [$area] = publishAreas(1);
    expect((string) $this->get('/')->getContent())->not->toContain('صيانة تكييفات منطقة رقم 1');

    $area->update(['show_in_footer' => true]);
    expect((string) $this->get('/')->getContent())->toContain('صيانة تكييفات منطقة رقم 1');
});
