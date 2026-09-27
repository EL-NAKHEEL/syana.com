<?php

namespace Database\Factories;

use App\Models\Area;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Area>
 */
class AreaFactory extends Factory
{
    protected $model = Area::class;

    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 9999);

        return [
            'slug' => 'area-'.$n,
            'name_ar' => 'منطقة '.$n,
            'local_intro' => 'مقدمة محلية للمنطقة '.$n,
            'response_time_note' => 'في نفس اليوم',
            'local_notes' => 'ملاحظات محلية',
            'is_published' => false,
        ];
    }
}
