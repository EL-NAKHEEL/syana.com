<?php

use App\Http\Middleware\CanonicalizeRequest;
use App\Models\NotFoundLog;
use App\Models\Redirect;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

it('maps every old site URL to its new home with a single 301', function (string $old, string $new) {
    $this->get($old)->assertStatus(301)->assertRedirect(url($new));
})->with([
    ['/index.html', '/'],
    ['/about.html', '/about'],
    ['/service.html', '/services'],
    ['/syana.html', '/services/ac-maintenance'],
    ['/tarkeeb.html', '/services/ac-installation'],
    ['/tagheez.html', '/services/ac-preparation'],
    ['/contact.html', '/contact'],
    ['/feature.html', '/about'],
    ['/testimonial.html', '/reviews'],
    ['/tarkeeb.html.html', '/services/ac-installation'],
    ['/tagheez.html.html.html', '/services/ac-preparation'],
]);

it('covers every HTML file of the old site in the redirect map', function () {
    $files = collect(glob(base_path('legacy/old-site/*.html')))->map(fn ($f) => '/'.basename($f));

    foreach ($files as $path) {
        expect(Redirect::lookup($path))->not->toBeNull("Old page {$path} has no redirect");
    }
});

it('returns 410 for the exact old stock image paths only', function () {
    $this->get('/img/carousel-1.jpg')->assertStatus(410);
    $this->get('/img/2306.q891.030.S.m004.c10.air%20conditioner%20split%20system%20realistic.jpg')->assertStatus(410);

    // No wildcard: any other /img/ path on the new site is untouched (404 here, served normally in real use).
    $this->get('/img/new-product-photo.jpg')->assertStatus(404);
});

it('counts redirect hits', function () {
    $this->get('/syana.html');
    $this->get('/syana.html');

    expect(Redirect::query()->where('from_path', '/syana.html')->value('hits'))->toBe(2);
});

// The test client trims slashes before requests reach the app, so URL-shape cases hit the middleware directly.
function canonicalize(string $url): Response
{
    return app(CanonicalizeRequest::class)->handle(Request::create($url), fn () => response('ok'));
}

it('lowercases paths, strips trailing slashes and drops ?page=1 in one hop', function (string $from, ?string $to) {
    $response = canonicalize($from);

    if ($to === null) {
        expect($response->getStatusCode())->toBe(200);
    } else {
        expect($response->getStatusCode())->toBe(301)->and($response->headers->get('Location'))->toBe($to);
    }
})->with([
    ['http://localhost/About/', 'http://localhost/about'],
    ['http://localhost/about//', 'http://localhost/about'],
    ['http://localhost//about', 'http://localhost/about'],
    ['http://localhost/?page=1', 'http://localhost'],
    ['http://localhost/about?page=abc&utm_source=x', 'http://localhost/about?utm_source=x'],
    ['http://localhost/about?page=2', null],
    ['http://localhost/about', null],
    ['http://localhost/storage/og/ABC.jpg', null],
]);

it('keeps POST requests untouched', function () {
    $response = app(CanonicalizeRequest::class)->handle(Request::create('http://localhost/About/', 'POST'), fn () => response('ok'));

    expect($response->getStatusCode())->toBe(200);
});

it('forces the canonical https non-www host from APP_URL', function () {
    config(['site.force_canonical_host' => true, 'app.url' => 'https://example.com']);

    $response = canonicalize('http://www.example.com/About/?x=1');

    expect($response->getStatusCode())->toBe(301)
        ->and($response->headers->get('Location'))->toBe('https://example.com/about?x=1')
        ->and(canonicalize('https://example.com/about')->getStatusCode())->toBe(200);
});

it('uses admin redirects and resolves them relative to APP_URL', function () {
    $redirect = Redirect::query()->create(['from_path' => '/Old-Offer/', 'to_url' => '/contact', 'status_code' => 301]);

    expect($redirect->fresh()->from_path)->toBe('/old-offer');
    $this->get('/old-offer')->assertStatus(301)->assertRedirect(url('/contact'));
});

it('returns a real 404 and logs it for redirect suggestions', function () {
    $this->get('/does-not-exist', ['User-Agent' => 'Mozilla/5.0', 'Referer' => 'https://google.com/'])->assertNotFound();
    $this->get('/does-not-exist', ['User-Agent' => 'Googlebot/2.1'])->assertNotFound();

    $log = NotFoundLog::query()->firstWhere('path', '/does-not-exist');
    expect($log->hits)->toBe(2)->and($log->last_user_agent)->toContain('Googlebot');
});

it('renders the 404 page as noindex without a canonical', function () {
    $doc = html((string) $this->get('/missing-page')->getContent());

    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($doc->querySelector('link[rel="canonical"]'))->toBeNull()
        ->and($doc->querySelectorAll('h1')->length)->toBe(1);
});

it('generates a GitHub Pages stub for every old HTML page pointing at the production URL', function () {
    config(['app.url' => 'https://example-ac.com']);
    $dir = 'storage/framework/testing/gh-stubs';
    File::deleteDirectory(base_path($dir));

    $this->artisan('app:github-pages-stubs', ['--output' => $dir])->assertSuccessful();

    $about = (string) file_get_contents(base_path($dir.'/about.html'));
    $files = collect(File::files(base_path($dir)))->map->getFilename();

    expect($about)->toContain('<link rel="canonical" href="https://example-ac.com/about">')
        ->toContain('content="0; url=https://example-ac.com/about"')
        ->and($files)->toContain('index.html', 'syana.html', 'tarkeeb.html', '404.html')
        ->and((string) file_get_contents(base_path($dir.'/404.html')))->toContain('noindex');

    File::deleteDirectory(base_path($dir));
});

it('refuses to generate stubs without a production https URL', function () {
    config(['app.url' => 'http://localhost']);

    $this->artisan('app:github-pages-stubs', ['--output' => 'storage/framework/testing/gh-stubs'])->assertFailed();
});
