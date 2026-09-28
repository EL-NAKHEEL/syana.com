<?php

namespace App\Seo;

use App\Models\Contracts\HasSeo;
use App\Models\SeoMeta;
use App\Seo\Schema\SchemaGraph;
use App\Settings\SeoSettings;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Request-scoped SEO state. Controllers describe the page; the layout renders it.
 *
 * Rules (PLAN.md §6): canonical is absolute and route-built; filter/sort params mean noindex without a
 * canonical; noindex pages never carry a canonical; ?page=N is self-canonical with «صفحة N» in the title.
 */
class Seo
{
    private string $title = '';

    private bool $brandTitle = true;

    private string $description = '';

    private ?string $canonical = null;

    private ?string $robots = null;

    private int $page = 1;

    /** @var array<int, array{name: string, url: string}> */
    private array $breadcrumbs = [];

    private string $pageType = 'WebPage';

    private ?string $about = null;

    /** @var array<int, array<string, mixed>> */
    private array $nodes = [];

    private ?string $ogImage = null;

    private ?string $ogImageAlt = null;

    private string $ogType = 'website';

    private ?string $ogKicker = null;

    private ?CarbonInterface $published = null;

    private ?CarbonInterface $modified = null;

    private bool $isPreview = false;

    public function __construct(
        private readonly Request $request,
        private readonly SchemaGraph $graph,
        private readonly OgImage $og,
        private readonly SeoSettings $settings,
    ) {}

    public function title(string $title, bool $withBrand = true): static
    {
        $this->title = $title;
        $this->brandTitle = $withBrand;

        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function canonical(string $url): static
    {
        $this->canonical = $url;

        return $this;
    }

    public function page(int $page): static
    {
        $this->page = max(1, $page);

        return $this;
    }

    public function robots(string $robots): static
    {
        $this->robots = $robots;

        return $this;
    }

    public function noindex(): static
    {
        return $this->robots('noindex, follow');
    }

    /**
     * Hub rule (owner decision C1): areas, projects, reviews, blog and category hubs stay noindex, follow
     * until they list at least `site.seo.hub_min_items` published items.
     */
    public function hub(int $items): static
    {
        return $items < (int) config('site.seo.hub_min_items') ? $this->noindex() : $this;
    }

    /**
     * Breadcrumb trail after «الرئيسية» (added automatically). The last item is the current page.
     *
     * @param  array<int, array{name: string, url: string}>  $items
     */
    public function breadcrumbs(array $items): static
    {
        $this->breadcrumbs = [['name' => 'الرئيسية', 'url' => url('/')], ...$items];

        return $this;
    }

    public function pageType(string $type, ?string $about = null): static
    {
        $this->pageType = $type;
        $this->about = $about;

        return $this;
    }

    /**
     * @param  array<string, mixed>  $node
     */
    public function addNode(array $node): static
    {
        $this->nodes[] = $node;

        return $this;
    }

    public function ogImage(string $url, string $alt): static
    {
        $this->ogImage = $url;
        $this->ogImageAlt = $alt;

        return $this;
    }

    public function ogType(string $type): static
    {
        $this->ogType = $type;

        return $this;
    }

    public function ogKicker(?string $kicker): static
    {
        $this->ogKicker = $kicker;

        return $this;
    }

    public function dates(?CarbonInterface $published, ?CarbonInterface $modified): static
    {
        $this->published = $published;
        $this->modified = $modified;

        return $this;
    }

    public function preview(bool $isPreview = true): static
    {
        $this->isPreview = $isPreview;

        return $this;
    }

    /**
     * Applies per-record admin overrides from the SEO panel.
     */
    public function fromModel(HasSeo&Model $model): static
    {
        $meta = $model->relationLoaded('seoMeta') ? $model->getRelation('seoMeta') : $model->seoMeta()->first();

        if (! $meta instanceof SeoMeta) {
            return $this;
        }

        if (filled($meta->title)) {
            $this->title((string) $meta->title, false);
        }
        if (filled($meta->description)) {
            $this->description((string) $meta->description);
        }
        if (filled($meta->robots) && $meta->robots !== 'index, follow') {
            $this->robots((string) $meta->robots);
        }
        if (filled($meta->canonical_override)) {
            $this->canonical((string) $meta->canonical_override);
        }
        if (filled($meta->og_image)) {
            $this->ogImage((string) $meta->og_image, $this->title);
        }

        return $this;
    }

    // ---- Resolved values (used by the layout and tests) ----

    public function fullTitle(): string
    {
        return TitleBuilder::title($this->title, $this->page, $this->brandTitle);
    }

    public function metaDescription(): string
    {
        return TitleBuilder::description($this->description, $this->page);
    }

    public function isPreviewing(): bool
    {
        return $this->isPreview;
    }

    public function isFiltered(): bool
    {
        $keys = array_keys($this->request->query());

        return array_intersect($keys, (array) config('site.seo.filter_params')) !== [];
    }

    public function robotsContent(): string
    {
        if ($this->isPreview || $this->isFiltered()) {
            return 'noindex, follow';
        }

        return $this->robots ?? 'index, follow';
    }

    public function isIndexable(): bool
    {
        return str_starts_with($this->robotsContent(), 'index');
    }

    public function canonicalUrl(): ?string
    {
        if (! $this->isIndexable() || $this->canonical === null) {
            return null;
        }

        return $this->page > 1 ? $this->canonical.'?page='.$this->page : $this->canonical;
    }

    /**
     * URL used for og:url and schema @ids: the canonical, else the clean current URL.
     */
    public function pageUrl(): string
    {
        return $this->canonicalUrl() ?? ($this->canonical ?? $this->request->url());
    }

    /**
     * @return array<int, array{name: string, url: string}>
     */
    public function breadcrumbItems(): array
    {
        return $this->breadcrumbs;
    }

    public function ogImageUrl(): string
    {
        if ($this->ogImage !== null) {
            return $this->ogImage;
        }

        try {
            return $this->og->url($this->title !== '' ? $this->title : (string) config('site.brand.name'), $this->ogKicker);
        } catch (\Throwable $e) {
            report($e);

            return filled($this->settings->default_og_image)
                ? asset('storage/'.$this->settings->default_og_image)
                : asset('images/logo-512.png');
        }
    }

    public function ogImageAlt(): string
    {
        return $this->ogImageAlt ?? $this->title;
    }

    public function openGraphType(): string
    {
        return $this->ogType;
    }

    public function jsonLd(): string
    {
        $url = $this->pageUrl();
        $hasBreadcrumbs = count($this->breadcrumbs) > 1;

        $nodes = [
            $this->graph->organization(),
            $this->graph->website(),
            $this->graph->webPage($url, [
                'type' => $this->pageType,
                'name' => $this->title,
                'description' => $this->metaDescription(),
                'breadcrumb' => $hasBreadcrumbs,
                'about' => $this->about,
                'published' => $this->published,
                'modified' => $this->modified,
            ]),
        ];

        if ($hasBreadcrumbs) {
            $nodes[] = $this->graph->breadcrumbList($url, $this->breadcrumbs);
        }

        return SchemaGraph::render([...$nodes, ...$this->nodes]);
    }
}
