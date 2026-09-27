<?php

namespace App\Support;

use App\Models\Area;
use App\Models\Page;
use App\Models\PriceGuide;
use App\Models\Service;
use App\Settings\BusinessSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

/**
 * Header/footer links. A link appears only when its route exists and its target is live, so the site never
 * links to a 404. Request-scoped; every query runs at most once per request.
 */
class Navigation
{
    /** @var array<string, mixed> */
    private array $memo = [];

    public function __construct(private readonly BusinessSettings $business) {}

    /**
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function main(): array
    {
        return $this->filter([
            ['label' => 'الرئيسية', 'route' => 'home'],
            ['label' => 'خدماتنا', 'route' => 'services.index', 'when' => fn () => $this->services()->isNotEmpty()],
            ['label' => 'الأسعار', 'route' => 'prices.index', 'when' => fn () => $this->hasLivePriceGuides()],
            ['label' => 'مناطق الخدمة', 'route' => 'areas.index', 'when' => fn () => $this->areas()->isNotEmpty()],
            ['label' => 'من نحن', 'route' => 'about', 'when' => fn () => $this->pageIsLive('about')],
            ['label' => 'تواصل معنا', 'route' => 'contact', 'when' => fn () => $this->pageIsLive('contact')],
        ]);
    }

    /**
     * Footer: hubs only (PLAN.md §6.6); footer areas are listed separately.
     *
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function footer(): array
    {
        return array_values(array_filter($this->main(), fn (array $item) => $item['route'] !== 'home'));
    }

    /**
     * @return Collection<int, Area>
     */
    public function footerAreas(): Collection
    {
        return $this->areas()->filter(fn (Area $area) => $area->show_in_footer)->values();
    }

    /**
     * @return Collection<int, Service>
     */
    public function services(): Collection
    {
        return $this->memo['services'] ??= Service::query()->live()->get(['id', 'slug', 'name', 'requires_24_7', 'is_published', 'published_at']);
    }

    /**
     * @return Collection<int, Area>
     */
    public function areas(): Collection
    {
        return $this->memo['areas'] ??= Area::live();
    }

    public function reviewUrl(): ?string
    {
        return $this->business->gbp_review_url ?: null;
    }

    private function hasLivePriceGuides(): bool
    {
        return $this->memo['prices'] ??= PriceGuide::query()->published()->with('services')->get()->contains->isLive();
    }

    private function pageIsLive(string $slug): bool
    {
        $this->memo['pages'] ??= Page::query()->published()->pluck('slug')->all();

        return in_array($slug, $this->memo['pages'], true);
    }

    /**
     * @param  array<int, array{label: string, route: string, when?: \Closure(): bool}>  $items
     * @return array<int, array{label: string, url: string, route: string}>
     */
    private function filter(array $items): array
    {
        $live = [];

        foreach ($items as $item) {
            if (! Route::has($item['route']) || (isset($item['when']) && ! $item['when']())) {
                continue;
            }
            $live[] = ['label' => $item['label'], 'url' => route($item['route']), 'route' => $item['route']];
        }

        return $live;
    }
}
