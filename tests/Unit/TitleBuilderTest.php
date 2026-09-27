<?php

use App\Seo\TitleBuilder;

it('appends the brand when it fits in 60 characters', function () {
    expect(TitleBuilder::title('صيانة وتركيب تكييفات في التجمع الخامس'))
        ->toBe('صيانة وتركيب تكييفات في التجمع الخامس | النخيل كوول');
});

it('drops the brand rather than exceeding the title budget', function () {
    $long = 'تكييف شارب 1.5 حصان بارد ساخن إنفرتر موديل جديد بالتركيب المجاني';

    expect(TitleBuilder::title($long))->toBe($long);
});

it('does not repeat the brand and adds the page number', function () {
    expect(TitleBuilder::title('النخيل كوول | بيع وتركيب وصيانة التكييفات في مصر'))->toBe('النخيل كوول | بيع وتركيب وصيانة التكييفات في مصر')
        ->and(TitleBuilder::title('مدونة التكييف', 3))->toBe('مدونة التكييف - صفحة 3 | النخيل كوول');
});

it('prefixes paginated descriptions and cuts long ones on a word boundary', function () {
    expect(TitleBuilder::description('وصف قصير', 2))->toBe('صفحة 2: وصف قصير');

    $cut = TitleBuilder::description(str_repeat('كلمة ', 60));
    expect(mb_strlen($cut))->toBeLessThanOrEqual(160)
        ->and($cut)->toEndWith('كلمة…');
});
