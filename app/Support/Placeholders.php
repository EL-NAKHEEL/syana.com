<?php

namespace App\Support;

/**
 * Responsive variants of the TEMPORARY stock photos (scripts/images/build-placeholders.py).
 * Real photos replace them before launch (TODO.md); media-library uploads take over from P2 on.
 */
class Placeholders
{
    /** @var array<string, array{widths: array<int, int>, ratio: float, placeholder: bool}>|null */
    private static ?array $manifest = null;

    /**
     * @return array{widths: array<int, int>, ratio: float, placeholder: bool}
     */
    public static function get(string $name): array
    {
        self::$manifest ??= json_decode((string) file_get_contents(resource_path('images/placeholders.json')), true);

        return self::$manifest[$name] ?? throw new \InvalidArgumentException("Unknown placeholder image [{$name}].");
    }

    /**
     * @return array<string, string> name => name, for admin selects
     */
    public static function options(): array
    {
        self::$manifest ??= json_decode((string) file_get_contents(resource_path('images/placeholders.json')), true);

        $names = array_keys(self::$manifest ?? []);

        return array_combine($names, $names);
    }

    public static function srcset(string $name, string $extension): string
    {
        return implode(', ', array_map(
            fn (int $width) => asset("images/placeholders/{$name}-{$width}.{$extension}")." {$width}w",
            self::get($name)['widths'],
        ));
    }
}
