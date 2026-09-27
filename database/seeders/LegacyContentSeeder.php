<?php

namespace Database\Seeders;

use App\Models\Page;
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
    }
}
