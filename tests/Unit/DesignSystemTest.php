<?php

/*
| Guards the design-system rules from CLAUDE.md: token contrast, logical properties only,
| no letter-spacing on Arabic, Handjet only for LCD digits.
*/

function tokens(): array
{
    preg_match_all('/--(t45|t38|t30|t24|ink|paper):\s*(#[0-9a-f]{6})/i', file_get_contents(resource_path('css/tokens.css')), $m);

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

it('keeps the brand tokens exactly as specified', function () {
    expect(tokens())->toBe([
        't45' => '#e73f1e', 't38' => '#fb6c00', 't30' => '#f9b637', 't24' => '#ffdd9c', 'ink' => '#1f120c', 'paper' => '#fff6e8',
    ]);
});

it('meets WCAG AA for the allowed text/background pairs', function (string $text, string $bg, float $min) {
    $t = tokens();

    expect(contrast($t[$text], $t[$bg]))->toBeGreaterThanOrEqual($min);
})->with([
    'ink on t38 (actions)' => ['ink', 't38', 4.5],
    'ink on t30' => ['ink', 't30', 4.5],
    'ink on t24' => ['ink', 't24', 4.5],
    'ink on paper' => ['ink', 'paper', 7.0],
    't30 LCD digits on ink' => ['t30', 'ink', 4.5],
    'paper on t45 (large text only)' => ['paper', 't45', 3.0],
    'ink on t45 (large text only)' => ['ink', 't45', 3.0],
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

it('limits Handjet to the LCD digits subset', function () {
    $css = file_get_contents(resource_path('css/fonts.css'));

    expect($css)->toContain('unicode-range: U+0020, U+0030-0039, U+00B0;');
});
