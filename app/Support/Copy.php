<?php

namespace App\Support;

use Illuminate\Support\HtmlString;

/**
 * Renders editor copy safely. «[TODO: …]» markers (unconfirmed facts) are highlighted so they are
 * impossible to miss in draft previews; published pages must not contain any (tested).
 */
class Copy
{
    public static function text(?string $text): HtmlString
    {
        return new HtmlString(self::markTodos(e((string) $text)));
    }

    /**
     * For HTML that was sanitized when saved (admin rich text / seeded drafts).
     */
    public static function html(?string $html): HtmlString
    {
        return new HtmlString(self::markTodos((string) $html));
    }

    private static function markTodos(string $html): string
    {
        return (string) preg_replace('/\[TODO[^\]]*\]/u', '<mark class="todo">$0</mark>', $html);
    }
}
