<?php

namespace App\Http\Controllers;

use App\Seo\Sitemap\SitemapGenerator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;

class SitemapController extends Controller
{
    public function index(SitemapGenerator $generator): Response
    {
        if ($generator->path('index') === null) {
            $generator->generate();
        }

        return $this->xml((string) Storage::disk('local')->get(SitemapGenerator::DIRECTORY.'/index.xml'));
    }

    public function child(SitemapGenerator $generator, string $name): Response
    {
        if ($generator->path('index') === null) {
            $generator->generate();
        }

        $path = $generator->path($name);
        abort_if($path === null, 404);

        return $this->xml((string) Storage::disk('local')->get($path));
    }

    private function xml(string $content): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
