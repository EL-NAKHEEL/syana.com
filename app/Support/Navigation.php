<?php

namespace App\Support;

use App\Models\Page;
use App\Settings\BusinessSettings;
use Illuminate\Support\Facades\Route;

/**
 * Header/footer links. A link appears only when its route exists and its target is live, so the site
 * never links to a 404 (later phases add store, services, areas, prices, blog…).
 */
class Navigation
{
    /** @var array<int, string>|null */
    private ?array $publishedPages = null;

    public function __construct(private readonly BusinessSettings $business) {}

    /**
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function main(): array
    {
        return $this->filter([
            ['label' => 'الرئيسية', 'route' => 'home', 'page' => 'home'],
            ['label' => 'من نحن', 'route' => 'about', 'page' => 'about'],
            ['label' => 'تواصل معنا', 'route' => 'contact', 'page' => 'contact'],
        ]);
    }

    /**
     * Footer: hubs and footer areas only (PLAN.md §6.6).
     *
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function footer(): array
    {
        return $this->filter([
            ['label' => 'من نحن', 'route' => 'about', 'page' => 'about'],
            ['label' => 'تواصل معنا', 'route' => 'contact', 'page' => 'contact'],
        ]);
    }

    public function reviewUrl(): ?string
    {
        return $this->business->gbp_review_url ?: null;
    }

    /**
     * @param  array<int, array{label: string, route: string, page?: string}>  $items
     * @return array<int, array{label: string, url: string, route: string}>
     */
    private function filter(array $items): array
    {
        $live = [];

        foreach ($items as $item) {
            if (! Route::has($item['route'])) {
                continue;
            }
            if (isset($item['page']) && $item['page'] !== 'home' && ! in_array($item['page'], $this->publishedPages(), true)) {
                continue;
            }
            $live[] = ['label' => $item['label'], 'url' => route($item['route']), 'route' => $item['route']];
        }

        return $live;
    }

    /**
     * @return array<int, string>
     */
    private function publishedPages(): array
    {
        return $this->publishedPages ??= Page::query()->published()->pluck('slug')->all();
    }
}
