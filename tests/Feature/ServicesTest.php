<?php

use App\Models\Service;
use App\Models\User;
use App\Seo\Sitemap\SitemapGenerator;
use App\Settings\BusinessSettings;
use Database\Seeders\LegacyContentSeeder;

beforeEach(function () {
    publishCorePages();
});

it('renders a published service with its Service schema, breadcrumbs and booking form', function () {
    [$service] = publishServices(1);
    $service->update(['starting_price' => 350]);

    $response = $this->get('/services/service-1')->assertOk();
    $doc = html((string) $response->getContent());
    $graph = collect(json_decode($doc->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph']);
    $node = $graph->firstWhere('@type', 'Service');

    expect($doc->querySelector('h1')->textContent)->toBe('خدمة تكييف رقم 1')
        ->and($doc->querySelector('form[action$="/bookings"]'))->not->toBeNull()
        ->and($doc->querySelector('nav.breadcrumbs'))->not->toBeNull()
        ->and($node['@id'])->toBe(url('/services/service-1').'#service')
        ->and($node['provider'])->toBe(['@id' => url('/').'/#organization'])
        ->and($node['offers']['price'])->toBe('350.00')
        ->and($node['offers']['priceCurrency'])->toBe('EGP')
        ->and($graph->firstWhere('@type', 'BreadcrumbList')['itemListElement'][1]['item'])->toBe(url('/services'));
});

it('hides drafts from visitors and previews them for staff', function () {
    Service::factory()->create(['slug' => 'draft-service']);

    $this->get('/services/draft-service')->assertNotFound();
    $this->actingAs(User::factory()->create())->get('/services/draft-service')->assertOk()->assertSee('noindex, follow', false);
});

it('redirects an old slug to the renamed service', function () {
    [$service] = publishServices(1);
    $service->update(['slug' => 'ac-maintenance']);

    $this->get('/services/service-1')->assertStatus(301)->assertRedirect(url('/services/ac-maintenance'));
});

it('shows the emergency service only when the business is really 24/7', function () {
    Service::factory()->published()->create(['slug' => 'emergency-ac-repair', 'requires_24_7' => true]);

    $this->get('/services/emergency-ac-repair')->assertNotFound();

    $settings = app(BusinessSettings::class);
    $settings->is_24_7 = true;
    $settings->save();

    $this->get('/services/emergency-ac-repair')->assertOk();
});

it('lists live services in the hub, the menu and the sitemap', function () {
    $this->get('/services')->assertNotFound();

    publishServices(2);

    $this->get('/services')->assertOk()->assertSee('خدمة رقم 1')->assertSee('خدمة رقم 2');
    $this->get('/')->assertSee('href="'.route('services.index').'"', false);

    expect(app(SitemapGenerator::class)->allUrls())
        ->toContain(url('/services'), url('/services/service-1'), url('/services/service-2'));
});

it('seeds the eight migrated service drafts unpublished, matching the old URLs', function () {
    $this->seed(LegacyContentSeeder::class);

    expect(Service::query()->count())->toBe(8)
        ->and(Service::query()->published()->count())->toBe(0)
        ->and(Service::query()->pluck('slug')->all())->toContain('ac-maintenance', 'ac-installation', 'ac-preparation');

    $this->get('/syana.html')->assertRedirect(url('/services/ac-maintenance'));
    $this->get('/tarkeeb.html')->assertRedirect(url('/services/ac-installation'));
    $this->get('/tagheez.html')->assertRedirect(url('/services/ac-preparation'));
});
