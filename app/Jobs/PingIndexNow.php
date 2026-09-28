<?php

namespace App\Jobs;

use App\Settings\SeoSettings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;

/**
 * Tells IndexNow engines (Bing, Yandex…) that a URL was published, updated or removed.
 */
class PingIndexNow implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly string $url) {}

    public function handle(SeoSettings $settings): void
    {
        $key = $settings->indexnow_key;

        if (! $key) {
            return;
        }

        Http::timeout(10)->post((string) config('site.indexnow.endpoint'), [
            'host' => parse_url((string) config('app.url'), PHP_URL_HOST),
            'key' => $key,
            'keyLocation' => route('indexnow.key', $key),
            'urlList' => [$this->url],
        ])->throw();
    }
}
