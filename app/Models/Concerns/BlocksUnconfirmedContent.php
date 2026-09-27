<?php

namespace App\Models\Concerns;

use Illuminate\Validation\ValidationException;

/**
 * Content honesty: a record holding a «[TODO: …]» marker (an unconfirmed fact) can never be published.
 * Also sanitizes rich-text attributes (no inline styles, no scripts, no H1).
 */
trait BlocksUnconfirmedContent
{
    public static function bootBlocksUnconfirmedContent(): void
    {
        static::saving(function (self $model): void {
            foreach ($model->richTextAttributes() as $attribute) {
                if (filled($model->{$attribute})) {
                    $model->{$attribute} = clean($model->{$attribute});
                }
            }

            if ($model->is_published && $model->containsTodo()) {
                throw ValidationException::withMessages([
                    'is_published' => 'فيه [TODO] — لازم تتأكد من المعلومات دي قبل النشر.',
                ]);
            }
        });
    }

    /**
     * @return array<int, string>
     */
    public function richTextAttributes(): array
    {
        return ['body'];
    }

    public function containsTodo(): bool
    {
        $values = array_map(
            fn ($value) => is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : (string) $value,
            array_intersect_key($this->getAttributes() + $this->attributesToArray(), array_flip($this->contentAttributes())),
        );

        return str_contains(implode(' ', $values), '[TODO');
    }
}
