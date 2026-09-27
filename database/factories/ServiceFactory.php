<?php

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        $n = $this->faker->unique()->numberBetween(1, 9999);

        return [
            'slug' => 'service-'.$n,
            'name' => 'خدمة تكييف '.$n,
            'h1' => 'خدمة تكييف رقم '.$n,
            'summary' => 'وصف مختصر لخدمة التكييف رقم '.$n.' للاختبار، فيه تفاصيل كفاية علشان الوصف يبقى طويل ومفيد للزائر.',
            'intro' => 'مقدمة الخدمة.',
            'included' => ['بند أول', 'بند تاني'],
            'warning_signs' => ['علامة أولى'],
            'process_steps' => [['title' => 'خطوة', 'text' => 'شرح الخطوة']],
            'price_factors' => ['عامل السعر'],
            'price_note' => 'السعر بعد المعاينة',
            'image' => 'technician-inspecting-condenser',
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->subDay()]);
    }
}
