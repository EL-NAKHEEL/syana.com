<?php

namespace Database\Seeders;

use App\Models\Page;
use App\Models\PriceGuide;
use App\Models\Service;
use Illuminate\Database\Seeder;

/**
 * Migrated, rewritten content from the old site. Always UNPUBLISHED; the owner reviews and publishes.
 * Idempotent and non-destructive: existing pages (possibly edited in the admin) are never overwritten.
 */
class LegacyContentSeeder extends Seeder
{
    public function run(): void
    {
        /** @var array<string, array<string, mixed>> $pages */
        $pages = require database_path('content/pages.php');

        foreach ($pages as $slug => $content) {
            if (Page::query()->where('slug', $slug)->exists()) {
                continue;
            }

            $page = Page::query()->create([
                'slug' => $slug,
                'template' => $content['template'],
                'title' => $content['title'],
                'intro' => $content['intro'] ?? null,
                'body' => $content['body'] ?? null,
                'data' => $content['data'] ?? null,
                'is_published' => false,
            ]);

            foreach ($content['faqs'] ?? [] as $i => $faq) {
                $page->faqs()->create([...$faq, 'sort' => $i]);
            }
        }

        /** @var array<int, array<string, mixed>> $services */
        $services = require database_path('content/services.php');

        foreach ($services as $content) {
            if (Service::query()->where('slug', $content['slug'])->exists()) {
                continue;
            }

            $faqs = $content['faqs'] ?? [];
            unset($content['faqs']);

            $service = Service::query()->create([...$content, 'is_published' => false]);

            foreach ($faqs as $i => $faq) {
                $service->faqs()->create([...$faq, 'sort' => $i]);
            }
        }

        /** @var array<int, array<string, mixed>> $guides */
        $guides = require database_path('content/price-guides.php');

        foreach ($guides as $content) {
            if (PriceGuide::query()->where('slug', $content['slug'])->exists()) {
                continue;
            }

            $slugs = $content['services'];
            unset($content['services']);

            $guide = PriceGuide::query()->create([...$content, 'is_published' => false]);
            $ids = Service::query()->whereIn('slug', $slugs)->pluck('id', 'slug');
            $pivot = [];
            foreach (array_values($slugs) as $i => $slug) {
                if ($ids->has($slug)) {
                    $pivot[$ids[$slug]] = ['sort' => $i];
                }
            }
            $guide->services()->sync($pivot);
        }
    }
}
