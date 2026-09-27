<?php

use App\Seo\OgImageRenderer;

it('keeps numbers intact when converting Arabic to visual order for GD', function () {
    $shaped = (new OgImageRenderer)->shape('تكييف 1.5 حصان بسعر 12,500 جنيه', 60);

    expect($shaped)->toContain('1.5')->toContain('12,500')->not->toContain('5.1')->not->toContain('500,12');
});

it('wraps to at most three lines and marks the cut', function () {
    $shaped = (new OgImageRenderer)->shape(str_repeat('صيانة تكييفات ', 20), 30);

    expect(explode("\n", $shaped))->toHaveCount(3)->and($shaped)->toContain('…');
});

it('renders a 1200x630 JPEG well under 300 KB', function () {
    $jpeg = (new OgImageRenderer)->render('صيانة وتركيب تكييفات في التجمع الخامس', 'خدماتنا');
    [$width, $height, $type] = getimagesizefromstring($jpeg);

    expect($width)->toBe(1200)->and($height)->toBe(630)->and($type)->toBe(IMAGETYPE_JPEG)
        ->and(strlen($jpeg))->toBeLessThan(300 * 1024);
});
