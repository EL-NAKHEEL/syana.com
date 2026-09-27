<?php

namespace App\Seo\Schema;

use App\Models\Area;
use App\Settings\BusinessSettings;
use App\Support\Navigation;
use Carbon\CarbonInterface;
use Spatie\SchemaOrg\BaseType;
use Spatie\SchemaOrg\Schema;

/**
 * Builds JSON-LD nodes for a single @graph with stable @ids.
 * Only confirmed business facts are emitted (see BusinessSettings).
 */
class SchemaGraph
{
    private const DAYS = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    public function __construct(private readonly BusinessSettings $business) {}

    public static function id(string $fragment, ?string $url = null): string
    {
        return rtrim($url ?? url('/'), '/').'/#'.$fragment;
    }

    public static function pageId(string $url, string $fragment): string
    {
        return $url.'#'.$fragment;
    }

    /**
     * @return array{'@id': string}
     */
    public static function ref(string $id): array
    {
        return ['@id' => $id];
    }

    /**
     * @return array<string, mixed>
     */
    public function organization(): array
    {
        $phone = (string) config('site.phone.e164');

        $node = Schema::hVACBusiness()
            ->identifier(self::id('organization'))
            ->name((string) config('site.brand.name'))
            ->alternateName(config('site.brand.alternate_names'))
            ->url(url('/'))
            ->logo(Schema::imageObject()
                ->identifier(self::id('logo'))
                ->url(asset('images/logo-512.png'))
                ->contentUrl(asset('images/logo-512.png'))
                ->setProperty('width', 512)
                ->setProperty('height', 512)
                ->caption((string) config('site.brand.name')))
            ->image(self::ref(self::id('logo')))
            ->telephone($phone)
            ->contactPoint(Schema::contactPoint()
                ->telephone($phone)
                ->contactType('customer service')
                ->availableLanguage(['ar']));

        if ($email = $this->business->publicEmail()) {
            $node->email($email);
        }

        $sameAs = array_values(array_filter([$this->business->gbp_url, ...$this->business->same_as]));
        if ($sameAs !== []) {
            $node->sameAs($sameAs);
        }

        if ($hours = $this->openingHours()) {
            $node->setProperty('openingHoursSpecification', $hours);
        }

        if ($this->business->publicAddressIsKnown()) {
            $node->address(Schema::postalAddress()
                ->streetAddress((string) $this->business->street_address)
                ->addressLocality((string) $this->business->locality)
                ->addressRegion((string) $this->business->region)
                ->postalCode((string) $this->business->postal_code)
                ->addressCountry('EG'));

            if ($this->business->latitude !== null && $this->business->longitude !== null) {
                $node->geo(Schema::geoCoordinates()->latitude($this->business->latitude)->longitude($this->business->longitude));
            }
        }

        if ($areas = $this->areaServed()) {
            $node->setProperty('areaServed', $areas);
        }

        if (filled($this->business->price_range)) {
            $node->priceRange((string) $this->business->price_range);
        }

        if ($this->business->founding_year) {
            $node->setProperty('foundingDate', (string) $this->business->founding_year);
        }

        return ['@type' => ['HVACBusiness', 'Store']] + $this->toArray($node);
    }

    /**
     * @return array<string, mixed>
     */
    public function website(): array
    {
        return $this->toArray(Schema::webSite()
            ->identifier(self::id('website'))
            ->url(url('/'))
            ->name((string) config('site.brand.name'))
            ->alternateName(config('site.brand.alternate_names'))
            ->inLanguage('ar')
            ->setProperty('publisher', self::ref(self::id('organization'))));
    }

    /**
     * @param  array{type?: string, name: string, description?: ?string, breadcrumb?: bool, image?: ?string, published?: ?CarbonInterface, modified?: ?CarbonInterface, about?: ?string}  $page
     * @return array<string, mixed>
     */
    public function webPage(string $url, array $page): array
    {
        $node = [
            '@type' => $page['type'] ?? 'WebPage',
            '@id' => self::pageId($url, 'webpage'),
            'url' => $url,
            'name' => $page['name'],
            'inLanguage' => 'ar',
            'isPartOf' => self::ref(self::id('website')),
        ];

        if (! empty($page['description'])) {
            $node['description'] = $page['description'];
        }
        if (! empty($page['about'])) {
            $node['about'] = self::ref($page['about']);
        }
        if (! empty($page['breadcrumb'])) {
            $node['breadcrumb'] = self::ref(self::pageId($url, 'breadcrumb'));
        }
        if (! empty($page['image'])) {
            $node['primaryImageOfPage'] = ['@type' => 'ImageObject', 'url' => $page['image']];
        }
        if (! empty($page['published'])) {
            $node['datePublished'] = $page['published']->toAtomString();
        }
        if (! empty($page['modified'])) {
            $node['dateModified'] = $page['modified']->toAtomString();
        }

        return $node;
    }

    /**
     * @param  array<int, array{name: string, url: string}>  $items
     * @return array<string, mixed>
     */
    public function breadcrumbList(string $url, array $items): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => self::pageId($url, 'breadcrumb'),
            'itemListElement' => array_map(fn (array $item, int $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $item['name'],
                'item' => $item['url'],
            ], $items, array_keys($items)),
        ];
    }

    /**
     * @param  array<int, array<string, mixed>>  $nodes
     */
    public static function render(array $nodes): string
    {
        return (string) json_encode(
            ['@context' => 'https://schema.org', '@graph' => array_values($nodes)],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * Live service areas only: no area is ever claimed without a live area page.
     *
     * @return array<int, array<string, string>>
     */
    public function areaServed(): array
    {
        return app(Navigation::class)->areas()
            ->map(fn (Area $area) => ['@type' => 'Place', 'name' => $area->name_ar, 'url' => $area->url()])
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function openingHours(): array
    {
        if ($this->business->is_24_7) {
            return [['@type' => 'OpeningHoursSpecification', 'dayOfWeek' => self::DAYS, 'opens' => '00:00', 'closes' => '23:59']];
        }

        return array_values(array_map(fn (array $row) => [
            '@type' => 'OpeningHoursSpecification',
            'dayOfWeek' => array_values(array_intersect(self::DAYS, $row['days'])),
            'opens' => $row['opens'],
            'closes' => $row['closes'],
        ], array_filter($this->business->opening_hours, fn ($row) => ! empty($row['days']) && ! empty($row['opens']) && ! empty($row['closes']))));
    }

    /**
     * @return array<string, mixed>
     */
    private function toArray(BaseType $type): array
    {
        $array = $type->toArray();
        unset($array['@context']);

        return $array;
    }
}
