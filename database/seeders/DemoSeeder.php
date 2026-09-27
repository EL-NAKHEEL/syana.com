<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Product;
use Illuminate\Database\Seeder;

/**
 * Development-only demo catalog (clearly fake brand names), all UNPUBLISHED. Never runs in production.
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        foreach ([['demo-alpha', 'ماركة تجريبية ألفا', 'Demo Alpha'], ['demo-beta', 'ماركة تجريبية بيتا', 'Demo Beta']] as $i => [$slug, $ar, $en]) {
            $brand = Brand::query()->firstOrCreate(['slug' => $slug], ['name_ar' => $ar, 'name_en' => $en, 'sort' => $i, 'is_published' => false]);

            foreach ([[1.5, 12000, 'split'], [2.25, 18000, 'split'], [3, 24000, 'split'], [1.5, 12000, 'window']] as $j => [$hp, $btu, $type]) {
                $model = strtoupper(substr($en, 5, 1)).'-'.($i + 1).$j.'0'.($hp * 100);
                Product::query()->firstOrCreate(['slug' => $slug.'-'.strtolower($model)], [
                    'brand_id' => $brand->id,
                    'name' => $en.' '.$model,
                    'model_number' => $model,
                    'type' => $type,
                    'hp' => $hp,
                    'btu' => $btu,
                    'cooling' => $j % 2 ? 'cool-heat' : 'cool',
                    'is_inverter' => $j % 2 === 1,
                    'price' => 15000 + $hp * 6000 + $i * 1000,
                    'sale_price' => $j === 0 ? 14000 + $hp * 6000 + $i * 1000 : null,
                    'stock_status' => $j === 3 ? 'out_of_stock' : 'in_stock',
                    'warranty_months' => null,
                    'is_published' => false,
                ]);
            }
        }
    }
}
