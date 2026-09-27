<?php

namespace Database\Factories;

use App\Models\Page;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Page>
 */
class PageFactory extends Factory
{
    protected $model = Page::class;

    public function definition(): array
    {
        return [
            'slug' => $this->faker->unique()->slug(2),
            'template' => 'default',
            'title' => 'صفحة تجريبية',
            'intro' => 'مقدمة قصيرة.',
            'body' => '<p>محتوى تجريبي.</p>',
            'data' => null,
            'is_published' => false,
        ];
    }

    public function published(): static
    {
        return $this->state(['is_published' => true, 'published_at' => now()->subDay()]);
    }
}
