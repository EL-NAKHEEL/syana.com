<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\HasSeoMeta;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use App\Models\Contracts\HasSeo;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name
 * @property string|null $intro
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 * @property-read SeoMeta|null $seoMeta
 */
#[Fillable(['slug', 'name', 'intro', 'sort', 'is_published', 'published_at'])]
class PostCategory extends Model implements HasSeo
{
    use AffectsPublicPages, HasSeoMeta, HasSlugHistory, Publishable, TracksContentModification;

    public function contentAttributes(): array
    {
        return ['name', 'intro', 'is_published'];
    }

    public function url(): string
    {
        return route('blog.category', $this->slug);
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
