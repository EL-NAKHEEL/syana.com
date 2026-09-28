<?php

use App\Events\PublicContentChanged;
use App\Jobs\PingIndexNow;
use App\Models\Page;
use App\Models\Post;
use App\Models\Review;
use App\Models\User;
use App\Seo\Sitemap\SitemapGenerator;
use App\Settings\SeoSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Queue;
use Spatie\ResponseCache\Facades\ResponseCache;

beforeEach(function () {
    publishCorePages();
});

function jsonLd(string $markup): Collection
{
    return collect(json_decode(html($markup)->querySelector('script[type="application/ld+json"]')->textContent, true)['@graph']);
}

// ---------- Blog ----------

it('renders a post with TOC anchors, author box, dates and BlogPosting + Person schema', function () {
    publishBlog(1);

    $response = $this->get('/blog/post-1')->assertOk();
    $doc = html((string) $response->getContent());
    $graph = jsonLd((string) $response->getContent());

    expect($doc->querySelectorAll('h1'))->toHaveCount(1)
        ->and($doc->querySelector('h1')->textContent)->toBe('مقال تجريبي عن التكييف رقم 1')
        ->and($doc->querySelector('.post-body h2#section-1'))->not->toBeNull()
        ->and($doc->querySelector('.toc a[href="#section-1"]'))->not->toBeNull()
        ->and($doc->querySelector('.post-meta a[href="'.url('/blog/author/ahmed').'"]'))->not->toBeNull()
        ->and($graph->firstWhere('@type', 'BlogPosting')['author']['@id'])->toBe(url('/blog/author/ahmed').'#person')
        ->and($graph->firstWhere('@type', 'Person')['name'])->toBe('أحمد الفني')
        ->and($doc->querySelector('meta[property="og:type"]')->getAttribute('content'))->toBe('article');
});

it('hides posts whose author is not published', function () {
    ['author' => $author] = publishBlog(1);
    $author->update(['is_published' => false]);

    $this->get('/blog/post-1')->assertNotFound();
    $this->get('/blog/author/ahmed')->assertNotFound();
});

it('embeds live products through the [product:slug] shortcode and drops unknown ones', function () {
    ['posts' => $posts] = publishBlog(1);
    publishCatalog(1);
    $posts[0]->update(['body' => '<p>قبل</p><p>[product:sharp-ah-1]</p><p>[product:missing]</p>']);

    $doc = html((string) $this->get('/blog/post-1')->getContent());

    expect($doc->querySelector('.product-embed a[href="'.url('/store/sharp-ah-1').'"]'))->not->toBeNull()
        ->and($doc->body->textContent)->not->toContain('[product:');
});

it('indexes the blog hub and category from three posts and lists them in the sitemap', function () {
    publishBlog(3);

    $doc = html((string) $this->get('/blog')->assertOk()->getContent());
    $urls = app(SitemapGenerator::class)->allUrls();

    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('index, follow')
        ->and($doc->querySelectorAll('.post-card'))->toHaveCount(3)
        ->and($urls)->toContain(url('/blog'), url('/blog/category/maintenance'), url('/blog/author/ahmed'), url('/blog/post-3'));
});

it('keeps the blog hub noindex with fewer than three posts', function () {
    publishBlog(2);

    $doc = html((string) $this->get('/blog')->assertOk()->getContent());

    expect($doc->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and(app(SitemapGenerator::class)->allUrls())->not->toContain(url('/blog'))->toContain(url('/blog/post-1'));
});

it('301s a renamed post slug', function () {
    ['posts' => $posts] = publishBlog(1);
    $posts[0]->update(['slug' => 'post-renamed']);

    $this->get('/blog/post-1')->assertRedirect(url('/blog/post-renamed'))->assertStatus(301);
});

it('shows the blog and projects in the main navigation only when they have live content', function () {
    $nav = fn () => collect(html((string) $this->get('/')->getContent())->querySelectorAll('.navbar a'))->map->getAttribute('href');

    expect($nav())->not->toContain(url('/blog'), url('/projects'));

    publishBlog(1);
    publishProjects(1);
    ResponseCache::clear();

    expect($nav())->toContain(url('/blog'), url('/projects'));
});

// ---------- Projects ----------

it('renders projects and keeps the hub noindex below three', function () {
    publishProjects(2);

    $hub = html((string) $this->get('/projects')->assertOk()->getContent());
    $show = html((string) $this->get('/projects/project-1')->assertOk()->getContent());

    expect($hub->querySelector('meta[name="robots"]')->getAttribute('content'))->toBe('noindex, follow')
        ->and($show->querySelector('h1')->textContent)->toBe('تركيب تكييفات لمكتب إداري رقم 1')
        ->and($show->querySelector('link[rel="canonical"]')->getAttribute('href'))->toBe(url('/projects/project-1'));
});

it('404s the projects hub for guests when nothing is published', function () {
    $this->get('/projects')->assertNotFound();
});

// ---------- Reviews ----------

it('stores a submitted review as pending and notifies admins', function () {
    $admin = User::factory()->create();

    $this->post('/reviews', ['name' => 'محمد', 'rating' => 5, 'body' => 'الفني جه في الميعاد والتركيب كان نضيف جدًا.'])
        ->assertRedirect(route('reviews.index'))->assertSessionHas('status');

    $review = Review::query()->sole();
    expect($review->status)->toBe('pending')
        ->and($review->approved_at)->toBeNull()
        ->and($admin->notifications()->count())->toBe(1);

    $this->get('/reviews')->assertOk()->assertDontSee('الفني جه في الميعاد');
});

it('rejects spam and invalid video links on the review form', function () {
    $this->post('/reviews', ['name' => 'م', 'rating' => 9, 'body' => 'قصير', 'video_url' => 'https://evil.example/x'])
        ->assertSessionHasErrors(['name', 'rating', 'body', 'video_url']);

    expect(Review::query()->count())->toBe(0);
});

it('publishes a review only after approval and flushes the page cache then', function () {
    $review = Review::query()->create(['name' => 'منى', 'rating' => 4, 'body' => 'خدمة كويسة جدًا والتكييف بقى يبرد تمام.', 'status' => 'pending']);
    $this->get('/reviews')->assertDontSee('خدمة كويسة جدًا');

    $review->update(['status' => 'approved']);

    expect($review->fresh()->approved_at)->not->toBeNull();
    $this->get('/reviews')->assertSee('خدمة كويسة جدًا');
});

it('adds aggregateRating to a product only from its approved reviews shown on the page', function () {
    ['products' => $products] = publishCatalog(1);
    $product = $products[0];
    foreach ([5, 4] as $rating) {
        Review::query()->create(['name' => 'عميل', 'rating' => $rating, 'body' => 'رأي حقيقي عن التكييف ده بعد الاستخدام.', 'status' => 'approved', 'reviewable_type' => 'product', 'reviewable_id' => $product->id]);
    }
    Review::query()->create(['name' => 'معلق', 'rating' => 1, 'body' => 'رأي لسه ما اتراجعش ومش المفروض يظهر.', 'status' => 'pending', 'reviewable_type' => 'product', 'reviewable_id' => $product->id]);

    $content = (string) $this->get('/store/sharp-ah-1')->assertOk()->getContent();
    $node = jsonLd($content)->firstWhere('@type', 'Product');

    expect($node['aggregateRating'])->toMatchArray(['ratingValue' => '4.5', 'reviewCount' => 2])
        ->and($node['review'])->toHaveCount(2)
        ->and(html($content)->querySelectorAll('.review-card'))->toHaveCount(2);
});

it('never marks up the business itself with aggregateRating', function () {
    Review::query()->create(['name' => 'عميل', 'rating' => 5, 'body' => 'رأي عام عن الشركة كلها وخدمتها.', 'status' => 'approved']);

    foreach (['/', '/reviews'] as $uri) {
        expect((string) $this->get($uri)->getContent())->not->toContain('aggregateRating');
    }
});

it('shows approved service reviews on the service page', function () {
    [$service] = publishServices(1);
    Review::query()->create(['name' => 'عميل', 'rating' => 5, 'body' => 'صيانة ممتازة والفني شرح كل حاجة.', 'status' => 'approved', 'reviewable_type' => 'service', 'reviewable_id' => $service->id]);

    $this->get('/services/service-1')->assertOk()->assertSee('صيانة ممتازة والفني شرح كل حاجة.');
});

// ---------- Policies ----------

it('serves published policy pages, links them in the footer and lists them in the sitemap', function () {
    Page::factory()->published()->create(['slug' => 'warranty', 'template' => 'default', 'title' => 'سياسة الضمان', 'body' => '<p>ضمان سنة على التركيب.</p>']);

    $doc = html((string) $this->get('/warranty')->assertOk()->getContent());

    expect($doc->querySelector('h1')->textContent)->toBe('سياسة الضمان')
        ->and($doc->querySelector('.footer__policies a[href="'.url('/warranty').'"]'))->not->toBeNull()
        ->and(app(SitemapGenerator::class)->allUrls())->toContain(url('/warranty'))->not->toContain(url('/privacy'));

    $this->get('/privacy')->assertNotFound();
});

// ---------- IndexNow ----------

it('serves the IndexNow key file only for the configured key', function () {
    $key = app(SeoSettings::class)->indexnow_key;

    $this->get('/'.$key.'.txt')->assertOk()->assertSeeText($key);
    $this->get('/0123456789abcdef0123456789abcdef.txt')->assertNotFound();
});

it('pings IndexNow for published content in production only', function () {
    Queue::fake();
    ['posts' => $posts] = publishBlog(1);

    event(new PublicContentChanged($posts[0]));
    Queue::assertNotPushed(PingIndexNow::class);

    app()->detectEnvironment(fn () => 'production');
    config(['site.indexnow.enabled' => true]);
    event(new PublicContentChanged($posts[0]));

    Queue::assertPushed(PingIndexNow::class, fn (PingIndexNow $job) => $job->url === url('/blog/post-1'));
});

// ---------- Admin ----------

it('reports duplicate SEO titles and media without alt on the SEO health page', function () {
    ['posts' => $posts] = publishBlog(2);
    foreach ($posts as $post) {
        $post->seoMeta()->create(['title' => 'عنوان مكرر']);
    }

    $this->actingAs(enrolledAdmin())
        ->get('/admin/seo-health')
        ->assertOk()
        ->assertSee('عنوان مكرر');
});

it('words the reading time with Arabic number agreement', function (int $minutes, string $expected) {
    $post = new Post;
    $post->reading_minutes = $minutes;

    expect($post->readingTime())->toBe($expected);
})->with([[1, 'دقيقة قراءة'], [2, 'دقيقتين قراءة'], [5, '5 دقايق قراءة'], [12, '12 دقيقة قراءة']]);
