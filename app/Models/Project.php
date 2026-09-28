<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\BlocksUnconfirmedContent;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A real job the company did (case study). Only real projects with real photos.
 *
 * @property int $id
 * @property string $slug
 * @property string $title
 * @property string $client_type
 * @property int|null $area_id
 * @property int|null $service_id
 * @property string $summary
 * @property string|null $story
 * @property Carbon|null $completed_on
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read Area|null $area
 * @property-read Service|null $service
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'title', 'client_type', 'area_id', 'service_id', 'summary', 'story', 'completed_on', 'is_published', 'published_at'])]
class Project extends Model implements HasMedia, HasSeo
{
    use AffectsPublicPages, BlocksUnconfirmedContent, HasSeoMeta, HasSlugHistory, InteractsWithMedia, Publishable, TracksContentModification;

    public const CLIENT_TYPES = ['home' => 'بيت', 'office' => 'مكتب', 'retail' => 'محل', 'medical' => 'منشأة طبية', 'hotel' => 'فندق', 'other' => 'أخرى'];

    protected function casts(): array
    {
        return ['completed_on' => 'date'];
    }

    public function contentAttributes(): array
    {
        return ['title', 'summary', 'story', 'area_id', 'service_id', 'is_published'];
    }

    /**
     * @return array<int, string>
     */
    public function richTextAttributes(): array
    {
        return ['story'];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photos');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('card')->nonQueued()->fit(Fit::Crop, 640, 480)->format('webp');
        $this->addMediaConversion('large')->fit(Fit::Max, 1400, 1400)->format('webp');
    }

    public function url(): string
    {
        return route('projects.show', $this->slug);
    }

    /**
     * @return BelongsTo<Area, $this>
     */
    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    /**
     * @return BelongsTo<Service, $this>
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}
