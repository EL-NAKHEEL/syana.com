<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use App\Models\Concerns\HasSlugHistory;
use App\Models\Concerns\Publishable;
use App\Models\Concerns\TracksContentModification;
use Database\Factories\BrandFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $slug
 * @property string $name_ar
 * @property string $name_en
 * @property string|null $description
 * @property int $sort
 * @property bool $is_published
 * @property Carbon|null $published_at
 * @property Carbon|null $content_modified_at
 * @property Carbon|null $created_at
 */
#[Fillable(['slug', 'name_ar', 'name_en', 'description', 'sort', 'is_published', 'published_at'])]
class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use AffectsPublicPages, HasFactory, HasSlugHistory, Publishable, TracksContentModification;

    protected function casts(): array
    {
        return ['sort' => 'integer'];
    }

    public function contentAttributes(): array
    {
        return ['name_ar', 'name_en', 'description', 'is_published'];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
