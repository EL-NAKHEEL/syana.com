<?php

use App\Models\Page;
use App\Models\Service;
use Dom\HTMLDocument;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

pest()->extend(TestCase::class)
    ->use(RefreshDatabase::class)
    ->beforeEach(function () {
        Storage::fake('local');
        Storage::fake('public');
    })
    ->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Publishes the fixed-route pages with clean (TODO-free) fixture copy.
 *
 * @return array<string, Page>
 */
function publishCorePages(): array
{
    $pages = [];

    foreach (['home' => 'النخيل كوول', 'about' => 'عن النخيل كوول', 'contact' => 'تواصل معنا'] as $slug => $title) {
        $pages[$slug] = Page::factory()->published()->create([
            'slug' => $slug,
            'template' => $slug,
            'title' => $title,
            'intro' => 'مقدمة للاختبار.',
            'body' => '<h2>قسم</h2><p>محتوى للاختبار.</p>',
            'data' => $slug === 'home' ? ['services' => ['heading' => 'خدماتنا', 'items' => [['title' => 'صيانة', 'text' => 'نص']]]] : null,
        ]);
    }

    return $pages;
}

/**
 * @return Collection<int, Service>
 */
function publishServices(int $count = 3): Collection
{
    return collect(range(1, $count))->map(fn (int $i) => Service::factory()->published()->create([
        'slug' => 'service-'.$i,
        'name' => 'خدمة رقم '.$i,
        'h1' => 'خدمة تكييف رقم '.$i,
        'sort' => $i,
    ]));
}

function html(string $markup): HTMLDocument
{
    return HTMLDocument::createFromString($markup, LIBXML_NOERROR);
}
