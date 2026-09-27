<?php

namespace App\Models\Contracts;

use App\Models\SeoMeta;
use Illuminate\Database\Eloquent\Relations\MorphOne;

/**
 * A public record with a per-record SEO panel (see App\Models\Concerns\HasSeoMeta).
 */
interface HasSeo
{
    /**
     * @return MorphOne<SeoMeta, covariant \Illuminate\Database\Eloquent\Model>
     */
    public function seoMeta(): MorphOne;
}
