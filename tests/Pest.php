<?php

use App\Models\Area;
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

/**
 * Creates areas that pass the publish guard (local intro, response time, notes, services, FAQs, neighbors).
 *
 * @param  Collection<int, Service>|null  $services
 * @return Collection<int, Area>
 */
function publishAreas(int $count = 3, ?Collection $services = null): Collection
{
    $services ??= Service::query()->published()->get();
    if ($services->isEmpty()) {
        $services = publishServices(1);
    }

    $areas = collect(range(1, $count))->map(fn (int $i) => Area::factory()->create([
        'slug' => 'area-'.$i,
        'name_ar' => 'منطقة رقم '.$i,
        'local_intro' => trim(str_repeat('نص محلي حقيقي عن المنطقة رقم '.$i.' ', 30)),
        'response_time_note' => 'غالبًا في نفس اليوم',
        'local_notes' => 'ملاحظات عن المباني في المنطقة '.$i,
        'sort' => $i,
    ]));

    foreach ($areas as $i => $area) {
        $area->services()->sync($services->pluck('id'));
        $area->faqs()->createMany([
            ['question' => 'سؤال محلي أول عن '.$area->name_ar.'؟', 'answer' => 'إجابة.', 'sort' => 0],
            ['question' => 'سؤال محلي تاني عن '.$area->name_ar.'؟', 'answer' => 'إجابة.', 'sort' => 1],
        ]);
        $area->neighbors()->sync([$areas[($i + 1) % $count]->id]);
        $area->update(['is_published' => true, 'published_at' => now()->subDay()]);
    }

    return $areas->map->fresh();
}

function html(string $markup): HTMLDocument
{
    return HTMLDocument::createFromString($markup, LIBXML_NOERROR);
}
