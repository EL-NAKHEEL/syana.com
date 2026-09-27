<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['title', 'description', 'primary_keyword', 'canonical_override', 'robots', 'og_title', 'og_description', 'og_image'])]
class SeoMeta extends Model
{
    use AffectsPublicPages;

    public const ROBOTS_OPTIONS = [
        'index, follow' => 'index, follow',
        'noindex, follow' => 'noindex, follow',
        'noindex, nofollow' => 'noindex, nofollow',
    ];

    protected $table = 'seo_meta';

    /**
     * @return MorphTo<Model, $this>
     */
    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
