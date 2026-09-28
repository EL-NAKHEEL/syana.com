<?php

use App\Models\Page;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(function () {
    config(['responsecache.enabled' => true, 'responsecache.cache.store' => 'array']);
    publishCorePages();
});

it('caches plain guest pages and clears the cache when content changes', function () {
    $this->get('/about')->assertOk();
    expect(ResponseCache::hasBeenCached(Request::create(url('/about'))))->toBeTrue();

    Page::query()->where('slug', 'about')->first()->update(['intro' => 'مقدمة محدّثة']);

    expect(ResponseCache::hasBeenCached(Request::create(url('/about'))))->toBeFalse();
    $this->get('/about')->assertSee('مقدمة محدّثة');
});

it('never caches filtered URLs or signed-in users', function () {
    $this->get('/?sort=price')->assertOk();
    expect(ResponseCache::hasBeenCached(Request::create(url('/?sort=price'))))->toBeFalse();

    $this->actingAs(User::factory()->create())->get('/contact')->assertOk();
    expect(ResponseCache::hasBeenCached(Request::create(url('/contact'))))->toBeFalse();
});

it('never caches a page that shows a one-time flash message', function () {
    $this->post('/reviews', ['name' => 'محمد', 'rating' => 5, 'body' => 'الفني جه في الميعاد والتركيب كان نضيف جدًا.'])->assertRedirect();
    $this->get('/reviews')->assertSee('رأيك وصلنا');

    expect(ResponseCache::hasBeenCached(Request::create(url('/reviews'))))->toBeFalse();
});

it('refreshes cached pages when a scheduled publish time or a sale end passes', function () {
    $this->freezeTime();
    ['products' => $products] = publishCatalog(1);
    $products[0]->update(['sale_price' => 19000, 'sale_ends_at' => now()->addMinutes(3)]);
    $this->artisan('app:refresh-scheduled-content')->assertSuccessful();

    $this->get('/store/sharp-ah-1')->assertOk();
    expect(ResponseCache::hasBeenCached(Request::create(url('/store/sharp-ah-1'))))->toBeTrue();

    $this->travel(5)->minutes();
    $this->artisan('app:refresh-scheduled-content')->assertSuccessful();

    expect(ResponseCache::hasBeenCached(Request::create(url('/store/sharp-ah-1'))))->toBeFalse();
});

it('leaves the cache alone when nothing scheduled happened', function () {
    $this->artisan('app:refresh-scheduled-content')->assertSuccessful();
    $this->get('/about')->assertOk();

    $this->travel(10)->minutes();
    $this->artisan('app:refresh-scheduled-content')->assertSuccessful();

    expect(ResponseCache::hasBeenCached(Request::create(url('/about'))))->toBeTrue();
});
