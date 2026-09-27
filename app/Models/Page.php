<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\HasSeoMeta;
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
use Illuminate\Validation\ValidationException;

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
class Page extends Model implements HasSeo
{
    /** @use HasFactory<PageFactory> */
    use AffectsPublicPages, HasFactory, HasSeoMeta, Publishable, TracksContentModification;

    public const TEMPLATES = ['home' => 'الرئيسية', 'about' => 'من نحن', 'contact' => 'تواصل معنا', 'default' => 'صفحة عادية'];

    protected function casts(): array
    {
        return ['data' => 'array'];
    }

    protected static function booted(): void
    {
        static::saving(function (self $page): void {
            if (filled($page->body)) {
                $page->body = clean($page->body);
            }

            // Content honesty: unconfirmed facts ([TODO: …]) can never go live.
            if ($page->is_published && $page->containsTodo()) {
                throw ValidationException::withMessages([
                    'is_published' => 'الصفحة فيها [TODO] — لازم تتأكد من المعلومات دي قبل النشر.',
                ]);
            }
        });
    }

    public function containsTodo(): bool
    {
        $haystack = implode(' ', [$this->title, $this->intro, $this->body, json_encode($this->data, JSON_UNESCAPED_UNICODE)]);

        return str_contains($haystack, '[TODO');
    }

    public function contentAttributes(): array
    {
        return ['title', 'intro', 'body', 'data', 'is_published'];
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
