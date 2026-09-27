<?php

/*
| Guards the design-system rules from CLAUDE.md: the existing site's tokens, WCAG contrast for the pairs the
| templates use, logical properties only, no letter-spacing on Arabic, Latin-only webfont subsets.
*/

function tokens(): array
{
    preg_match_all('/--(primary|primary-text|secondary|light|dark|white|success):\s*(#[0-9a-f]{6})/i', file_get_contents(resource_path('css/tokens.css')), $m);

    return array_combine($m[1], $m[2]);
}

function luminance(string $hex): float
{
    $channels = array_map(function (string $c) {
        $v = hexdec($c) / 255;

        return $v <= 0.03928 ? $v / 12.92 : (($v + 0.055) / 1.055) ** 2.4;
    }, str_split(ltrim($hex, '#'), 2));

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function contrast(string $a, string $b): float
{
    [$l1, $l2] = [luminance($a), luminance($b)];

    return (max($l1, $l2) + 0.05) / (min($l1, $l2) + 0.05);
}

it('keeps the existing site\'s brand colors', function () {
    expect(tokens())->toMatchArray([
        'primary' => '#ff5e14', 'secondary' => '#5f656f', 'light' => '#f5f5f5', 'dark' => '#02245b',
    ]);
});

it('meets WCAG AA for the allowed text/background pairs', function (string $text, string $bg, float $min) {
    $t = tokens();

    expect(contrast($t[$text], $t[$bg]))->toBeGreaterThanOrEqual($min);
})->with([
    'body text on white' => ['secondary', 'white', 4.5],
    'body text on light sections' => ['secondary', 'light', 4.5],
    'headings on white' => ['dark', 'white', 7.0],
    'white on navy' => ['white', 'dark', 7.0],
    'small orange text on white' => ['primary-text', 'white', 4.5],
    'white on success buttons' => ['white', 'success', 4.5],
    'white on orange (bold ≥ 1.2rem only)' => ['white', 'primary', 3.0],
    'orange on navy' => ['primary', 'dark', 4.5],
]);

it('uses logical properties and never letter-spacing', function () {
    foreach (glob(resource_path('css/{*,components/*}.css'), GLOB_BRACE) as $file) {
        $css = (string) preg_replace('#/\*.*?\*/#s', '', file_get_contents($file));

        expect($css)->not->toMatch('/letter-spacing/', basename($file))
            ->not->toMatch('/(?<![-\w])(margin|padding|border)-(left|right)\b/', basename($file))
            ->not->toMatch('/(?<![-\w])(left|right)\s*:/', basename($file))
            ->not->toMatch('/text-align:\s*(left|right)/', basename($file))
            ->not->toMatch('/float:\s*(left|right)/', basename($file));
    }
});

it('serves Latin-only webfont subsets (Arabic uses the platform font, as on the existing site)', function () {
    $css = file_get_contents(resource_path('css/fonts.css'));

    expect(substr_count($css, '@font-face'))->toBe(4)
        ->and(substr_count($css, 'unicode-range: U+0020-007E'))->toBe(4)
        ->and($css)->not->toContain('U+0600');
});

it('keeps orange buttons bold and large enough for white-on-orange contrast', function () {
    $css = file_get_contents(resource_path('css/components/buttons.css'));

    expect($css)->toMatch('/\.btn-primary \{[^}]*font-size: 1\.2rem;[^}]*font-weight: 700;/s');
});
