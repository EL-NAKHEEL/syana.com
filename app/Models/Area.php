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

    public function isIndexable(): bool
    {
        $robots = $this->seoMeta?->robots;

        return $this->isPublished() && ($robots === null || str_starts_with($robots, 'index'));
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
