<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 99999);

        return [
            'brand_id' => Brand::factory(),
            'slug' => 'ac-model-'.$n,
            'name' => 'موديل '.$n,
            'model_number' => 'MDL-'.$n,
            'type' => 'split',
            'hp' => 1.5,
            'btu' => 12000,
            'cooling' => 'cool',
            'is_inverter' => false,
            'price' => 20000 + $n,
            'stock_status' => 'in_stock',
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->subDay()]);
    }
}
