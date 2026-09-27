<?php

namespace App\Support;

/**
 * Search normalization (PLAN.md §6.7): أ/إ/آ→ا، ة→ه، ى→ي، strips tashkeel and tatweel, Arabic-Indic digits →
 * Western, lowercase Latin, single spaces. Applied to stored search_text and to queries.
 */
class ArabicNormalizer
{
    public static function normalize(?string $text): string
    {
        $text = (string) $text;
        $text = (string) preg_replace('/[\x{064B}-\x{0652}\x{0670}\x{0640}]/u', '', $text);
        $text = strtr($text, [
            'أ' => 'ا', 'إ' => 'ا', 'آ' => 'ا', 'ٱ' => 'ا', 'ة' => 'ه', 'ى' => 'ي', 'ؤ' => 'و', 'ئ' => 'ي',
            '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4', '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
            '٫' => '.', '،' => ' ',
        ]);
        $text = mb_strtolower($text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }
}
