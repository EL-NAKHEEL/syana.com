<?php

namespace Tests\Support;

use Dom\HTMLDocument;
use Illuminate\Testing\TestResponse;

/**
 * Crawls every internal link reachable from "/" through the HTTP kernel (no real network).
 */
class SiteCrawler
{
    /** @var array<string, TestResponse> */
    public array $pages = [];

    /** @var array<string, array<int, string>> link → pages that contain it */
    public array $linkedFrom = [];

    /**
     * @param  \Closure(string): TestResponse  $get
     */
    public function __construct(private readonly \Closure $get) {}

    /**
     * @return array<string, TestResponse>
     */
    public function crawl(string $start = '/'): array
    {
        $queue = [url($start)];

        while ($queue !== []) {
            $url = array_shift($queue);
            if (isset($this->pages[$url])) {
                continue;
            }

            $response = ($this->get)($url);
            $this->pages[$url] = $response;

            if (! str_contains((string) $response->headers->get('Content-Type'), 'text/html')) {
                continue;
            }

            foreach ($this->internalLinks((string) $response->getContent()) as $link) {
                $this->linkedFrom[$link][] = $url;
                if (! isset($this->pages[$link])) {
                    $queue[] = $link;
                }
            }
        }

        return $this->pages;
    }

    /**
     * @return array<int, string>
     */
    public function internalLinks(string $html): array
    {
        $doc = HTMLDocument::createFromString($html, LIBXML_NOERROR);
        $base = rtrim(url('/'), '/');
        $links = [];

        foreach ($doc->querySelectorAll('a[href]') as $a) {
            $href = (string) $a->getAttribute('href');
            if ($href === '' || str_starts_with($href, '#')) {
                continue;
            }
            $absolute = str_starts_with($href, '/') ? $base.$href : $href;
            if (! str_starts_with($absolute, $base)) {
                continue;
            }
            $links[] = strtok($absolute, '#') ?: $absolute;
        }

        return array_values(array_unique($links));
    }
}
