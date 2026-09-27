<?php

use App\Support\ArabicNormalizer;

it('normalizes Arabic search text', function (string $input, string $expected) {
    expect(ArabicNormalizer::normalize($input))->toBe($expected);
})->with([
    ['إنفرتر', 'انفرتر'],
    ['أجهزة التكييف', 'اجهزه التكييف'],
    ['تكيـــيف', 'تكييف'],
    ['مُكَيِّف', 'مكيف'],
    ['تكييف ١٫٥ حصان', 'تكييف 1.5 حصان'],
    ['  Sharp   AH-A12 ', 'sharp ah-a12'],
    ['مستشفى', 'مستشفي'],
]);
