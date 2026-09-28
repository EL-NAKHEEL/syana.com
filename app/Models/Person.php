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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A real team member or author (E-E-A-T). Never invented: only people the owner adds.
 *
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $job_title
 * @property string|null $bio
 * @property string|null $credentials
 * @property int|null $years_experience
 * @property array<int, string>|null $same_as
 * @property bool $is_author
 * @property bool $is_team_member
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'name', 'job_title', 'bio', 'credentials', 'years_experience', 'same_as', 'is_author', 'is_team_member', 'sort', 'is_published', 'published_at'])]
class Person extends Model implements HasMedia, HasSeo
{
    use AffectsPublicPages, BlocksUnconfirmedContent, HasSeoMeta, HasSlugHistory, InteractsWithMedia, Publishable, TracksContentModification;

    protected $table = 'people';

    protected function casts(): array
    {
        return ['same_as' => 'array', 'is_author' => 'boolean', 'is_team_member' => 'boolean'];
    }

    public function contentAttributes(): array
    {
        return ['name', 'job_title', 'bio', 'credentials', 'years_experience', 'is_published'];
    }

    /**
     * @return array<int, string>
     */
    public function richTextAttributes(): array
    {
        return [];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('photo')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('avatar')->nonQueued()->fit(Fit::Crop, 240, 240)->format('webp');
    }

    public function url(): string
    {
        return route('blog.author', $this->slug);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class, 'author_id');
    }
}
