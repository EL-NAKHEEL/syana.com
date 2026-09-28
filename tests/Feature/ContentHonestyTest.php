<?php

use App\Models\Page;
use App\Models\User;
use App\Settings\BusinessSettings;
use Database\Seeders\LegacyContentSeeder;
use Illuminate\Validation\ValidationException;

it('seeds the migrated drafts unpublished and never overwrites edits', function () {
    $this->seed(LegacyContentSeeder::class);

    expect(Page::query()->count())->toBe(7)
        ->and(Page::query()->published()->count())->toBe(0);

    Page::query()->where('slug', 'about')->first()->update(['title' => 'عنوان عدّله صاحب الموقع']);
    $this->seed(LegacyContentSeeder::class);

    expect(Page::query()->count())->toBe(7)
        ->and(Page::query()->where('slug', 'about')->value('title'))->toBe('عنوان عدّله صاحب الموقع');
});

it('refuses to publish a page that still has [TODO] markers', function () {
    $this->seed(LegacyContentSeeder::class);

    Page::query()->where('slug', 'about')->first()->update(['is_published' => true]);
})->throws(ValidationException::class);

it('keeps the migrated drafts free of the old template filler and broken contact formats', function () {
    $this->seed(LegacyContentSeeder::class);
    $this->actingAs(User::factory()->create());

    foreach (['/', '/about', '/contact'] as $path) {
        $html = (string) $this->get($path)->assertOk()->getContent();

        expect($html)
            ->not->toContain('+2001')
            ->not->toContain('tel:+0201')
            ->not->toContain('tel:%20')
            ->not->toContain('wa.me/+')
            ->not->toContain('01207720574')
            ->not->toContain('hanymahmoud')
            ->not->toContain('htmlcodex')
            ->not->toMatch('/\b(9856|1839|957|135)\b/')
            ->not->toContain('أحمد علي')
            ->not->toContain('منى سعيد')
            ->not->toContain('سامي حسين')
            ->toContain('tel:+201055207525')
            ->toContain('https://wa.me/201055207525');
    }
});

it('shows the email and schema facts only once they are confirmed', function () {
    publishCorePages();
    $settings = app(BusinessSettings::class);

    expect((string) $this->get('/contact')->getContent())->not->toContain('elnakheel55@gmail.com');

    $settings->email_confirmed = true;
    $settings->is_24_7 = true;
    $settings->has_public_address = true;
    $settings->street_address = 'شارع التسعين';
    $settings->locality = 'التجمع الخامس';
    $settings->save();

    $html = (string) $this->get('/contact')->getContent();
    $graph = json_decode(html($html)->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph'];

    expect($html)->toContain('elnakheel55@gmail.com')
        ->and($graph[0]['email'])->toBe('elnakheel55@gmail.com')
        ->and($graph[0]['address']['addressLocality'])->toBe('التجمع الخامس')
        ->and($graph[0]['openingHoursSpecification'][0]['opens'])->toBe('00:00');
});
