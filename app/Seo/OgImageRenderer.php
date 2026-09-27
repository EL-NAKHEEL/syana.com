<?php

namespace App\Seo;

use ArPHP\I18N\Arabic;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Geometry\Factories\RectangleFactory;
use Intervention\Image\ImageManager;
use Intervention\Image\Typography\FontFactory;

/**
 * Renders the 1200×630 brand-template Open Graph image (JPEG, kept well under 300 KB for WhatsApp).
 * GD cannot shape Arabic, so text is shaped and reordered with ar-php before drawing.
 */
class OgImageRenderer
{
    public const WIDTH = 1200;

    public const HEIGHT = 630;

    /** Bump when the template changes so cached files are regenerated. */
    public const VERSION = 2;

    /**
     * Same look as the site: navy ground, white Arabic title, orange brand block and phone bar.
     */
    public function render(string $title, ?string $kicker = null): string
    {
        $fonts = resource_path('fonts/src');
        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager->createImage(self::WIDTH, self::HEIGHT)->fill('#02245B');

        // Orange brand block (top right) and phone bar (bottom).
        $image->drawRectangle(fn (RectangleFactory $r) => $r->at(self::WIDTH - 470, 0)->size(470, 110)->background('#FF5E14'));
        $image->drawRectangle(fn (RectangleFactory $r) => $r->at(0, self::HEIGHT - 100)->size(self::WIDTH, 100)->background('#FF5E14'));

        $right = self::WIDTH - 70;

        $image->text($this->shape((string) config('site.brand.name'), 40), $right, 55, fn (FontFactory $f) => $f
            ->filepath($fonts.'/NotoSansArabic-Bold.ttf')->size(52)->color('#FFFFFF')->align('right', 'center'));

        if ($kicker !== null && $kicker !== '') {
            $image->text($this->shape($kicker, 60), $right, 170, fn (FontFactory $f) => $f
                ->filepath($fonts.'/NotoSansArabic-SemiBold.ttf')->size(34)->color('#FF7A3D')->align('right', 'top'));
        }

        $image->text($this->shape($title, 30), $right, 235, fn (FontFactory $f) => $f
            ->filepath($fonts.'/NotoSansArabic-Bold.ttf')->size(62)->color('#FFFFFF')->align('right', 'top')->lineHeight(1.75));

        $image->text($this->shape('اتصل بنا', 20), $right, self::HEIGHT - 50, fn (FontFactory $f) => $f
            ->filepath($fonts.'/NotoSansArabic-Bold.ttf')->size(40)->color('#FFFFFF')->align('right', 'center'));

        $image->text((string) config('site.phone.display'), 70, self::HEIGHT - 50, fn (FontFactory $f) => $f
            ->filepath($fonts.'/Rubik-Bold.ttf')->size(48)->color('#FFFFFF')->align('left', 'center'));

        return (string) $image->encodeUsingMediaType('image/jpeg', quality: 82);
    }

    /**
     * Wraps logically (max 3 lines, ellipsis when cut), then converts each line to visual order for GD:
     * Arabic words are shaped one by one with ar-php (which also mirrors brackets), numbers and Latin
     * tokens stay as they are (so 1.5 and 12,500 never flip), and word order is reversed for RTL.
     */
    public function shape(string $text, int $maxChars): string
    {
        $arabic = new Arabic;
        $lines = [];
        $line = '';

        foreach (preg_split('/\s+/u', trim($text)) ?: [] as $word) {
            if ($line !== '' && mb_strlen($line.' '.$word) > $maxChars) {
                $lines[] = $line;
                $line = $word;
            } else {
                $line = $line === '' ? $word : $line.' '.$word;
            }
        }
        $lines[] = $line;

        if (count($lines) > 3) {
            $lines = array_slice($lines, 0, 3);
            $lines[2] .= '…';
        }

        return implode("\n", array_map(fn (string $line) => implode(' ', array_reverse(array_map(
            fn (string $word) => preg_match('/\p{Arabic}/u', $word) ? $arabic->utf8Glyphs($word, 1000, false, true) : $word,
            explode(' ', $line),
        ))), $lines));
    }
}
