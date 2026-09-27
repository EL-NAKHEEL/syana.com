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
    public const VERSION = 1;

    private const LOUVER = ['#E73F1E', '#FB6C00', '#F9B637', '#FFDD9C'];

    public function render(string $title, ?string $kicker = null): string
    {
        $fonts = resource_path('fonts/src');
        $manager = ImageManager::usingDriver(GdDriver::class);
        $image = $manager->createImage(self::WIDTH, self::HEIGHT)->fill('#FFF6E8');

        // Four-stripe louver band (top) and ink footer band.
        foreach (self::LOUVER as $i => $color) {
            $image->drawRectangle(fn (RectangleFactory $r) => $r->at(0, $i * 18)->size(self::WIDTH, 18)->background($color));
        }
        $image->drawRectangle(fn (RectangleFactory $r) => $r->at(0, self::HEIGHT - 110)->size(self::WIDTH, 110)->background('#1F120C'));

        $right = self::WIDTH - 80;

        if ($kicker !== null && $kicker !== '') {
            $image->text($this->shape($kicker, 60), $right, 130, fn (FontFactory $f) => $f
                ->filepath($fonts.'/IBMPlexSansArabic-SemiBold.ttf')->size(34)->color('#E73F1E')->align('right', 'top'));
        }

        $image->text($this->shape($title, 30), $right, 200, fn (FontFactory $f) => $f
            ->filepath($fonts.'/Changa-ExtraBold.ttf')->size(68)->color('#1F120C')->align('right', 'top')->lineHeight(1.9));

        $image->text($this->shape(config('site.brand.name'), 40), $right, self::HEIGHT - 55, fn (FontFactory $f) => $f
            ->filepath($fonts.'/Changa-ExtraBold.ttf')->size(44)->color('#F9B637')->align('right', 'center'));

        $image->text(config('site.phone.display'), 80, self::HEIGHT - 55, fn (FontFactory $f) => $f
            ->filepath($fonts.'/Handjet-Medium.ttf')->size(56)->color('#F9B637')->align('left', 'center'));

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
