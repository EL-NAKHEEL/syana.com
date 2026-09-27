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
