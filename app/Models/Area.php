<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use Database\Factories\AreaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name_ar
 * @property string|null $name_en
 * @property string|null $governorate
 * @property string|null $local_intro
 * @property string|null $response_time_note
 * @property string|null $local_notes
 * @property bool $show_in_footer
 * @property int $sort
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'name_ar', 'name_en', 'governorate', 'local_intro', 'response_time_note', 'local_notes', 'show_in_footer', 'sort', 'is_published', 'published_at'])]
class Area extends Model implements HasSeo
{
    /** @use HasFactory<AreaFactory> */
    use AffectsPublicPages, BlocksUnconfirmedContent, HasFactory, HasSeoMeta, HasSlugHistory, Publishable, TracksContentModification;

    protected function casts(): array
    {
        return ['show_in_footer' => 'boolean', 'sort' => 'integer'];
    }

    public function contentAttributes(): array
    {
        return ['name_ar', 'local_intro', 'response_time_note', 'local_notes', 'is_published'];
    }

    /**
     * @return array<int, string>
     */
    public function richTextAttributes(): array
    {
        return [];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function url(): string
    {
        return route('areas.show', $this);
    }

    /** Minimum words in the local intro before an area page may go live (anti doorway page). */
    public const MIN_INTRO_WORDS = 150;

    public const MIN_FAQS = 2;

    /**
     * Live areas, guard included. Relation counts are loaded in one query each (no N+1).
     *
     * @return Collection<int, Area>
     */
    public static function live(): Collection
    {
        return self::query()
            ->published()
            ->withCount(['services', 'faqs', 'neighbors'])
            ->with('seoMeta')
            ->orderBy('sort')
            ->orderBy('name_ar')
            ->get()
            ->filter(fn (Area $area) => $area->guardFailures() === [])
            ->values();
    }

    /**
     * Why this area may not be published (owner decision C1: local reviews optional, everything else required).
     *
     * @return array<int, string>
     */
    public function guardFailures(): array
    {
        $failures = [];

        if (self::wordCount($this->local_intro) < self::MIN_INTRO_WORDS) {
            $failures[] = 'المقدمة المحلية لازم تكون '.self::MIN_INTRO_WORDS.' كلمة على الأقل (دلوقتي '.self::wordCount($this->local_intro).').';
        }
        if (blank($this->response_time_note)) {
            $failures[] = 'اكتب وقت الاستجابة في المنطقة.';
        }
        if (blank($this->local_notes)) {
            $failures[] = 'اكتب ملاحظات محلية عن المنطقة.';
        }
        if ($this->relationCount('services') < 1) {
            $failures[] = 'اختار خدمة واحدة على الأقل متاحة في المنطقة.';
        }
        if ($this->relationCount('faqs') < self::MIN_FAQS) {
            $failures[] = 'أضف '.self::MIN_FAQS.' أسئلة شائعة محلية على الأقل.';
        }
        if ($this->relationCount('neighbors') < 1) {
            $failures[] = 'اختار منطقة مجاورة واحدة على الأقل.';
        }

        return $failures;
    }

    public function isLive(): bool
    {
        return $this->isPublished() && $this->guardFailures() === [];
    }

    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isLive() && ($robots === null || str_starts_with($robots, 'index'));
    }

    public static function wordCount(?string $text): int
    {
        return count(preg_split('/\s+/u', trim((string) $text), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    }

    private function relationCount(string $relation): int
    {
        $counted = $this->getAttribute($relation.'_count');

        return $counted !== null ? (int) $counted : $this->{$relation}()->count();
    }

    /**
     * @return BelongsToMany<Service, $this>
     */
    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class)->withPivot('note');
    }

    /**
     * @return BelongsToMany<Area, $this>
     */
    public function neighbors(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'area_neighbors', 'area_id', 'neighbor_id');
    }

    /**
     * @return MorphMany<Faq, $this>
     */
    public function faqs(): MorphMany
    {
        return $this->morphMany(Faq::class, 'faqable')->orderBy('sort');
    }
}
