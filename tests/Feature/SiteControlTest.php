<?php

use App\Filament\Pages\ManageLayout;
use App\Models\Page;
use App\Settings\LayoutSettings;
use App\Support\Navigation;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->pages = publishCorePages();
});

function saveLayout(array $changes): void
{
    $layout = app(LayoutSettings::class);
    foreach ($changes as $key => $value) {
        $layout->{$key} = $value;
    }
    $layout->save();
}

it('renders the header, menu, footer and floating buttons from the layout settings', function () {
    publishServices(1);
    saveLayout([
        'topbar_label' => 'كلّمنا:',
        'header_cta_label' => 'اطلب سعر دلوقتي',
        'footer_about' => 'نبذة كتبها صاحب الموقع.',
        'float_call' => false,
        'menu' => [
            ['key' => 'services', 'label' => 'الخدمات كلها', 'visible' => true],
            ['key' => 'home', 'label' => 'البداية', 'visible' => true],
            ['key' => 'about', 'label' => 'من نحن', 'visible' => false],
        ],
    ]);

    $doc = html((string) $this->get('/')->getContent());
    $menu = collect($doc->querySelectorAll('.navbar__nav ul a'))->map(fn ($a) => trim($a->textContent))->all();

    expect($doc->querySelector('.topbar__phone a')->textContent)->toContain('كلّمنا:')
        ->and($doc->querySelector('.navbar__cta')->textContent)->toContain('اطلب سعر دلوقتي')
        ->and(array_slice($menu, 0, 2))->toBe(['الخدمات كلها', 'البداية'])
        ->and($menu)->not->toContain('من نحن')
        ->toContain('تواصل معنا') // entries missing from the saved menu are still shown
        ->and($doc->querySelector('.float-btn--call'))->toBeNull()
        ->and($doc->querySelector('.float-btn--whatsapp'))->not->toBeNull()
        ->and($doc->querySelector('.footer')->textContent)->toContain('نبذة كتبها صاحب الموقع.');
});

it('can hide the header WhatsApp button', function () {
    saveLayout(['header_cta_visible' => false]);

    expect(html((string) $this->get('/')->getContent())->querySelector('.navbar__cta'))->toBeNull();
});

it('keeps a hidden or empty section out of the menu', function () {
    saveLayout(['menu' => [['key' => 'blog', 'label' => 'المدونة', 'visible' => true]]]);

    expect((string) $this->get('/')->getContent())->not->toContain('href="'.url('/blog').'"');
});

it('uses the built-in wording for a section page until its page is published', function () {
    publishServices(1);
    $hub = Page::query()->create(['slug' => 'services', 'template' => 'hub', 'title' => 'كل خدمات التكييف', 'intro' => 'مقدمة من صاحب الموقع.', 'body' => '<p>نص إضافي تحت القائمة.</p>']);
    $hub->seoMeta()->create(['title' => 'عنوان SEO مخصص لصفحة الخدمات']);

    $doc = html((string) $this->get('/services')->getContent());
    expect($doc->querySelector('h1')->textContent)->toBe('خدماتنا');

    $hub->update(['is_published' => true, 'published_at' => now()->subMinute()]);
    $hub->faqs()->create(['question' => 'سؤال عن الخدمات؟', 'answer' => 'إجابة.', 'sort' => 0]);

    $doc = html((string) $this->get('/services')->getContent());
    expect($doc->querySelector('h1')->textContent)->toBe('كل خدمات التكييف')
        ->and($doc->querySelector('.page-header__intro')->textContent)->toBe('مقدمة من صاحب الموقع.')
        ->and($doc->body->textContent)->toContain('نص إضافي تحت القائمة.')->toContain('سؤال عن الخدمات؟')
        ->and($doc->querySelector('title')->textContent)->toBe('عنوان SEO مخصص لصفحة الخدمات')
        ->and($doc->querySelectorAll('h1'))->toHaveCount(1);
});

it('orders and hides home sections as the owner sets them', function () {
    $home = $this->pages['home'];
    $home->update(['data' => [
        'services' => ['heading' => 'خدماتنا', 'items' => [['title' => 'صيانة', 'text' => 'نص']]],
        'cta' => ['heading' => 'كلّمنا النهارده'],
        'sections' => [['key' => 'cta', 'visible' => true], ['key' => 'services', 'visible' => false]],
    ]]);
    $home->faqs()->create(['question' => 'سؤال الرئيسية؟', 'answer' => 'إجابة.', 'sort' => 0]);

    $html = (string) $this->get('/')->getContent();

    expect($html)->not->toContain('id="services-title"')
        ->and(mb_strpos($html, 'كلّمنا النهارده'))->toBeLessThan(mb_strpos($html, 'سؤال الرئيسية؟'));
});

it('shows an uploaded service photo with its Arabic alt and dimensions, else the placeholder', function () {
    [$service] = publishServices(1);
    $service->update(['image' => 'technician-servicing-indoor-split-ac']);

    $doc = html((string) $this->get('/services')->getContent());
    expect($doc->querySelector('.service-card__img img')->getAttribute('src'))->toContain('/images/placeholders/');

    $service->addMedia(UploadedFile::fake()->image('ac-technician-cairo.jpg', 1200, 800))
        ->withCustomProperties(['alt' => 'فني بيركب تكييف في شقة'])
        ->toMediaCollection('image');

    $this->app->forgetScopedInstances();
    $doc = html((string) $this->get('/services')->getContent());
    $img = $doc->querySelector('.service-card__img img');

    expect($img->getAttribute('src'))->toContain('ac-technician-cairo')
        ->and($img->getAttribute('alt'))->toBe('فني بيركب تكييف في شقة')
        ->and($img->getAttribute('width'))->toBe('1200')
        ->and($img->getAttribute('height'))->toBe('800')
        ->and($doc->querySelector('.service-card__img source[type="image/webp"]')->getAttribute('srcset'))->toContain('800w');
});

it('lets the owner upload the home hero photo', function () {
    $this->pages['home']->addMedia(UploadedFile::fake()->image('split-ac-living-room.jpg', 1920, 1080))->toMediaCollection('hero');

    $img = html((string) $this->get('/')->getContent())->querySelector('.hero img');

    expect($img->getAttribute('src'))->toContain('split-ac-living-room')
        ->and($img->getAttribute('fetchpriority'))->toBe('high');
});

it('opens the layout, home, section-page and service editors', function () {
    [$service] = publishServices(1);
    $hub = Page::query()->create(['slug' => 'store', 'template' => 'hub', 'title' => 'المتجر']);
    $admin = enrolledAdmin();

    $this->actingAs($admin)->get('/admin/manage-layout')->assertOk()->assertSee('القائمة الرئيسية');
    $this->actingAs($admin)->get('/admin/pages/'.$this->pages['home']->id.'/edit')->assertOk()->assertSee('ترتيب أقسام الرئيسية');
    $this->actingAs($admin)->get('/admin/pages/'.$hub->id.'/edit')->assertOk()->assertSee('صورة خلفية رأس الصفحة');
    $this->actingAs($admin)->get('/admin/services/'.$service->getRouteKey().'/edit')->assertOk()->assertSee('صورة الخدمة');
});

it('saves the layout form from the admin', function () {
    $this->actingAs(enrolledAdmin());

    Livewire\Livewire::test(ManageLayout::class)
        ->assertFormFieldExists('menu')
        ->fillForm(['topbar_label' => 'اتصل دلوقتي:', 'footer_about' => 'نبذة جديدة للفوتر.', 'float_whatsapp' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    $layout = app(LayoutSettings::class)->refresh();
    expect($layout->topbar_label)->toBe('اتصل دلوقتي:')
        ->and($layout->float_whatsapp)->toBeFalse()
        ->and(collect($layout->menu)->pluck('key')->all())->toBe(array_keys(Navigation::ITEMS));
});
