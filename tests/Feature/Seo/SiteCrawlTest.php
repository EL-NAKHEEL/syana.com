<?php

use App\Seo\Sitemap\SitemapGenerator;
use Tests\Support\SiteCrawler;

/*
| Definition of done (brief §8): crawl every public route and check status, one H1, unique titles and
| descriptions within length, absolute canonicals, robots meta, valid JSON-LD and breadcrumbs.
*/

function crawlSite(object $test): SiteCrawler
{
    $crawler = new SiteCrawler(fn (string $url) => $test->get($url));
    $crawler->crawl('/');

    return $crawler;
}

beforeEach(function () {
    publishCorePages();
    publishServices();
});

it('reaches every published page with a 200 and no broken internal links', function () {
    $crawler = crawlSite($this);

    expect(array_keys($crawler->pages))->toContain(url('/'), route('about'), route('contact'));

    foreach ($crawler->pages as $url => $response) {
        expect($response->getStatusCode())->toBe(200, "Broken internal link {$url} (linked from ".implode(', ', $crawler->linkedFrom[$url] ?? []).')');
    }
});

it('renders RTL Arabic documents with exactly one H1 and no leftover TODO markers', function () {
    foreach (crawlSite($this)->pages as $url => $response) {
        $html = (string) $response->getContent();
        $doc = html($html);

        expect($doc->documentElement->getAttribute('lang'))->toBe('ar', $url)
            ->and($doc->documentElement->getAttribute('dir'))->toBe('rtl', $url)
            ->and($doc->querySelectorAll('h1')->length)->toBe(1, "{$url} must have exactly one <h1>")
            ->and($doc->querySelector('main'))->not->toBeNull()
            ->and($html)->not->toContain('[TODO')
            ->and($doc->querySelector('meta[name="keywords"]'))->toBeNull();

        foreach ($doc->querySelectorAll('img') as $img) {
            expect($img->hasAttribute('alt'))->toBeTrue("{$url}: every <img> needs an alt attribute");
        }
    }
});

it('gives every page a unique title and description within length limits', function () {
    $titles = $descriptions = [];

    foreach (crawlSite($this)->pages as $url => $response) {
        $doc = html((string) $response->getContent());
        $title = trim((string) $doc->querySelector('title')?->textContent);
        $description = (string) $doc->querySelector('meta[name="description"]')?->getAttribute('content');

        expect(mb_strlen($title))->toBeGreaterThanOrEqual(10)->toBeLessThanOrEqual(config('site.seo.title_hard_max'), "{$url} title: {$title}")
            ->and(mb_strlen($description))->toBeGreaterThanOrEqual(config('site.seo.description_hard_min'))
            ->toBeLessThanOrEqual(config('site.seo.description_hard_max'), "{$url} description: {$description}");

        $titles[$url] = $title;
        $descriptions[$url] = $description;
    }

    expect(array_unique($titles))->toHaveCount(count($titles), 'Duplicate <title> found')
        ->and(array_unique($descriptions))->toHaveCount(count($descriptions), 'Duplicate meta description found');
});

it('uses a self-referencing absolute canonical and index robots on indexable pages', function () {
    foreach (crawlSite($this)->pages as $url => $response) {
        $doc = html((string) $response->getContent());
        $robots = $doc->querySelector('meta[name="robots"]')?->getAttribute('content');
        $canonical = $doc->querySelector('link[rel="canonical"]')?->getAttribute('href');

        expect($robots)->toBe('index, follow', $url)
            ->and($canonical)->toBe($url)
            ->and($canonical)->toStartWith(config('app.url'));
    }
});

it('ships valid JSON-LD with the required nodes on every page', function () {
    foreach (crawlSite($this)->pages as $url => $response) {
        $doc = html((string) $response->getContent());
        $scripts = $doc->querySelectorAll('script[type="application/ld+json"]');
        expect($scripts->length)->toBe(1, $url);

        $data = json_decode((string) $scripts->item(0)->textContent, true, flags: JSON_THROW_ON_ERROR);
        expect($data['@context'])->toBe('https://schema.org');

        $nodes = collect($data['@graph'])->keyBy(fn (array $node) => is_array($node['@type']) ? implode(',', $node['@type']) : $node['@type']);

        $org = $nodes->get('HVACBusiness,Store');
        expect($org)->not->toBeNull()
            ->and($org['@id'])->toBe(url('/').'/#organization')
            ->and($org['name'])->toBe('النخيل كوول')
            ->and($org['alternateName'])->toBe(['شركة النخيل', 'Al Nakheel Cool'])
            ->and($org['telephone'])->toBe('+201055207525')
            ->and($org['url'])->toBe(url('/'))
            ->and($org['logo']['url'])->toStartWith('http')
            ->and($org)->not->toHaveKeys(['address', 'geo', 'aggregateRating', 'email']);

        $website = $nodes->get('WebSite');
        expect($website['@id'])->toBe(url('/').'/#website')->and($website['name'])->toBe('النخيل كوول');

        $page = collect($data['@graph'])->first(fn (array $n) => ($n['@id'] ?? null) === $url.'#webpage');
        expect($page)->not->toBeNull("{$url} is missing its WebPage node")
            ->and($page['url'])->toBe($url)
            ->and($page['isPartOf'])->toBe(['@id' => url('/').'/#website']);

        if ($url !== url('/')) {
            $breadcrumb = $nodes->get('BreadcrumbList');
            expect($breadcrumb)->not->toBeNull($url)
                ->and($breadcrumb['itemListElement'][0]['item'])->toBe(url('/'))
                ->and(last($breadcrumb['itemListElement'])['item'])->toBe($url);
        }
    }
});

it('shows visible breadcrumbs on every page except the home page', function () {
    foreach (crawlSite($this)->pages as $url => $response) {
        $nav = html((string) $response->getContent())->querySelector('nav.breadcrumbs');

        $url === url('/')
            ? expect($nav)->toBeNull()
            : expect($nav)->not->toBeNull($url)->and($nav->querySelector('[aria-current="page"]'))->not->toBeNull();
    }
});

it('lists exactly the indexable crawled pages in the sitemap', function () {
    $crawled = collect(crawlSite($this)->pages)
        ->filter(fn ($r) => str_contains((string) $r->getContent(), 'content="index, follow"'))
        ->keys()->sort()->values()->all();

    $inSitemap = collect(app(SitemapGenerator::class)->allUrls())->sort()->values()->all();

    expect($inSitemap)->toBe($crawled);
});

it('has Open Graph and Twitter tags with an absolute 1200x630 image', function () {
    foreach (crawlSite($this)->pages as $url => $response) {
        $doc = html((string) $response->getContent());
        $meta = fn (string $selector) => $doc->querySelector($selector)?->getAttribute('content');

        expect($meta('meta[property="og:locale"]'))->toBe('ar_EG')
            ->and($meta('meta[property="og:url"]'))->toBe($url)
            ->and($meta('meta[property="og:image"]'))->toStartWith('http')
            ->and($meta('meta[property="og:image:width"]'))->toBe('1200')
            ->and($meta('meta[name="twitter:card"]'))->toBe('summary_large_image');
    }
});
