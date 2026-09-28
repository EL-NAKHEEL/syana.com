<?php

namespace App\Support;

use App\Models\Area;
use App\Models\Page;
use App\Models\Post;
use App\Models\PriceGuide;
use App\Models\Product;
use App\Models\Project;
use App\Models\Review;
use App\Models\Service;
use App\Settings\BusinessSettings;
use App\Settings\LayoutSettings;
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

    public function __construct(private readonly BusinessSettings $business, private readonly LayoutSettings $layout) {}

    /** Menu entries the owner can reorder, rename or hide (admin › الهيدر والفوتر), in default order. */
    public const ITEMS = [
        'home' => ['label' => 'الرئيسية', 'route' => 'home'],
        'store' => ['label' => 'المتجر', 'route' => 'store.index'],
        'services' => ['label' => 'خدماتنا', 'route' => 'services.index'],
        'prices' => ['label' => 'الأسعار', 'route' => 'prices.index'],
        'areas' => ['label' => 'مناطق الخدمة', 'route' => 'areas.index'],
        'projects' => ['label' => 'مشاريعنا', 'route' => 'projects.index'],
        'blog' => ['label' => 'المدونة', 'route' => 'blog.index'],
        'reviews' => ['label' => 'آراء العملاء', 'route' => 'reviews.index', 'visible' => false], // footer by default
        'about' => ['label' => 'من نحن', 'route' => 'about'],
        'contact' => ['label' => 'تواصل معنا', 'route' => 'contact'],
    ];

    /**
     * Main menu in the owner's order and wording. An entry still shows only when its section has live content.
     *
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function main(): array
    {
        return $this->memo['main'] ??= $this->filter(array_map(fn (array $entry) => [
            'label' => filled($entry['label'] ?? null) ? (string) $entry['label'] : self::ITEMS[$entry['key']]['label'],
            'route' => self::ITEMS[$entry['key']]['route'],
            'when' => $this->liveCheck($entry['key']),
        ], $this->menuEntries()));
    }

    /**
     * Saved menu entries (unknown keys dropped); entries added to ITEMS after the menu was saved are appended visible.
     *
     * @return array<int, array{key: string, label?: string|null, visible?: bool}>
     */
    private function menuEntries(): array
    {
        $saved = array_values(array_filter($this->layout->menu, fn ($entry) => is_array($entry) && isset(self::ITEMS[$entry['key'] ?? null])));
        $keys = array_column($saved, 'key');

        foreach (array_keys(self::ITEMS) as $key) {
            if (! in_array($key, $keys, true)) {
                $saved[] = ['key' => $key, 'visible' => self::ITEMS[$key]['visible'] ?? true];
            }
        }

        return array_values(array_filter($saved, fn (array $entry) => ($entry['visible'] ?? true) !== false));
    }

    /**
     * @return \Closure(): bool
     */
    private function liveCheck(string $key): \Closure
    {
        return match ($key) {
            'store' => fn () => $this->memo['store'] ??= Product::query()->live()->exists(),
            'services' => fn () => $this->services()->isNotEmpty(),
            'prices' => fn () => $this->hasLivePriceGuides(),
            'areas' => fn () => $this->areas()->isNotEmpty(),
            'projects' => fn () => $this->memo['projects'] ??= Project::query()->published()->exists(),
            'blog' => fn () => $this->memo['blog'] ??= Post::query()->published()->whereHas('author', fn ($q) => $q->published())->exists(),
            'reviews' => fn () => $this->memo['reviews'] ??= Review::query()->approved()->exists(),
            'about', 'contact' => fn () => $this->pageIsLive($key),
            default => fn () => true,
        };
    }

    /**
     * Footer: hubs only (PLAN.md §6.6); footer areas are listed separately.
     *
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function footer(): array
    {
        $items = array_values(array_filter($this->main(), fn (array $item) => $item['route'] !== 'home'));

        // Reviews always get a footer link once live, even when hidden from the header menu.
        if (! in_array('reviews.index', array_column($items, 'route'), true)) {
            $items = [...$items, ...$this->filter([['label' => self::ITEMS['reviews']['label'], 'route' => 'reviews.index', 'when' => $this->liveCheck('reviews')]])];
        }

        return $items;
    }

    /**
     * Policy pages (warranty, returns, privacy, terms) linked sitewide once published.
     *
     * @return array<int, array{label: string, url: string, route: string}>
     */
    public function policies(): array
    {
        return $this->filter([
            ['label' => 'سياسة الضمان', 'route' => 'warranty', 'when' => fn () => $this->pageIsLive('warranty')],
            ['label' => 'التوصيل والاسترجاع', 'route' => 'shipping-returns', 'when' => fn () => $this->pageIsLive('shipping-returns')],
            ['label' => 'سياسة الخصوصية', 'route' => 'privacy', 'when' => fn () => $this->pageIsLive('privacy')],
            ['label' => 'الشروط والأحكام', 'route' => 'terms', 'when' => fn () => $this->pageIsLive('terms')],
        ]);
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
        return $this->memo['services'] ??= Service::query()->live()->with('media')->get(['id', 'slug', 'name', 'requires_24_7', 'is_published', 'published_at']);
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
