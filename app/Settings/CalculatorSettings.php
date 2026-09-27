<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

/**
 * «تكييف كام حصان لأوضتك؟». Defaults are common rules of thumb, NOT confirmed by the owner's technicians:
 * the calculator stays hidden until `confirmed` is switched on in the admin (PLAN.md Q17).
 */
class CalculatorSettings extends Settings
{
    public bool $confirmed;

    public int $btu_per_m2;

    public float $sunny_factor;

    public float $top_floor_factor;

    // HP → nominal BTU, as a JSON-able map of strings (keys "1.5", "2.25", …).
    public array $hp_btu;

    public static function group(): string
    {
        return 'calculator';
    }

    /**
     * @return array{btu: int, hp: float|null}
     */
    public function recommend(int $areaM2, bool $sunny, bool $topFloor): array
    {
        $btu = (int) round($areaM2 * $this->btu_per_m2 * ($sunny ? $this->sunny_factor : 1) * ($topFloor ? $this->top_floor_factor : 1));
        $capacities = collect($this->hp_btu)->map(fn ($v) => (int) $v)->sort();
        $hp = $capacities->filter(fn (int $capacity) => $capacity >= $btu)->keys()->first() ?? $capacities->keys()->last();

        return ['btu' => $btu, 'hp' => $hp !== null ? (float) $hp : null];
    }
}
