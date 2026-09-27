<?php

namespace Database\Factories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 9999);

        return ['slug' => 'brand-'.$n, 'name_ar' => 'ماركة '.$n, 'name_en' => 'Brand '.$n, 'is_published' => false];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->subDay()]);
    }
}
