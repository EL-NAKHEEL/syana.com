<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSiteImages;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use Database\Factories\PageFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Spatie\MediaLibrary\HasMedia;

/**
 * Fixed-route pages (home, about, contact, policies). The slug is the route key, not a URL segment,
 * so it never changes and needs no slug history.
 */
/**
 * @property int $id
 * @property string $slug
 * @property string $template
 * @property string $title
 * @property string|null $intro
 * @property string|null $body
 * @property array<string, mixed>|null $data
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'template', 'title', 'intro', 'body', 'data', 'is_published', 'published_at'])]
class Page extends Model implements HasMedia, HasSeo
{
    /** @use HasFactory<PageFactory> */
    use AffectsPublicPages, BlocksUnconfirmedContent, HasFactory, HasSeoMeta, HasSiteImages, Publishable, TracksContentModification;

    public const TEMPLATES = ['home' => 'الرئيسية', 'about' => 'من نحن', 'contact' => 'تواصل معنا', 'default' => 'صفحة عادية', 'hub' => 'صفحة قسم'];

    /** Section (hub) pages whose heading, intro, extra text, FAQs, header photo and SEO are edited as a Page. */
    public const HUBS = [
        'services' => 'خدماتنا',
        'store' => 'المتجر',
        'prices' => 'الأسعار',
        'areas' => 'مناطق الخدمة',
        'blog' => 'المدونة',
        'projects' => 'مشاريعنا',
        'reviews' => 'آراء العملاء',
    ];

    /** Home sections below the hero, in default order. */
    public const HOME_SECTIONS = [
        'about' => 'من نحن',
        'why' => 'ليه إحنا',
        'services' => 'الخدمات',
        'process' => 'خطوات الشغل',
        'body' => 'محتوى إضافي',
        'faqs' => 'الأسئلة الشائعة',
        'cta' => 'الدعوة للتواصل',
    ];

    /**
     * Visible home sections in the owner's order (data.sections = list of {key, visible}); sections not in the
     * saved list are appended, so a newly added section never disappears silently.
     *
     * @return array<int, string>
     */
    public function homeSections(): array
    {
        $saved = collect((array) $this->data('sections', []))
            ->filter(fn ($entry) => is_array($entry) && isset(self::HOME_SECTIONS[$entry['key'] ?? null]))
            ->unique('key');

        $missing = collect(array_keys(self::HOME_SECTIONS))->diff($saved->pluck('key'))
            ->map(fn (string $key) => ['key' => $key, 'visible' => true]);

        return $saved->concat($missing)
            ->filter(fn (array $entry) => ($entry['visible'] ?? true) !== false)
            ->pluck('key')->values()->all();
    }

    /**
     * The published text for a section page, or null (the view then uses its built-in wording).
     */
    public static function hub(string $slug): ?self
    {
        return static::query()->published()->where('template', 'hub')->where('slug', $slug)
            ->with(['seoMeta', 'faqs', 'media'])->first();
    }

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    public function contentAttributes(): array
    {
        return ['title', 'intro', 'body', 'data', 'is_published'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('header')->singleFile(); // inner-page header background
        $this->addMediaCollection('hero')->singleFile();   // home hero
        $this->addMediaCollection('gallery');              // home «من نحن» photos (first two)
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }

    /**
     * Indexable when live and not switched to noindex in the SEO panel.
     */
    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isPublished() && ($robots === null || str_starts_with($robots, 'index'));
    }

    public function data(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->data ?? [], $key, $default);
    }
}
