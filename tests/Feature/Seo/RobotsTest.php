<?php

it('allows crawling in production except private areas and lists the sitemap', function () {
    app()->detectEnvironment(fn () => 'production');

    $body = (string) $this->get('/robots.txt')->assertOk()->assertHeader('Content-Type', 'text/plain; charset=UTF-8')->getContent();

    expect($body)->toContain("User-agent: *\nAllow: /")
        ->toContain('Disallow: /admin')
        ->toContain('Disallow: /cart')
        ->toContain('Disallow: /checkout')
        ->toContain('Disallow: /search')
        ->toContain('Sitemap: '.url('/sitemap.xml'))
        ->not->toContain('Disallow: /build')
        ->not->toContain('Disallow: /storage')
        ->not->toMatch('/User-agent: (Googlebot|Bingbot|OAI-SearchBot|PerplexityBot)/');
});

it('blocks everything outside production', function () {
    $body = (string) $this->get('/robots.txt')->getContent();

    expect(trim($body))->toBe("User-agent: *\nDisallow: /");
});

it('sends X-Robots-Tag noindex outside production only', function () {
    publishCorePages();

    $this->get('/')->assertHeader('X-Robots-Tag', 'noindex, nofollow');

    app()->detectEnvironment(fn () => 'production');
    $this->get('/')->assertHeaderMissing('X-Robots-Tag');
});
