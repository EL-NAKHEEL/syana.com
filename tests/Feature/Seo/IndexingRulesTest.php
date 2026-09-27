<?php

use App\Models\Page;
use App\Models\User;

beforeEach(function () {
    publishCorePages();
});

it('canonicalizes tracking parameters to the clean URL', function () {
    $doc = html((string) $this->get('/about?utm_source=whatsapp&gclid=abc&fbclid=xyz')->getContent());

    expect($doc->querySelector('link[rel="canonical"]')->getAttribute('href'))->toBe(route('about'))
        ->and($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('index, follow');
});

it('noindexes filtered or sorted URLs and drops the canonical', function () {
    $doc = html((string) $this->get('/?sort=price&brand=sharp')->getContent());

    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($doc->querySelector('link[rel="canonical"]'))->toBeNull();
});

it('hides unpublished pages from visitors with a 404', function () {
    Page::query()->where('slug', 'about')->update(['is_published' => false]);

    $this->get('/about')->assertNotFound();
});

it('lets signed-in staff preview drafts as noindex with a draft banner', function () {
    Page::query()->where('slug', 'about')->update(['is_published' => false]);

    $response = $this->actingAs(User::factory()->create())->get('/about')->assertOk();
    $doc = html((string) $response->getContent());

    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($doc->querySelector('link[rel="canonical"]'))->toBeNull()
        ->and($doc->querySelector('.draft-banner'))->not->toBeNull();
});

it('drops unpublished pages from navigation and the sitemap', function () {
    Page::query()->where('slug', 'contact')->first()->update(['is_published' => false]);

    $home = (string) $this->get('/')->getContent();
    expect($home)->not->toContain('href="'.route('contact').'"');

    $this->get('/sitemap.xml')->assertOk();
    $this->get('/sitemaps/pages.xml')->assertOk()->assertDontSee(route('contact'), false);
});

it('respects a noindex override from the SEO panel', function () {
    Page::query()->where('slug', 'about')->first()->seoMeta()->create(['robots' => 'noindex, follow']);

    $doc = html((string) $this->get('/about')->getContent());
    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow');

    $this->get('/sitemaps/pages.xml')->assertDontSee(route('about'), false);
});

it('applies SEO panel title and description overrides', function () {
    Page::query()->where('slug', 'about')->first()->seoMeta()->create([
        'title' => 'قصة النخيل كوول وفريق الفنيين بتوعنا',
        'description' => str_repeat('وصف مخصص للصفحة ', 8),
    ]);

    $doc = html((string) $this->get('/about')->getContent());
    expect($doc->querySelector('title')->textContent)->toBe('قصة النخيل كوول وفريق الفنيين بتوعنا')
        ->and($doc->querySelector('meta[name="description"]')->getAttribute('content'))->toContain('وصف مخصص');
});

it('serves a sitemap index with real lastmod and no priority or changefreq', function () {
    $index = $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
    $index->assertSee(route('sitemap.child', ['name' => 'pages']), false);

    $child = (string) $this->get('/sitemaps/pages.xml')->assertOk()->getContent();
    expect($child)->toContain('<lastmod>')
        ->not->toContain('<priority>')
        ->not->toContain('<changefreq>')
        ->toContain('<loc>'.url('/').'</loc>');

    $this->get('/sitemaps/nope.xml')->assertNotFound();
});

it('updates lastmod only when visible content changes', function () {
    $page = Page::query()->where('slug', 'about')->first();
    $before = $page->content_modified_at;

    $this->travel(2)->days();
    $page->touch();
    expect($page->fresh()->content_modified_at->equalTo($before))->toBeTrue();

    $page->update(['intro' => 'مقدمة جديدة']);
    expect($page->fresh()->content_modified_at->greaterThan($before))->toBeTrue();
});
