<?php

namespace App\Seo;

class TitleBuilder
{
    /**
     * Appends «صفحة N» for paginated pages and the brand suffix when it fits within the title budget.
     */
    public static function title(string $base, int $page = 1, bool $withBrand = true): string
    {
        $title = trim($base);

        if ($page > 1) {
            $title .= ' - صفحة '.$page;
        }

        $brand = (string) config('site.brand.name');

        if (! $withBrand || str_contains($title, $brand)) {
            return $title;
        }

        $branded = $title.config('site.brand.title_separator').$brand;

        return mb_strlen($branded) <= (int) config('site.seo.title_max') ? $branded : $title;
    }

    public static function description(string $description, int $page = 1): string
    {
        $description = trim((string) preg_replace('/\s+/u', ' ', $description));

        if ($page > 1) {
            $description = 'صفحة '.$page.': '.$description;
        }

        return self::limit($description, (int) config('site.seo.description_max'));
    }

    /**
     * Cuts on a word boundary so a description never ends mid-word.
     */
    public static function limit(string $text, int $max): string
    {
        if (mb_strlen($text) <= $max) {
            return $text;
        }

        $cut = mb_substr($text, 0, $max - 1);
        $space = mb_strrpos($cut, ' ');

        return rtrim($space !== false ? mb_substr($cut, 0, $space) : $cut, ' ،,.:؛-').'…';
    }
}
