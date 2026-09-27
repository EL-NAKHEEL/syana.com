<?php

namespace App\Models;

use App\Models\Concerns\AffectsPublicPages;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['question', 'answer', 'sort'])]
class Faq extends Model
{
    use AffectsPublicPages;

    /**
     * @return MorphTo<Model, $this>
     */
    public function faqable(): MorphTo
    {
        return $this->morphTo();
    }
}
