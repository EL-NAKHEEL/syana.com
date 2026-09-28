<?php

namespace App\Http\Controllers;

use App\Settings\SeoSettings;
use Illuminate\Http\Response;

class IndexNowKeyController extends Controller
{
    public function __invoke(SeoSettings $settings, string $key): Response
    {
        abort_unless($settings->indexnow_key && hash_equals($settings->indexnow_key, $key), 404);

        return response($key, 200, ['Content-Type' => 'text/plain; charset=UTF-8', 'X-Robots-Tag' => 'noindex']);
    }
}
