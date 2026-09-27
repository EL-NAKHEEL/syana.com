<?php

namespace App\Catalog;

/**
 * Catalog vocabulary: AC types, capacities (HP) and their URL slugs (PLAN.md §4).
 */
class Catalog
{
    /** @var array<string, string> slug => Arabic name */
    public const TYPES = [
        'split' => 'سبليت',
        'window' => 'شباك',
        'concealed' => 'كونسيلد',
        'cassette' => 'كاسيت',
        'floor-standing' => 'دولابي',
    ];

    /** @var array<string, string> */
    public const COOLING = [
        'cool' => 'بارد فقط',
        'cool-heat' => 'بارد ساخن',
    ];

    /** @var array<string, string> */
    public const STOCK = [
        'in_stock' => 'متوفر',
        'out_of_stock' => 'غير متوفر حاليًا',
        'preorder' => 'حجز مسبق',
        'discontinued' => 'توقف إنتاجه',
    ];

    /** @var array<string, string> schema.org availability per stock status */
    public const AVAILABILITY = [
        'in_stock' => 'https://schema.org/InStock',
        'out_of_stock' => 'https://schema.org/OutOfStock',
        'preorder' => 'https://schema.org/PreOrder',
        'discontinued' => 'https://schema.org/Discontinued',
    ];

    /** @var array<string, float> slug => HP */
    public const CAPACITIES = [
        '1-5-hp' => 1.5,
        '2-25-hp' => 2.25,
        '3-hp' => 3.0,
        '4-hp' => 4.0,
        '5-hp' => 5.0,
    ];

    public static function hpSlug(float|string $hp): string
    {
        return str_replace('.', '-', rtrim(rtrim(number_format((float) $hp, 2, '.', ''), '0'), '.')).'-hp';
    }

    public static function hpFromSlug(string $slug): ?float
    {
        return self::CAPACITIES[$slug] ?? null;
    }

    /** 1.5 → «1.5», 3.0 → «3» */
    public static function hpLabel(float|string $hp): string
    {
        return rtrim(rtrim(number_format((float) $hp, 2, '.', ''), '0'), '.');
    }
}
